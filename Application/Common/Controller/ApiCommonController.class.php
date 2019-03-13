<?php
/**
 * 未登录接口, 需要继承的基类
 * create by zhaojiping <QQ: 17620286>
 */
namespace Common\Controller;
use Common\Controller\CommonController;
class ApiCommonController extends CommonController {

    public function __construct(){
        parent::__construct();

        define('WEB_URL', 'http://sd.shengyangjituan.com');
        if (session('WX_UID')) {
            define('UID', session('WX_UID'));
        }
    }

    protected function apiReturn($result){
        /*if ($result['status'] != 0 && $result['status'] != 1) {
            exit('参数调用错误 status');
        }*/
        $data = $result['data'];
        if ($result['data'] != '' && C('APP_DATA_ENCODE') == true) {
            $data = json_encode($result['data']); // 数组转为json字符串
            $aes = new \Common\Tools\Aes();
            $data = $aes->aes128cbcEncrypt($data); // 加密
        }

        if (is_null($data) || empty($data)) $data = array();
            $data = string_data($data);
            $this->ajaxReturn(V($result['status'], $result['info'], $data));

    }

}
