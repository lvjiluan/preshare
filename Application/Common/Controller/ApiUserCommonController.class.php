<?php
/**
 * 用户登录后, 需要继承的基类
 */
namespace Common\Controller;
use Common\Controller\ApiCommonController;
class ApiUserCommonController extends ApiCommonController {
    
    public function __construct()
    {
        parent::__construct();
        $token = I('post.token', '');
        //获取参数配置
        $this->get_global_config();
        $user = D('User');
        if (session('WX_UID')) {
            define('UID', session('WX_UID'));
        } else {
//            $code = I('get.code', '');
//
//            $userInfo = getUserInfo($code);
//            if ($userInfo == false) {
//                return false;
//            }
//            $data['open_id'] = $userInfo['openid'];
//            $result = $user->field('user_id')->where(array('open_id' => $data['open_id']))->find();
//            if ($result) {
//                session('WX_UID', $result['user_id']);
//                define('UID', session('WX_UID'));
//            } else {
//                //$this->create_emchat_user();
//                $data['nickname'] = $userInfo['nickname'];
//                $data['head_pic'] = $userInfo['headimgurl'];
//                $data['sex'] = $userInfo['sex'];
//
//                $data['register_time'] = time();
//                $arr = $user->add($data);
//                if ($arr) {
//                    session('WX_UID', $arr);
//                    define('UID', session('WX_UID'));
//                } else {
//                }
//            }
        }
        define('UID',59);
        $user = D('User');
        $userInfo=$user->where(array('user_id' => UID))->find();
        if(!$userInfo['mobile']){
            redirect(U('Index/bindMobile'));
        }

    }

    function wx_get_jsapi_ticket() {
        $url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", $this->wx_get_token());
        $res = $this->get_curl_contents($url);
        $res = json_decode($res, true);
        //这里应该把access_token缓存起来，至于要怎么缓存就看各位了，有效期是7200s
        $jsapi_ticket = session($res['ticket']);
        return $res['ticket'];
    }
    //获取微信公从号access_token
    function wx_get_token() {
        $AppID = 'wx1156fa16fc1f0ace';//AppID(应用ID)
        $AppSecret = 'ea53d13d47de4606d6736545b77262f4';//AppSecret(应用密钥)
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppID.'&secret='.$AppSecret;
        $res = $this->get_curl_contents($url);
        $res = json_decode($res, true);
        //这里应该把access_token缓存起来，至于要怎么缓存就看各位了，有效期是7200s
        $access_token = session($res['access_token']);
        return $res['access_token'];
    }
    //curl获取请求文本内容
    function get_curl_contents($url, $method ='GET', $data = array()) {
        if ($method == 'POST') {
            //使用crul模拟
            $ch = curl_init();
            //禁用htt<a href="/fw/photo.html" target="_blank">ps</a>
            // <a href="/tags.php/curl_setopt/" target="_blank">curl_setopt</a>($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        }else {
            if (ini_get('allow_<a href="/tags.php/fopen/" target="_blank">fopen</a>_url') == '1') {
                $result = file_get_contents($url);
            }else {
                //使用crul模拟
                $ch = curl_init();
                //允许请求以文件流的形式返回
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //禁用https
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch); //执行发送
                curl_close($ch);
            }
        }
        return $result;
    }

    protected function checkTokenAndGetUid($token){
        $where['token'] = $token;
        $id = M('user_token')->where($where)->getField('user_id');
        return $id ? $id : 0;
    }
    /**
     * [get_global_config 获取配置]
     * @return [type] [description]
     */
    function get_global_config()
    {
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $configParse = new \Common\Tools\ConfigParse();
            $config      =   $configParse->lists();
            S('DB_CONFIG_DATA',$config,60);
        }
        C($config); //添加配置

    }
}
