<?php

/**
 * 微信插件 使用需 require_once("./Plugins/WxPay/WxPay.php");
 * @author Jack_YanTC <627495692@qq.com>
 */
class WxPay {

    /**
     * 初始化参数
     *
     * @param array $options
     * @param $options ['app_id']  APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     * @param $options ['mch_id'] MCHID：商户号（必须配置，开户邮件中可查看）
     * @param $options ['key'] KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * @param $options ['appsecret'] 公众帐号secert（仅JSAPI支付的时候需要配置)，
     * @param $options ['notify_url'] 支付宝回调地址
     */
    public function __construct($options = array()) {
        $this->config = !empty($options) ? $options : C('WxPay');
    }

    /**
     * 微信支付App
     * @param string $data 业务参数 body out_trade_no total_fee
     * @param string $data ['out_trade_no'] 订单号  必填
     * @param string $data ['total_fee'] 订单金额  必填
     * @param string $data ['body'] 订单详情  必填
     * @return $response 返回app所需字符串
     */
    public function WxPayApp($d) {
        
        $wxConfig = $this->config;

        $out_trade_no = $d['out_trade_no'];
        $total_fee = abs(floatval($d['total_fee'])) * 100;// 微信支付 单位为分
        $nonce_str = $this->getRandChar(32);
        $ip = $this->get_client_ip();
        if ($ip == '::1')
            $ip = '1.1.1.1';
        $data ["appid"] = $wxConfig["app_id"];
        $data ["body"] = $d['body'];
        $data ["mch_id"] = $wxConfig['mch_id'];
        $data ["nonce_str"] = $nonce_str;
        $data ["notify_url"] = $wxConfig["notify_url"];
        $data ["out_trade_no"] = $out_trade_no;
        $data ["spbill_create_ip"] = $ip;
        $data ["total_fee"] = $total_fee;
        $data ["trade_type"] = "APP";
        $s = $this->getSign($data);
        $data ["sign"] = $s;
        $xml = $this->arrayToXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $response = $this->postXmlCurl($xml, $url);

        $re = $this->xmlstr_to_array($response);
        if ($re ['return_code'] == 'FAIL') {
            return $re['return_msg'];
        }
        // 二次签名
        $reapp = $this->getOrder($re['prepay_id']);
        return $reapp;
    }

    /**
     * 支付宝web支付 需要签约 电脑网站支付
     * @param string $data 业务参数
     * @param string $data ['out_trade_no'] 订单号  必填
     * @param string $data ['total_fee'] 订单金额  必填
     * @param string $data ['body'] 订单详情  必填
     * @return string 支付二维码图片地址
     */
    public function WxPayWeb($d) {
        $wxConfig = $this->config;
        $out_trade_no = $d['out_trade_no'];
        $total_fee = abs(floatval($d['total_fee'])) * 100;// 微信支付 单位为分
        $nonce_str = $this->getRandChar(32);
        $ip = $this->get_client_ip();
        if ($ip == '::1')
            $ip = '1.1.1.1';
        $data ["appid"] = $wxConfig["app_id"];
        $data ["body"] = $d['body'];
        $data ["mch_id"] = $wxConfig['mch_id'];
        $data ["nonce_str"] = $nonce_str;
        $data ["notify_url"] = $wxConfig["notify_url"];
        $data ["out_trade_no"] = $out_trade_no;
        $data ["spbill_create_ip"] = $ip;
        $data ["total_fee"] = $total_fee;
        $data ["trade_type"] = "NATIVE";
        $s = $this->getSign($data);
        $data ["sign"] = $s;
        $xml = $this->arrayToXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $response = $this->postXmlCurl($xml, $url);
        $re = $this->xmlstr_to_array($response);
        if ($re ['return_code'] == 'FAIL') {
            return $re['return_msg'];
        }
        $url2 = $re["code_url"];
        $imgUrl = '/Plugins/WxPay/qrcode.php?data=' . urlencode($url2);
        return $imgUrl;
        //return '<img alt="模式二扫码支付" src="/Plugins/WxPay/phpqrcode.php?data=' . urlencode($url2).'"/>';
    }

    /**
     * 微信签名验证
     * @param string $data 业务参数
     * @return array
     */
    public function WxPayNotifyCheck() {
        $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            error_log('parse xml error', 3, './wechat_errorlog.txt');
        }
        if ($postObj->return_code != 'SUCCESS') {
            error_log($postObj->return_msg, 3, './wechat_errorlog.txt');
        }

        $arr = (array)$postObj;
        unset($arr['sign']);
        if ($this->getSign($arr) == $postObj->sign) {
            return array('status' => true, 'data' => $arr);
        } else {
            return array('status' => false);
        }
    }


    /**
     * 以下为微信所需相关方法，请勿修改
     */


    // 执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId) {
        $data ["appid"] = $this->config ["app_id"];
        $data ["noncestr"] = $this->getRandChar(32);;
        $data ["package"] = "Sign=WXPay";
        $data ["partnerid"] = $this->config ['mch_id'];
        $data ["prepayid"] = $prepayId;
        $data ["timestamp"] = time();
        $s = $this->getSign($data);
        $data ["sign"] = $s;

        return $data;
    }

    //生成签名
    function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters [strtolower($k)] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        // echo "【string】 =".$String."</br>";
        // 签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->config ['key'];
        // echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        // 签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    // 获取指定长度的随机字符串
    function getRandChar($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol [rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    // 数组转xml
    function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    // post https请求，CURLOPT_POSTFIELDS xml格式
    function postXmlCurl($xml, $url, $second = 30) {
        // 初始化curl
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    //获取当前服务器的IP
    function get_client_ip() {
        if ($_SERVER ['REMOTE_ADDR']) {
            $cip = $_SERVER ['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    // 将数组转成uri字符串
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }

        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //xml转成数组
    function xmlstr_to_array($xmlstr) {
        $doc = new \DOMDocument ();
        $doc->loadXML($xmlstr);
        return $this->domnode_to_array($doc->documentElement);
    }

    //dom转成数组
    function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE :
            case XML_TEXT_NODE :
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE :
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset ($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset ($output [$t])) {
                            $output [$t] = array();
                        }
                        $output [$t] [] = $v;
                    } elseif ($v) {
                        $output = ( string )$v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a [$attrName] = ( string )$attrNode->value;
                        }
                        $output ['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output [$t] = $v [0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

}