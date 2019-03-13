<?php
/**
 * 后台默认控制器类
 */
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    //取出当前登陆管理帐号所拥有的权限菜单列表
    public function index(){
        $admin_id = session('admin_id');
        $menu = D('Admin/Privilege')->getMenu($admin_id);
        $this->assign('menu',$menu);

        $this->display();
    }

    /**
     *@description 获取首页的统计数据
     *@author liuyang
     *@date 2016-07-18
     */
    public function welcome(){
        $cv_secret = C('YOU_YUN_SECRET');
        $balance = resume_balance($cv_secret);
        $balance = json_decode($balance, true);
        $balance_expired = $balance['data']['expired_time'];
        $balance = $balance['data']['balance'];
        $this->expired = $balance_expired;
        $this->balance = $balance;
        $this->display();
    }
}