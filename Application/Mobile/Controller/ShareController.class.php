<?php

namespace Mobile\Controller;
use Think\Controller;
require_once './ThinkPHP/Library/Vendor/Wechat/WxUpload.php';

/**
 * 邀请详情
 * @author wangzhiliang liuniukeji.com
 */
class ShareController extends Controller {

    // 邀请页面
    public function index(){
        $wxupload = new \WxUpload("wx055e36e6c92ad33f","154e4665b5919b6571540318c486ae01");
        $signPackage = $wxupload->getSignPackage();
        $this->assign('sing',$signPackage);
        //使用数组定义分享内容参数
        $news = array(
            "Title" =>"欢迎加入盛阳",
            "Description"=>"欢迎加入盛阳",
            "PicUrl" =>"http://guanying.pro4.liuniukeji.net/Application/Wechat/Static/images/18.jpg",
            "Url" =>"http://guanying.pro4.liuniukeji.net/Wechat/release/detail",
        );
        $this->assign('news',$news);
        $this->display();
    }

}