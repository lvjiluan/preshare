<?php
namespace Mobile\Controller;
use Common\Controller\CommonController;

class IndexController extends CommonController{
//    public function __construct() {
//        parent::__construct();
//    }

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

    //首页接口
    public function index(){
        //获取轮播图
        $banner=M('Ad')->where(array('position_id'=>1,'display'=>1))->field('ad_id,content')->order('sort asc,ad_id desc')->limit(5)->select();
//        foreach($banner as $k=>$v){
//            $banner[$k]['content']=WEB_URL.$v['content'];
//        }
        $spec_material=C('MATERIAL');
        $spec_width=C('WIDTH');
        $spec_height=C('HEIGHT');
        //获取产品
        $goods= M('Goods')
            ->alias('g')
            ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
            ->field('c.cat_name,g.goods_id,g.material,g.height,g.width,g.thumb_img,g.price,g.sort')
            ->order('g.sort asc,g.goods_id desc')
            ->where(array('display'=>1))
            ->limit(5)
            ->select();
        foreach($goods as $key=>$v){
            $goods[$key]['price']=fen_to_yuan($v['price']);
            $goods[$key]['material']=$spec_material[$v['material']];
            $goods[$key]['width']=$spec_width[$v['width']];
            $goods[$key]['height']=$spec_height[$v['height']];
        }
        $p=I('p',1);
        $where['display']=1;
        $news=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->page($p,5)->order('addtime desc')->select();
        // p(M('Article')->_sql());die;

        $list['banner']=$banner;
        $list['goods']=$goods;
        $list['news']=$news;
       // $this->apiReturn(V(1,'查询成功',$list));
        $this->assign('list',$list);
        $this->assign('yangshi',1);
        $this->display();
    }

    /**
     * 绑定手机
     */
    public function bindMobile() {
        if(IS_POST){
            $mobile = I('mobile', '');
            $company = I('company', '');
            $sms_code = I('sms_code', '');
            $userModel = D('Admin/User');
            $where_sel['mobile']=$mobile;
            $where_sel['user_id']=array('neq',session('WX_UID'));
            $userInfo = $userModel->getUserInfo($where_sel);
            if($userInfo){
                $this->apiReturn(V(0, '该手机号已被注册'));
            }

            $result = D('Common/SmsMessage')->checkSmsMessage($sms_code, $mobile, 4);

            if ($result['status'] == 1) {
                $save['mobile'] = $mobile;
                $save['company'] = $company;
               // $res = $userModel->saveUserData(array('user_id' => UID), $save);
                $res = $userModel->where(array('user_id' => session('WX_UID')))->save($save);
                if(false !== $res){
                    $this->apiReturn(V(1, '手机号码绑定成功'));
                }else{
                    $this->apiReturn(V(0, $this->_sql()));
                }
                $this->apiReturn(V(0, '绑定失败！'));
            }
            else{
                $this->apiReturn(V(0, '验证码错误'));
            }
        }else{
            $userInfo=M('User')->where(array('user_id'=>session('WX_UID')))->find();
            $this->assign('userInfo',$userInfo);
            $this->display('UserCenter/bindmobile');
        }

    }

    //手机号解绑
    public function delBindMobile(){
        $memberModel = M("User");
        $where['user_id']=session('WX_UID');
        if(IS_POST){
            $code=I('sms_code');
            if($code == ''){
                $this->ajaxReturn(V(0, '请输入验证码'));
            }else{
                $userinfo =$memberModel->where($where)->find();
                $result = D('SmsMessage')->checkSmsMessage($code, $userinfo['mobile']);
                //            p($result);die;
                if($result['status'] ==0){
                    $this->ajaxReturn($result);
                }
            }


            $data['mobile'] = '';
            $res =$memberModel->where($where)->save($data);

            if($res){
                $this->ajaxReturn(V(1, '解绑成功',$res));
            }else{
                $this->ajaxReturn(V(0, '信息错误'));
            }
        }else{
            $userInfo=M('User')->where(array('user_id'=>session('WX_UID')))->find();
            $this->assign('userInfo',$userInfo);
            $this->display('UserCenter/delbindmobile');
        }

    }

    /**
     * 获取短信接口
     */
    public function smsCode() {
        $mobile = I('phone', '');
        $type = I('type', 4, 'intval');

        //1注册短信，2找回密码 3修改密码 4修改手机 5修改提现密码 6设置支付密码
        $type_array = array(1, 2 , 3, 4, 5, 6);
        if (!in_array($type, $type_array)) {
            $this->ajaxReturn(V(0, '参数错误'));
        }

        if (empty($mobile) || !isMobile($mobile)) {
            $this->ajaxReturn(V(0, '请输入有效的手机号码'));
            exit;
        }
        //验证手机号码是否已经验证
        $result = $this->checkMemberExist($mobile);
        if ($result == true && $type == 1) {
            $this->ajaxReturn(V(0, '手机号码已存在'));
        } elseif ($result == false && in_array($type, array(2,3,5,6))) {
            $this->ajaxReturn(V(0, '手机号码不存在'));
        } elseif ($result == true && $type == 4) {
            $this->ajaxReturn(V(0, '手机号码已存在'));
        }
        // 短信内容

        $sms_code = randCode(C('SMS_CODE_LEN'), 1);
        switch ($type) {
            case 1: //注册短信
                $msg = '注册验证码';
                $sms_content = C('SMS_REGISTER_MSG') . $sms_code;
                break;
            case 2: //找回密码
                $msg = '找回密码验证码';
                $sms_content = C('SMS_FINDPWD_MSG') . $sms_code;
                break;
            case 3: //修改密码
                $msg = '修改密码验证码';
                $sms_content = C('SMS_MODPWD_MSG') . $sms_code;
                break;
            case 4: //修改手机号码
                $msg = '修改手机号验证码';
                $sms_content = C('SMS_MODMOBILE_MSG') . $sms_code;
                break;
            case 5: //解绑手机号
                $msg = '解绑手机号验证码';
                $sms_content = C('SMS_DELMOBILE_MSG') . $sms_code;
                break;
            case 6: //设置支付密码
                $msg = '设置支付密码验证码';
                $sms_content = C('SMS_PAY_MSG') . $sms_code;
                break;
            default:
                break;
        }

        $send_result = sendMessageRequest($mobile, $sms_content);

        // 保存短信信息
        $data['sms_content'] = $sms_content;
        $data['sms_code'] = $sms_code;
        $data['mobile'] = $mobile;
        $data['type'] = $msg;
        $data['send_status'] = $send_result['status'];
        $data['send_response_msg'] = $send_result['info'];
        D('Common/SmsMessage')->addSmsMessage($data);

        if ($send_result['status'] == 1) {
            $this->ajaxReturn(V(1, '发送成功'));
        } else {
            $this->ajaxReturn(V(0, '发送失败:'. $send_result['info']));
        }
    }

    // 判断用户是否存在
    public function checkMemberExist($phone){
        $where['mobile'] = $phone;

        $count = M('User')->where($where)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

}
