<?php

/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/29/17
 * Time: 9:14 AM
 * 控制器基类
 */
namespace Common\Controller;
use Common\Controller\CommonController;

class UserCommonController extends CommonController
{
    public function __construct()
    {
        parent::__construct();

        $wx = array(); //生成签名的时间戳
        $wx['timestamp'] = time(); //生成签名的随机串
        $wx['noncestr'] = 'jeWaOG8KNzVICwTh'; //jsapi_ticket是公众号用于调用微信JS接口的临时票据。正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
        $wx['jsapi_ticket'] = $this->wx_get_jsapi_ticket(); //分享的地址，注意：这里是指当前网页的URL，不包含#及其后面部分，曾经的我就在这里被坑了，所以小伙伴们要小心了
        $wx['url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wx['jsapi_ticket'], $wx['noncestr'], $wx['timestamp'], $wx['url']); //生成签名
        $wx['signature'] = sha1($string);

        $this->assign('wx', $wx);

        if(session('WX_UID')){
        }else{
            $code = I('get.code','');
            $user = D('User');
            $userInfo = getUserInfo($code);
            if($userInfo == false){
                return false;
            }
            $data['open_id'] = $userInfo['openid'];
            $result = $user->field('id')->where(array('open_id' => $data['open_id']))->find();
            if($result){
                session('WX_UID',$result['id']);
                define('UID',session('WX_UID'));
            }else{
                $this->create_emchat_user();
                $data['nick_name'] = $userInfo['nickname'];
                $data['head_pic'] = $userInfo['headimgurl'];
                $data['sex'] = $userInfo['sex'];

                $data['register_time'] = time();
                $arr = $user->add($data);
                if($arr){
                    session('WX_UID',$arr);
                    define('UID',session('WX_UID'));
                }else{
                }
            }
        }
//        if( ! UID ){// 还没登录 跳转到登录页面
//            $this->redirect('/user-login');
//        }
//        // 禁用刷新就下线
//        $where['user_id'] = array('eq', UID);
//        $disabled = M('User')->where($where)->getfield('disabled');
//        unset($where);
//        if ($disabled == 0) {
//            session(null);
//            $this->redirect('/user-login');
//        }
    }

    //获取微信公从号ticket
    function wx_get_jsapi_ticket() {
        $url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", $this->wx_get_token());
        $res = $this->get_curl_contents($url);
        $res = json_decode($res, true);
        //这里应该把access_token缓存起来，至于要怎么缓存就看各位了，有效期是7200s
        $jsapi_ticket = session($res['ticket']);
        return $res['ticket'];
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

    // 上传图片
    public function _uploadImg(){
        //处理手机端因为url未变时框架会读取本地缓存的图片数据，导致修改的图片未更新的问题(导致原因：修改图片时只是修改了资源，)
        //$oldImg = I('oldImg', '', 'htmlspecialchars');
        $oldImg = '';
        $savePath = I('savePath', '', 'htmlspecialchars');
        if($savePath != '') $savePath = $savePath . '/';

        $result = array( 'status' => 1, 'msg' => '上传完成');
        //判断有没有上传图片
        //p(trim($_FILES['photo2']['name']));
        if(trim($_FILES['photo']['name']) != ''){
            $upload = new \Think\Upload(C('PICTURE_UPLOAD')); // 实例化上传类
            $upload->replace  = true; //覆盖
            $upload->savePath = $savePath; //定义上传目录
            //如果有上传名, 用原来的名字
            if($oldImg != '') $upload->saveName = $oldImg;
            // 上传文件
            $info = $upload->uploadOne($_FILES['photo']);
            if(!$info) {
                $result = array( 'status' => 0, 'msg' => $upload->getError() );
            }else{
                if ($oldImg != '') {
                    //删除缩略图
                    $dir = '.'.C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'];
                    $filesnames = dir($dir);
                    while($file = $filesnames->read()){
                        if ((!is_dir("$dir/$file")) AND ($file != ".") AND ($file != "..")) {
                            $count = strpos($file, $oldImg.'_');
                            if ($count !== false) {
                                if (file_exists("$dir/$file") == true) {
                                    @unlink("$dir/$file");
                                }
                            }
                        }
                    }
                    $filesnames->close();
                }
                $result['src'] = C('UPLOAD_PICTURE_ROOT') . '/' . $info['savepath'] . $info['savename'];
            }
            $this->ajaxReturn($result);
        }
    }

    // 删除图片
    public function _delFile(){

        $file = I('file', '', 'htmlspecialchars');

        $result = array( 'status' => 1, 'msg' => '删除完成');

        if($file != ''){
            $file = './' . __ROOT__ . $file;

            if (file_exists($file) == true) {
                @unlink($file);
            }
        }
        $this->ajaxReturn($result);
    }

}
