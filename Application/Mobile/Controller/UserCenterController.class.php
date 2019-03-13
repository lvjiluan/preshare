<?php
namespace Mobile\Controller;
use Common\Controller\ApiUserCommonController;

class UserCenterController extends ApiUserCommonController{
//    public function __construct()
//    {
//        parent::__construct();
//        $user = D('User');
//        $userInfo=$user->where(array('user_id' => UID))->find();
//        if(!$userInfo['mobile']){
//            redirect(U('UserCenter/bindMobile'));
//        }
//    }
    public function index(){
      $userInfo=M('User')->where(array('user_id'=>UID))->find();
      $userInfo['address_id']=M('UserAddress')->where(array('user_id'=>UID))->order('address_id desc')->getField('address_id');
      $this->assign('userInfo',$userInfo);
        $this->assign('yangshi',4);
      $this->display();
    }

    /**
     * @desc 编辑个人资料
     */
    public function editUserInfo(){
      if(IS_POST){
          $user_name = I('user_name', '', 'trim');
          if(!$user_name){
              $this->apiReturn(V(0, '请输入真实姓名'));
          }
          $nickNameLength = mb_strlen($user_name, 'utf-8');
          if($nickNameLength > 20){
              $this->apiReturn(V(0, '真实姓名不能超过20个字符'));
          }
          $saveData = array();

          $saveData['user_name'] = $user_name;
          $where = array('user_id' => UID);
          $result = D('Admin/User')->saveUserData($where, $saveData);
          if(false !== $result) $this->apiReturn(V(1, '保存成功'));
          $this->apiReturn(V(0, '操作失败,请稍后重试！'));
      }else{
          $userInfo=M('User')->where(array('user_id'=>UID))->find();
          $this->assign('userInfo',$userInfo);
          $this->display();
      }

    }


    /**
     * 绑定手机
     */
    public function bindMobile() {
        if(IS_POST){
            $mobile = I('mobile', '');
            $sms_code = I('sms_code', '');
            $userModel = D('Admin/User');
            $where_sel['mobile']=$mobile;
            $where_sel['user_id']=array('neq',UID);
            $userInfo = $userModel->getUserInfo($where_sel);
            if($userInfo){
                $this->apiReturn(V(0, '该手机号已被注册'));
            }

            $result = D('Common/SmsMessage')->checkSmsMessage($sms_code, $mobile, 4);
            if ($result['status'] == 1) {
                $save = array('mobile' => $mobile);
                $res = $userModel->saveUserData(array('user_id' => UID), $save);
                if(false !== $res){
                    $this->apiReturn(V(1, '手机号码绑定成功'));
                }
                $this->apiReturn(V(0, '绑定失败！'));
            }
            else{
                $this->apiReturn(V(0, '验证码错误'));
            }
        }else{
            $userInfo=M('User')->where(array('user_id'=>UID))->find();
            $this->assign('userInfo',$userInfo);
            $this->display('bindmobile');
        }

    }

    //手机号解绑
    public function delBindMobile(){
        $memberModel = M("User");
        $where['user_id']=UID;
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
            $userInfo=M('User')->where(array('user_id'=>UID))->find();
            $this->assign('userInfo',$userInfo);
            $this->display('delbindmobile');
        }

    }


    /**
     * 新增/编辑用户地址
     */
    public function addEditAddress(){
        $data = I('post.');
        $data['user_id'] = UID;
        $addressModel = D('UserAddress');
        $address_id = I('address_id');
        $goods_id = I('goods_id');
        $goods_num = I('goods_num');
        if (IS_POST) {
            if ($address_id > 0) {
                if($addressModel->create($data, 2)){
                    if ($addressModel->where('address_id='. $address_id .'')->save() !== false) {
                        if ($_POST['source'] == 'order') {
                            header('Location:' . U('/Mobile/order/index', array('address_id' => $address_id,'goods_id'=>$goods_id,'goods_num'=>$goods_num)));
                            exit;
                        } else
                            header('Location:' . U('/Mobile/UserCenter/index'));
                        exit();
                    }
                }
            } else {
                if ($addressModel->create($data, 1)) {
                    if ($addressModel->add() !== false) {
                        if ($_POST['source'] == 'order') {
                            header('Location:' . U('/Mobile/order/index', array('address_id' => $address_id,'goods_id'=>$goods_id,'goods_num'=>$goods_num)));
                            exit;
                        } else
                            header('Location:' . U('/Mobile/UserCenter/index'));
                        exit();
                    }
                }
            }
            $this->apiReturn(V(0, $addressModel->getError()));
        }else{
            $regionList = D('Region')->getRegionNameByParentId();
            $address=M('UserAddress')->where(array('address_id'=>$address_id))->find();
            if($address){
                //获取省份
                $p = M('region')->where(array('parent_id' => 1, 'is_display' => 0))->select();
                $c = M('region')->where(array('parent_id' => $address['province'], 'is_display' => 0))->select();
                $d = M('region')->where(array('parent_id' => $address['city'], 'is_display' => 0))->select();
                $this->assign('province', $p);
                $this->assign('city', $c);
                $this->assign('district', $d);
            }else{
                $this->assign('province',$regionList);
            }
            $list=$addressModel->where(array('user_id'=>UID))->find();

            $this->assign('address',$address);
            $this->assign('list',$list);
            $this->display('addaddress');
        }
    }

    //系统消息列表
    public function messagelist(){
        $map['member_id'][0] = array('eq' , UID );
        $map['member_id'][1] = array('eq' , '');
        $map['member_id'][2] = 'or';

        $map['status'] = array('neq' , 0 );
        $p=I('p',1);
        $res = M('Message')
            ->where($map)
            ->field('message_id,content,add_time,title')
            ->page($p,5)
            ->order('message_id desc')
            ->select();
        foreach ($res as $key => $value) {
            $res[$key]['add_time'] = date('m-d H:i',$value['add_time']);
        }
       $this->assign('list',$res);
       $this->display();

    }

    //我的订单(常规)
    public function order_list(){
        $where['user_id']=UID;
        $where['is_delete']=0;
        $p = I('p',1);
        //总订单数
        $count=M('Order')->where($where)->count();
        $this->assign('count',$count);
        //未确认订单数量
        $where_no['order_status']=0;
        $count_no=M('Order')->where($where)->where($where_no)->count();
        $this->assign('count_no',$count_no);
        //已确认订单数量
        $where_yes['order_status']=array('in','1,2');
        $count_yes=M('Order')->where($where)->where($where_yes)->count();
        $this->assign('count_yes',$count_yes);

        $max_page = floor($count/1)+1;
        $list=M('Order')->where($where)->limit(10) ->order('order_id desc')->select();
        foreach($list as $key=>$v){
            $list[$key]['images']=$v['images'];
            $list[$key]['add_time']=date('Y-m-d',$v['add_time']);
            $list[$key]['goods_price']=fen_to_yuan($v['goods_price']);
            $list[$key]['total_amount']=fen_to_yuan($v['total_amount']);
        }
        $this->assign('max_page',$max_page);
        $this->assign('page',$p+1);
        $this->assign('list',$list);
        $this->assign('yangshi',3);
        $this->display();
    }
    //我的订单(常规)
    public function order_list_my(){
        $where['user_id']=UID;
        $where['is_delete']=0;
        $p = I('p',1);

        //总订单数
        $count=M('OrderCustomize')->where($where)->count();
        $this->assign('count',$count);
        //未确认订单数量
        $where_no['order_status']=0;
        $count_no=M('OrderCustomize')->where($where)->where($where_no)->count();
        $this->assign('count_no',$count_no);
        //已确认订单数量
        $where_yes['order_status']=array('in','1,2');
        $count_yes=M('OrderCustomize')->where($where)->where($where_yes)->count();
        $this->assign('count_yes',$count_yes);

        $max_page = floor($count/1)+1;
        $list=M('OrderCustomize')->where($where)->limit(10) ->order('order_id desc')->select();
        foreach($list as $key=>$v){
//            $list[$key]['images']=$v['images'];
              $list[$key]['add_time']=date('Y-m-d',$v['add_time']);
//            $list[$key]['goods_price']=fen_to_yuan($v['goods_price']);
//            $list[$key]['total_amount']=fen_to_yuan($v['total_amount']);
        }
        $this->assign('max_page',$max_page);
        $this->assign('page',$p+1);
        $this->assign('list',$list);
        $this->display();
    }

    //我的订单(订制)
    public function ajax_more_order_my(){
        $where['user_id']=UID;
        $where['is_delete']=0;
        $order_status=I('order_status','');
        if($order_status!=''){
            if($order_status==1){
                $where['order_status']=array('in','1,2');
            }else{
                $where['order_status']=$order_status;
            }
        }
        $p = I('p',1);

        $count=M('OrderCustomize')->where($where)->count();
        $max_page = floor($count/1)+1;
        if($p >= $max_page){
            // $this->returnApiError('暂无更多');
//            $this->apiReturn(V(0, "<span class='no_more'>暂无更多</span>"));
        }
        $listRows = 10;
        $firstRow = ($p-1)*$listRows;
        $list=M('OrderCustomize')
            ->where($where)
            ->limit($firstRow.','.$listRows)
            ->order('order_id desc')
            ->select();
        foreach($list as $key=>$v){
            $list[$key]['add_time']=date('Y-m-d',$v['add_time']);
        }
        $this->assign('max_page',$max_page);
        $this->assign('page',$p+1);
        $this->assign('list',$list);
        $this->display();
    }

    //我的订单(常规)
    public function ajax_more_order(){
        $where['user_id']=UID;
        $where['is_delete']=0;
       $order_status=I('order_status','');
       if($order_status!=''){
           if($order_status==1){
               $where['order_status']=array('in','1,2');
           }else{
               $where['order_status']=$order_status;
           }
       }


        $p = I('p',1);

        $count=M('Order')->where($where)->count();
        $max_page = floor($count/1)+1;
        if($p >= $max_page){
           // $this->returnApiError('暂无更多');
//            $this->apiReturn(V(0, "<span class='no_more'>暂无更多</span>"));
        }
        $listRows = 10;
        $firstRow = ($p-1)*$listRows;
        $list=M('Order')
            ->where($where)
            ->limit($firstRow.','.$listRows)
            ->order('order_id desc')
            ->select();
        foreach($list as $key=>$v){
            $list[$key]['images']=$v['images'];
            $list[$key]['add_time']=date('Y-m-d',$v['add_time']);
            $list[$key]['goods_price']=fen_to_yuan($v['goods_price']);
            $list[$key]['total_amount']=fen_to_yuan($v['total_amount']);
        }
        $this->assign('max_page',$max_page);
        $this->assign('page',$p+1);
        $this->assign('list',$list);
        $this->display();
    }



    //订单详情
    public function order_detail(){
        $order_id=I('order_id');
        $info=M('Order')->where(array('order_id'=>$order_id))->find();
            if($info['audit_time']){
                $info['audit_time']=date('Y-m-d H:i:s',$info['audit_time']);
            }
            $info['add_time']=date('Y-m-d H:i:s',$info['add_time']);
            $info['goods_price']=fen_to_yuan($info['goods_price']);
            $info['total_amount']=fen_to_yuan($info['total_amount']);
        $sheng=M('Region')->where(array('id'=>$info['province']))->find();
        $shi=M('Region')->where(array('id'=>$info['city']))->find();
        $qu=M('Region')->where(array('id'=>$info['district']))->find();
        $info['address']=$sheng['region_name'].$shi['region_name'].$qu['region_name']. $info['address'];

        $this->assign('info',$info);
        $this->display();
    }
    //订单详情
    public function order_detail_my(){
        $order_id=I('order_id');
        $info=M('OrderCustomize')->where(array('order_id'=>$order_id))->find();
        if($info['audit_time']){
            $info['audit_time']=date('Y-m-d H:i:s',$info['audit_time']);
        }
        $info['add_time']=date('Y-m-d H:i:s',$info['add_time']);
        $this->assign('info',$info);
        $this->display();
    }


    //删除订单
    public function del_order(){
        $order_id=I('order_id');
        $data['is_delete']=1;
        $where['order_id']=$order_id;
        $info=M('Order')->where($where)->save($data);
        if($info!==false){
            $this->ajaxReturn(V(1, '删除成功'));
        }else{
            $this->ajaxReturn(V(0, '删除失败'));
        }
    }
//删除订单
    public function del_order_my(){
        $order_id=I('order_id');
        $data['is_delete']=1;
        $where['order_id']=$order_id;
        $info=M('OrderCustomize')->where($where)->save($data);
        if($info!==false){
            $this->ajaxReturn(V(1, '删除成功'));
        }else{
            $this->ajaxReturn(V(0, '删除失败'));
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




    /**
     * @desc HR招揽/推荐人数
     */
    public function recruitRecommendInfo(){
        $user_id = UID;
        $res = D('Admin/User')->getUserInfo(array('user_id' => $user_id), 'recruit_number,recommended_number');
        $this->apiReturn(V(1, '', $res));
    }

    /**
     * @desc  用户修改密码
     * @param password string 用户密码
     * @param new_password string 新密码
     * @param re_password string 确认新密码
     */
    public function settingUserPwd(){
        $where = array('user_id' => UID);
        $password = I('password');
        $newPassword = I('new_password');
        $rePassword = I('re_password');
        if(!$password || !$newPassword) $this->apiReturn(V(0, '请输入密码！'));
        $passLen = strlen($newPassword);
        if($passLen < 6 || $passLen > 18) $this->apiReturn(V(0, '密码长度支持6-18位！'));
        if($newPassword != $rePassword) $this->apiReturn(V(0, '两次新密码不一致！'));
        $model = D('Admin/User');
        $userInfo = $model->getUserInfo($where, 'password');
        if(!pwdHash($password, $userInfo['password'], true)) $this->apiReturn(V(0, '原密码输入不正确！'));
        $data = $model->saveUserData($where, array('password' => $newPassword));//before_update的问题
        if(false !== $data){
            $this->apiReturn(V(1, '密码修改成功！'));
        }
        else{
            $this->apiReturn(V(0, '服务器繁忙，请稍后重试！'));
        }
    }

    /**
     * @desc 设置支付密码
     */
    public function saveUserPayPassword(){
        $mobile = I('mobile');
        $sms_code = I('sms_code', 0, 'intval');
        $pay_word = I('pay_password', '', 'trim');
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $userInfo = $model->getUserInfo($where);
        $user_type = $userInfo['user_type'];
        if($mobile != $userInfo['mobile']) $this->apiReturn(V(0, '手机号与认证手机号不一致！'));
        if(!isMobile($mobile)) $this->apiReturn(V(0, '请输入合法的手机号！'));
        $payLen = strlen($pay_word);
        if($payLen < 6 || $payLen > 18) $this->apiReturn(V(0, '密码长度6-18位！'));
        if(pwdHash($pay_word, $userInfo['password'], true)) $this->apiReturn(V(0, '不能与登录密码一致！'));
        $valid = D('Admin/SmsMessage')->checkSmsMessage($sms_code, $mobile, $user_type, 6);
        if(!$valid['status']) $this->apiReturn($valid);
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $data = $model->saveUserData($where, array('pay_password' => pwdHash($pay_word)));
        if(false !== $data){
            $this->apiReturn(V(1, '支付密码设置成功！'));
        }
        else{
            $this->apiReturn(V(0, '支付密码设置失败！'));
        }
    }

    /**
     * @desc 上传身份认证凭证
     */
    public function userAuthUpload(){
        $model = D('Admin/User');
        $where = array('user_id' => UID);
        $userInfo = $model->getUserInfo($where);
        $user_auth = $userInfo['is_auth'];
        if($user_auth) $this->apiReturn(V(0, '身份验证已经通过！'));
        $authModel = D('Admin/UserAuth');
        $auth_info = $authModel->getAuthInfo($where);
        $data = I('post.');
        if(1 == $data['cert_type'] && cmp_black_white($data['idcard_number'])) $this->apiReturn(V(0, '身份证号在黑名单内！'));
        if(!$auth_info){
            $create = $authModel->create($data, 1);
            if(false !== $create){
                $res = $authModel->add($data);
                if(false !== $res){
                    $task_id = 1;
                    add_task_log(UID, $task_id);
                    $this->apiReturn(V(1, '身份验证凭据上传成功！'));
                }
                else{
                    $this->apiReturn(V(0, $authModel->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $authModel->getError()));
            }
        }
        else{
            $create = $authModel->create($data, 2);
            if(false !== $create){
                $res = $authModel->where($where)->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '身份验证凭据上传成功！'));
                }
                else{
                    $this->apiReturn(V(0, $authModel->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $authModel->getError()));
            }
        }
    }

    /**
     * @desc 上传文件
     */
    public function uploadFile(){
        $voice = $_FILES['voice'];
        if(!empty($voice)){
            $img = app_upload_file('voice', '', 'Resume');
            if ($img === 0 || $img === -1) {
                $this->apiReturn(V(0, '语音文件上传失败！'));
            }
            else{
                $data['introduced_voice'] = $img;
            }
        }
        $array = array('file' => $img);
        $this->apiReturn(V(1, '', $array));
    }

    /**
     * @desc 上传凭证信息
     */
    public function getUserAuthInfo(){
        $where = array('user_id' => UID);
        $model = D('Admin/UserAuth');
        $auth_info = $model->getAuthInfo($where);
        if($auth_info){
            $auth_info['cert_name'] = strval(C('CERT_TYPE')[$auth_info['cert_type']]);
            $this->apiReturn(V(1, '', $auth_info));
        }
        $auth_field = M('UserAuth')->getDbFields();
        $return = array();
        foreach($auth_field as &$val){
            $return[$val] = '';
        }
        $return['audit_status'] = '-1';
        $return['cert_name'] = '';
        $this->apiReturn(V(1, '获取凭证上传信息失败！', $return));
    }

    /**
     * @desc 发布问题
     */
    public function releaseQuestion(){
        $user_id = UID;
        $data = I('post.');
        $data['user_id'] = $user_id;
        $model = D('Admin/Question');
        $create = $model->create($data);
        if(false !== $create){
            //评论图片处理
            $photo = $data['photo'];
            if ($photo) {
                $photo = explode(',', $photo);
                $num = count($photo);
                if ($num > 9) $this->apiReturn(V(0, '图片上传最多9张！'));
            }
            $question_id = $model->add($data);
            if(!$question_id) $this->apiReturn(V(0, $model->getError()));
            $questionImgModel = D('Admin/QuestionImg');
            if ($photo) {
                foreach ($photo as &$value) {
                    $data_img['item_id'] = $question_id;
                    $data_img['img_path'] = $value;
                    $questionImgModel->add($data_img);
                }
                unset($value);
            }
            add_key_operation(3, $question_id);
            $this->apiReturn(V(1, '问题发布成功！'));
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 问题类型列表
     */
    public function getQuestionTypeList(){
        $model = D('Admin/QuestionType');
        $list = $model->getQuestionTypeList();
        array_unshift($list['info'], array('id' => 0, 'type_name' => '所有领域', 'sort' => 1));
        $this->apiReturn(V(1, '问题类型列表获取成功！', $list['info']));
    }

    /**
     * @desc 发布答案
     */
    public function releaseAnswer(){
        $user_id = UID;
        $data = I('post.');
        $data['user_id'] = $user_id;
        $model = D('Admin/Answer');
        $create = $model->create($data);
        if(false !== $create){
            //评论图片处理
            $photo = $data['photo'];
            if($photo){
                $photo = explode(',', $photo);
                $num = count($photo);
                if($num > 9) $this->apiReturn(V(0, '图片上传最多9张！'));
            }
            $answer_id = $model->add($data);
            if(!$answer_id)$this->apiReturn(V(0, $model->getError()));
            $questionImgModel = D('Admin/QuestionImg');
            if ($photo) {
                foreach ($photo as &$value) {
                    $data_img['item_id'] = $answer_id;
                    $data_img['img_path'] = $value;
                    $data_img['type'] = 2;
                    $questionImgModel->add($data_img);
                }
                unset($value);
            }
            $incWhere = array('id' => $data['question_id']);
            D('Admin/Question')->setQuestionInc($incWhere, 'answer_number');//问题回答数
            add_key_operation(4, $answer_id);
            $this->apiReturn(V(1, '回答成功！'));
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 首页数据
     */
    public function getHomeData()
    {
        $keywords = I('keywords', '', 'trim');
        $city_id = I('city_id', '', 'trim');
        $where = array('a.city_name' => $city_id);
        unset($where['a.city_name']);
        $userLikeTags = D('Admin/User')->getUserField(array('user_id' => UID), 'like_tags');
        if($userLikeTags){
            $userLikeTags .= ',0';
            $where['a.question_type'] = array('in', $userLikeTags);
        }
        else{
            $where['a.question_type'] = 0;
        }
        if ($keywords) $where['question_title'] = array('like', '%' . $keywords . '%');
        $map['_complex'] = $where;
        $map['_logic'] = 'or';
        $map['a.user_id'] = UID;
        $maps['_complex'] = $map;
        $maps['a.disabled'] = 1;
        $maps['_logic'] = 'and';
        $model = D('Admin/Question');
        $field = 'u.nickname,u.head_pic,a.id,a.like_number,a.browse_number,a.answer_number,a.add_time,a.question_title';
        $question = $model->getQuestionList($maps, $field);
        $question_list = $question['info'];
        foreach ($question_list as &$val) {
            $val['head_pic'] = $val['head_pic'] ? $val['head_pic'] : DEFAULT_IMG;
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $img_where = array('type' => 1, 'item_id' => $val['id']);
            $val['question_img'] = D('Admin/QuestionImg')->getQuestionImgList($img_where);
        }
        unset($val);
        $array = array();
        $array['question_list'] = $question_list;
        $this->apiReturn(V(1, '获取成功！', $array));
    }

    /**
     * @desc 获取问题详情
     */
    public function getQuestionDetail(){
        $question_id = I('question_id', 0, 'intval');
        $where = array('id' => $question_id, 'disabled' => 1);
        $quesModel = D('Admin/Question');
        $questionDetail = $quesModel->getQuestionDetail($where);
        if(!$questionDetail) $this->apiReturn(V(0, '问题详情获取失败！'));
        $releaseInfo = D('Admin/User')->getUserInfo(array('user_id' => $questionDetail['user_id']), 'nickname,head_pic');
        $questionDetail['add_time'] = time_format($questionDetail['add_time']);
        $questionDetail['head_pic'] = $releaseInfo['head_pic'] ? strval($releaseInfo['head_pic']) : DEFAULT_IMG;
        $questionDetail['nickname'] = strval($releaseInfo['nickname']);
        $ques_img_where = array('type' => 1, 'item_id' => $question_id);
        $questionImg = D('Admin/QuestionImg')->getQuestionImgList($ques_img_where);
        $answer_where = array('question_id' => $question_id);
        $answerModel = D('Admin/Answer');
        $answer_list = $answerModel->getAnswerList($answer_where);
        $questionPointsModel = D('Admin/QuestionPoints');
        $points_where = array('item_id' => $question_id, 'type' => 1, 'operate_type' => 2, 'user_id' => UID);
        $points_info = $questionPointsModel->getQuestionPointsInfo($points_where);
        if(!$points_info){
            $quesModel->setQuestionInc($where, 'browse_number');
            $questionPointsModel->add($points_where);
        }
        $questionDetail['is_self'] = 0;
        if($questionDetail['user_id'] == UID) $questionDetail['is_self'] = 1;
        $is_optimum = $answerModel->getAnswerDetail(array('question_id' => $question_id, 'is_optimum' => 1));
        $questionDetail['is_optimum'] = 0;
        if($is_optimum) $questionDetail['is_optimum'] = 1;
        $returnArray = array('question' => $questionDetail, 'question_img' => $questionImg, 'answer_list' => $answer_list['info']);
        $this->apiReturn(V(1, '问题详情获取成功！', $returnArray));
    }

    /**
     * @desc 删除回答答案
     */
    public function delAnswer(){
        $answer_id = I('answer_id', 0, 'intval');
        $answer_model = D('Admin/Answer');
        $res = $answer_model->where(array('id' => $answer_id, 'user_id' => UID))->save(array('disabled' => 0));
        if(false !== $res){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '删除失败！'));
        }
    }

    /**
     * @desc 删除问题
     */
    public function delQuestion(){
        $question_id = I('question_id', 0, 'intval');
        $answer_count = D('Admin/Answer')->getAnswerNum(array('question_id' => $question_id));
        if($answer_count > 0) $this->apiReturn(V(0, '该问题下已有回答，不可删除！'));
        $questionRes = D('Admin/Question')->where(array('id' => $question_id, 'user_id' => UID))->save(array('disabled' => 0));
        if(false !== $questionRes){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '操作错误！'));
        }
    }

    /**
     * @desc 问题点赞
     */
    public function likeQuestion(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D("Admin/QuestionPoints");
        $where = array(
            'item_id' => $data['item_id'],
            'user_id' => $data['user_id'],
            'operate_type' => 1,
            'type' => 1
        );
        $info = $model->getQuestionPointsInfo($where);
        if($info) $this->apiReturn(V(0, '您已经对该问题点过赞！'));
        M()->startTrans();
        $create = $model->create($data);
        if(false !== $create){
            $res = $model->add($data);
            if(false !== $res){
                $incWhere = array('id' => $data['item_id']);
                $qRes = D('Admin/Question')->setQuestionInc($incWhere, 'like_number');
                if(false !== $qRes){
                    $model->add($where);
                    M()->commit();
                    $this->apiReturn(V(1, '点赞成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, '点赞失败！'));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 回答点赞
     */
    public function likeAnswer(){
        $data = I('post.');
        $data['user_id'] = UID;
        if(!$data['type']) $data['type'] = 2;
        $model = D("Admin/QuestionPoints");
        $where = array(
            'item_id' => $data['item_id'],
            'user_id' => $data['user_id'],
            'operate_type' => 1,
            'type' => 2
        );
        $info = $model->getQuestionPointsInfo($where);
        if($info) $this->apiReturn(V(0, '您已经对该回答点过赞！'));
        M()->startTrans();
        $create = $model->create($data);
        if(false !== $create){
            $res = $model->add($data);
            if(false !== $res){
                $incWhere = array('id' => $data['item_id']);
                $qRes = D('Admin/Answer')->setAnswerInc($incWhere, 'like_number');
                if(false !== $qRes){
                    $model->add($where);
                    M()->commit();
                    $this->apiReturn(V(1, '点赞成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, '点赞失败！'));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 设置答案为最佳答案
     */
    public function settingAnswerOptimum(){
        $answer_id = I('answer_id', 0, 'intval');
        $question_id = I('question_id', 0, 'intval');
        if(!$answer_id || !$question_id) $this->apiReturn(V(0, '请传入合法的参数！'));
        $where = array('id' => $answer_id, 'question_id' => $question_id);
        $res = D('Admin/Answer')->settingOptimum($where);
        $this->apiReturn($res);
    }

    /**
     * @desc 我的提问列表
     */
    public function getPersonalQuestion(){
        $where = array('a.user_id' => UID, 'a.disabled' => 1);
        $model = D('Admin/Question');
        $field = 'u.nickname,u.head_pic,a.id,a.like_number,a.browse_number,a.answer_number,a.add_time,a.question_title';
        $question = $model->getQuestionList($where, $field);
        $question_list = $question['info'];
        foreach($question_list as &$val){
            $val['nickname'] = strval($val['nickname']);
            $val['head_pic'] = !empty($val['head_pic']) ? strval($val['head_pic']) : DEFAULT_IMG;
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $img_where = array('type' => 1, 'item_id' => $val['id']);
            $val['question_img'] = D('Admin/QuestionImg')->getQuestionImgList($img_where);
        }
        unset($val);
        $this->apiReturn(V(1, '获取成功！', $question_list));
    }

    /**
     * @desc 我的回答列表
     */
    public function getPersonalAnswer(){
        $where = array('a.user_id' => UID);
        $model = D('Admin/Answer');
        $answer_field = 'a.id,a.answer_content,a.add_time,a.question_id,a.is_anonymous,u.nickname,u.head_pic';
        $answer = $model->getAnswerList($where, $answer_field);
        $answerList = $answer['info'];
        $ques_model = D('Admin/Question');
        $ques_img_model = D('Admin/QuestionImg');
        $ques_field = 'id,question_title,question_content,question_type,like_number,browse_number,answer_number,add_time';
        foreach($answerList as &$val){
            $t_ques_where = array('id' => $val['question_id']);
            $val['question_detail'] = $ques_model->getQuestionDetail($t_ques_where, $ques_field);
            $val['question_detail']['add_time'] = time_format($val['question_detail']['add_time'], 'Y-m-d');
            $img_where = array('type' => 1, 'item_id' => $val['question_id']);
            $val['question_img'] = $ques_img_model->getQuestionImgList($img_where);
        }
        $this->apiReturn(V(1, '', $answerList));
    }

    /**
     * @desc 联系人关系列表
     */
    public function getContactsRelationList(){
        $model = D('Admin/ContactsRelation');
        $list = $model->getContactsRelationList();
        if($list){
            foreach ($list['info'] as $key => $value) {
                $list['info'][$key]['relation_img'] = C('IMG_SERVER').$value['relation_img'];
            }

            $this->apiReturn(V(1, '联系人关系列表获取成功！', $list['info']));
        }
        else{
            $this->apiReturn(V(0, '获取联系人关系列表失败！'));
        }
    }

    /**
     * @desc 获取联系人列表
     */
    public function getContactsList(){
        $where = array('c.user_id' => UID);
        $model = D('Admin/Contacts');
        $list = $model->getContactsList($where);
        if($list){
            $this->apiReturn(V(1, '联系人列表获取成功！', $list['info']));
        }
        else{
            $this->apiReturn(V(0, '联系人列表获取失败！'));
        }
    }

    /**
     * @desc 紧急联系人添加/编辑
     */
    public function editContacts(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D('Admin/Contacts');
        if($data['id'] > 0){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
    }

    /**
     * @desc 获取紧急联系人详情
     */
    public function getContactsInfo(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/Contacts');
        $res = $model->getContactsInfo($where);
        if($res){
            $this->apiReturn(V(1, '联系人详情获取成功！', $res));
        }
        else{
            $this->apiReturn(V(0, '联系人详情获取失败！'));
        }
    }

    /**
     * @desc 删除紧急联系人
     */
    public function deleteContacts(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/Contacts');
        $del = $model->delContacts($where);
        if(false !== $del){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '删除失败！'));
        }
    }

    /**
     * @desc 获取行业/职位信息列表
     */
    public function getPositionIndustryList(){
        $type = I('type', 1, 'intval');
        $parent_id = I('parent_id', 0, 'intval');
        $industry_id = I('industry_id', 1, 'intval');
        switch ($type){
            case 1:
                $where = array('parent_id' => 0);
                $model = D('Admin/Industry');
                $field = 'id,industry_name as name,parent_id,sort';
                $list = $model->getIndustryList($where, $field);
                foreach($list as &$val){
                    //$children = $model->getIndustryList(array('parent_id' => $val['id']), $field);
                    //foreach ($children as &$c) $c['sel'] = 0; unset($c);
                    //$val['children'] = $children;
                    $val['sel'] = 0;
                }
                unset($val);
                break;
            case 2:
                $where = array('parent_id' => $parent_id);
                if($industry_id) $where['industry_id'] = $industry_id;
                $model = D('Admin/Position');
                $field = 'id,position_name as name,parent_id,sort';
                $list = $model->getPositionList($where, $field, '', false);
                unset($val);
                break;
            default:
                $this->apiReturn(V(0, '不合法的数据类型！'));
        }
        $this->apiReturn(V(1, '列表信息获取成功！', $list));
    }

    /**
     * @desc 列表功能
     */
    public function getAssistList(){
        $type = I('type', 0, 'intval');
        switch($type){
            case 1:
                $model = D('Admin/Education');
                $field = 'id,education_name as name';
                $list = $model->getEducationList(array(), $field);
                break;
            case 2:
                $model = D('Admin/CompanyNature');
                $field = 'id,nature_name as name';
                $list = $model->getCompanyNatureList(array(), $field);
                break;
            case 3:
                $work_nature = C('WORK_NATURE');
                foreach ($work_nature as $key => $value) {
                    $list[$key]['id'] = $key;
                    $list[$key]['name'] = $value;
                }
                break;
            case 4:
                $company_size = C('COMPANY_SIZE');
                foreach ($company_size as $key => $value) {
                    $list[$key]['id'] = $key;
                    $list[$key]['name'] = $value;
                }
                break;
            case 5:
                $cert_type = C('CERT_TYPE');
                $m = 0;
                $list = array();
                foreach ($cert_type as $key => $value) {
                    $list[$m]['id'] = $key;
                    $list[$m]['name'] = $value;
                    $m++;
                }
                break;
            case 6:
                $cert_type = C('SHAN_LANGUAGE');
                $m = 0;
                $list = array();
                foreach ($cert_type as $key => $value) {
                    $list[$m]['id'] = $key;
                    $list[$m]['name'] = $value;
                    $m++;
                }
                break;
            case 7:
                $age = C('SHAN_AGE');
                $m = 0;
                $list = array();
                foreach ($age as $key => $value) {
                    $list[$m]['id'] = $key;
                    $list[$m]['name'] = $value;
                    $m++;
                }
                break;
            default:
                $this->apiReturn(V(0, '不合法的数据类型！'));
        }
        $this->apiReturn(V(1, '列表获取成功！', $list));
    }

    /**
     * @desc 获取标签
     */
    public function getTags(){
        $type = I('type', 0, 'intval');
        if(!in_array($type, array(1,2,3,4,5))) $this->apiReturn(V(0, '标签类型不合法！'));
        $user_tags = D('Admin/User')->getUserField(array('user_id' => UID), 'like_tags');
        if(1 == $type){
            $user_tags = D('Admin/Resume')->where(array('user_id' => UID))->getField('career_label');
        }
        $user_tags = explode(',', $user_tags);
        if(4 == $type){
            $list = D('Admin/QuestionType')->getQuestionTypeList(array(), true, 'id,type_name as tags_name');
        }
        else{
            $model = D('Admin/Tags');
            $where = array('tags_type' => $type);
            $list = $model->getTagsList($where);
        }
        foreach($list as &$val){
            $val['sel'] = 0;
            if(in_array($val['id'], $user_tags)) $val['sel'] = 1;
            if(1 == $type && in_array($val['tags_name'], $user_tags)) $val['sel'] = 1;
        }
        unset($val);
        $this->apiReturn(V(1, '标签列表获取成功！', $list));
    }

    /**
     * @desc 获取公司列表
     */
    public function getCompanyList(){
        $keywords = I('keywords', '', 'trim');
        $where = array('company_name' => array('like', '%'.$keywords.'%'));
        $list = D('Admin/Company')->getCompanyList($where);
        $this->apiReturn(V(1, '公司列表获取成功！', $list['info']));
    }

     /**
     * @desc 增加公司
     */
    public function saveCompany(){
        $name = I('name', '', 'trim');
        $compay_model = M('company');
        $where['company_name'] = $name;
        $info = $compay_model->where($where)->find();
        if (empty($info)) {
            $data['company_name'] = $name;
            $compay_model->add($data);
        }
        $this->apiReturn(V(1, '操作成功'));
    }


    /**
     * @desc 获取用户银行卡号列表
     */
    public function getUserBankList(){
        $where = array('user_id' => UID);
        $model = D('Admin/UserBank');
        $list = $model->getUserBankList($where);
        foreach($list['info'] as &$val){
            $val['num_string'] = substr($val['bank_num'], -4);
            $val['isTouchMove'] = 0;
        }
        $this->apiReturn(V(1, '银行卡号列表获取成功!', $list['info']));
    }

    /**
     * @desc 添加/编辑用户银行卡号信息
     */
    public function editUserBank(){
        $data = I('post.');
        $model = D('Admin/UserBank');
        if($data['id'] > 0){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
    }

    /**
     * @desc 获取银行卡号信息
     */
    public function getUserBankInfo(){
        $id = I('post.id', 0, 'intval');
        $where = array('user_id' => UID);
        if($id) $where['id'] = $id;
        $model = D('Admin/UserBank');
        $info = $model->getUserBankInfo($where);
        if($info){
            $info['string_num'] = substr($info['bank_num'], -4);
            $this->apiReturn(V(1, '银行卡信息获取成功！', $info));
        }
        else{
            $this->apiReturn(V(1, '至少添加一张银行卡！', array()));
        }
    }

    /**
     * @desc 删除银行卡号
     */
    public function deleteUserBank(){
        $id = I('post.id');
        $where = array('user_id' => UID, 'id' => $id);
        $model = D('Admin/UserBank');
        $res = $model->deleteUserBank($where);
        if(false !== $res){
            $this->apiReturn(V(1, '银行卡号删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '操作错误！'));
        }
    }

    /**
     * @desc 用户提现
     */
    public function userWithdraw(){
        if(!check_is_auth(UID)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }
        $user_id = UID;
        $amount = I('amount', 0, 'trim');
        $bank_id = I('bank_id', 0, 'intval');
        if($amount <= 0) $this->apiReturn(V(0, '请输入合法的提现金额！'));
        $regex = '/^\d+(\.\d{1,2})?$/';
        if(!preg_match($regex, $amount)){
            $this->apiReturn(V(0, '提现金额小数点不能超过两位！'));
        }
        $user_model = D('Admin/User');
        $bank_where = $user_where = array('user_id' => $user_id);
        $bank_model = D('Admin/UserBank');
        $bank_where['id'] = $bank_id;
        $bank_info = $bank_model->getUserBankInfo($bank_where);
        if(!$bank_info) $this->apiReturn(V(0, '未找到相关的银行卡号信息！'));
        $user_info = $user_model->getUserInfo($user_where, 'withdrawable_amount,frozen_money');
        $user_withdraw_amount = $user_info['withdrawable_amount'];
        $amount = yuan_to_fen($amount);
        if($amount > $user_withdraw_amount) $this->apiReturn(V(0, '可提现金额不足！'));
        M()->startTrans();
        $user_account_model = D('Admin/UserAccount');
        $accountData = array(
            'user_id' => UID,
            'money' => $amount,
            'type' => 1,
            'payment' => 3,
            'brank_no' => $bank_info['bank_num'],
            'brank_name' => $bank_info['bank_name'],
            'brank_user_name' => $bank_info['cardholder'],
            'trade_no' => 'T'.randNumber(18)
        );
        $account_res = $user_account_model->add($accountData);
        if(!$account_res){
            M()->rollback();
            $this->apiReturn(V(0, '提现数据写入失败！'));
        }
        $withdrawable_amount = $user_withdraw_amount - $amount;
        $user_frozen_money = $user_info['frozen_money'] + $amount;
        $save_data = array('frozen_money' => $user_frozen_money, 'withdrawable_amount' => $withdrawable_amount);
        $user_res = $user_model->saveUserData($user_where, $save_data);
        if(!$user_res){
            M()->rollback();
            $this->apiReturn(V(0, '用户信息修改失败！'));
        }
        else{
            account_log($user_id, $amount, 1, '提现-待审核！', $account_res);
            M()->commit();
            $this->apiReturn(V(1, '提现申请成功，请等待审核！'));
        }
    }

    /**
     * @desc 创建简历
     */
    public function writeResume(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D('Admin/Resume');
        $resume_where = array('user_id' => UID);
        $resume_info = $model->where($resume_where)->find();
        $data['age'] = strtotime($data['age']);
        $data['job_area'] = rtrim($data['job_area'], ',');
        $address = explode(' ', $data['address']);
        $data['first_degree'] = str_replace('请选择', '', $data['first_degree']);
        $data['first_degree'] = str_replace('不限', '', $data['first_degree']);
        if($address){
            $address[0] = rtrim($address[0], ',');
            $data['address'] = implode(' ', $address);
        }
        if($resume_info){
            $data['id'] = $resume_info['id'];
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
                if(false !== $res){
                    echo json_encode(V(1, '保存成功！'));
                    set_time_limit(0);
                    fastcgi_finish_request();
                    refreshUserTags(false, $data['id'], array('job_position' => $data['position_id'], 'job_area' => $data['job_area']));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if($res > 0){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
    }

    /**
     * @desc 获取简历基本资料
     */
    public function getResumeInfo(){
        $where = array('user_id' => UID);
        $model = D('Admin/Resume');
        $res = $model->getResumeInfo($where);
        $res['head_pic'] = $res['head_pic'] ? $res['head_pic'] : DEFAULT_IMG;
        if($res){
            $address = explode(' ' ,$res['address']);
            $res['address_p'] = $address[0];
            unset($address[0]);
            $res['address'] = str_replace($res['address_p'].' ', '', $res['address']);
            $res['age'] = time_format($res['age'], 'Y-m-d');
            $this->apiReturn(V(1, '简历获取成功！', $res));
        }
        $auth_field = M('Resume')->getDbFields();
        $return = array();
        foreach($auth_field as &$val){
            $return[$val] = '';
        }
        $return['address_p'] = '';
        $this->apiReturn(V(1, '获取资料失败！', $return));
    }

    /**
     * @desc 自我介绍
     */
    public function saveIntroduce(){
        $data = I('post.', '');
        $model = D('Admin/Resume');
        $res = $model->where(array('user_id' => UID))->save($data);
        if(false !== $res){
            $this->apiReturn(V(1, '保存成功！'));
        }
        else{
            $this->apiReturn(V(0, '保存失败！'));
        }
    }

    /**
     * @desc 写工作经历
     */
    public function writeResumeWork(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D('Admin/ResumeWork');
        if($data['resume_id'] < 1) $data['resume_id'] = D('Admin/Resume')->getResumeField(array('user_id' => UID), 'id');
        if($data['resume_id'] < 1) $this->apiReturn(V(0, '请先添加简历！'));
        $hr_mobile = $data['mobile'];
        $hr_name = $data['hr_name'];
        if(!$data['endtime']) $data['is_current'] = 1;
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = strtotime($data['endtime']);
        if($data['is_current'] == 1) $data['endtime'] = 0;
        if($data['endtime'] && $data['starttime'] > $data['endtime']){
            $this->apiReturn(V(0, '结束时间不能小于开始时间！'));
        }
        if(!isMobile($hr_mobile)) $this->apiReturn(V(0, '请输入合法的HR联系方式！'));
        if(!$hr_name) $this->apiReturn(V(0, '请输入HR姓名！'));
        $data['work_mobile'] = $hr_mobile;
        $data['work_hr_name'] = $hr_name;
        if($data['id'] > 0){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            M()->startTrans();
            $create = $model->create($data, 1);
            if (false !== $create){
                $res = $model->add($data);
                if($res > 0){
                    $resumeAuth = array('resume_id' => $data['resume_id'], 'hr_name' => $hr_name, 'hr_mobile' => $hr_mobile, 'user_id' => UID, 'work_id' => $res);
                    //简历验证
                    $auth_res = D('Admin/ResumeAuth')->changeResumeAuth($resumeAuth);
                    /*if(false !== $auth_res){
                        sendMessageRequest($hr_mobile, '《闪荐》简历信息邀请您验证！');
                    }*/
                    M()->commit();
                    $this->apiReturn(V(1, '保存成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, $model->getError()));
            }
        }
    }

    /**
     * @desc 删除工作经历
     */
    public function deleteResumeWork(){
        $id = I('post.id');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/ResumeWork');
        $res = $model->deleteResumeWork($where);
        if($res){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '删除失败！'));
        }
    }

    /**
     * @desc 获取工作经历详情
     */
    public function getResumeWorkInfo(){
        $id = I('post.id');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/ResumeWork');
        $res = $model->getResumeWorkInfo($where);
        if($res){
            $res['starttime'] = time_format($res['starttime'], 'Y-m-d');
            if($res['endtime']){
                $res['endtime'] = time_format($res['endtime'], 'Y-m-d');
            }
            else{
                $res['endtime'] = '至今';
            }
            $res['mobile'] = strval($res['work_mobile']);
            $res['hr_name'] = strval($res['work_hr_name']);
            $this->apiReturn(V(1, '经历详情获取成功！', $res));
        }
        else{
            $this->apiReturn(V(0, '获取失败！'));
        }
    }

    /**
     * @desc 填写简历教育经历
     */
    public function writeResumeEdu(){
        $data = I('post.');
        if($data['resume_id'] < 1) $data['resume_id'] = D('Admin/Resume')->getResumeField(array('user_id' => UID), 'id');
        if($data['resume_id'] < 1) $this->apiReturn(V(0, '请先添加简历！'));
        $model = D('Admin/ResumeEdu');
        if(!$data['endtime']) $data['is_current'] = 1;
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = strtotime($data['endtime']);
        if($data['is_current'] == 1) $data['endtime'] = 0;
        if($data['endtime'] && $data['starttime'] > $data['endtime']){
            $this->apiReturn(V(0, '结束时间不能小于开始时间！'));
        }
        if($data['id'] > 0){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '学历信息保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if($res > 0){
                    $this->apiReturn(V(1, '学历信息保存成功！'));
                }
                else{
                    $this->apiReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
    }

    /**
     * @desc 获取简历教育背景详情
     */
    public function getResumeEduInfo(){
        $id = I('post.id');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/ResumeEdu');
        $res = $model->getResumeEduInfo($where);
        if($res){
            $res['starttime'] = time_format($res['starttime'], 'Y-m-d');
            if($res['endtime']){
                $res['endtime'] = time_format($res['endtime'], 'Y-m-d');
            }
            else{
                $res['endtime'] = '至今';
            }
            $this->apiReturn(V(1, '', $res));
        }
        else{
            $this->apiReturn(V(0, '获取失败！'));
        }
    }

    /**
     * @desc 删除教育经历
     */
    public function deleteResumeEdu(){
        $id = I('post.id');
        $where = array('id' => $id, 'user_id' => UID);
        $model = D('Admin/ResumeEdu');
        $res = $model->deleteResumeEdu($where);
        if($res){
            $this->apiReturn(V(1, '删除成功！'));
        }
        else{
            $this->apiReturn(V(0, '删除失败！'));
        }
    }

    /**
     * @desc 评价简历
     */
    public function scoreResume(){
        $data = I('post.');
        $data['user_id'] = UID;
        $model = D('Admin/ResumeEvaluation');
        $resume_info = D('Admin/Resume')->getResumeInfo(array('id' => $data['resume_id']));
        if(!$resume_info) $this->apiReturn(V(0, '请先填写简历！'));
        $create = $model->create($data);
        if(false !== $create){
            $res = $model->add($data);
            if($res){
                //非本人评价简历额外获得令牌完成任务
                if($resume_info['user_id'] != UID){
                    $task_id = 2;
                    add_task_log(UID, $task_id);
                }
                $this->apiReturn(V(1, '评价成功！'));
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 获取简历详情
     * @extra 根据推荐列表获取简历详情
     */
    public function getResumeDetail(){
        $user_id = UID;
        $id = I('post.id');
        $interview_id = I('interview_id', 0, 'intval');
        $resume_id = I('post.resume_id');
        $is_open = I('post.is_open', 0, 'intval');
        $auth_id = I('auth_id', 0, 'intval');
        $resumeModel = D('Admin/Resume');
        if(!$resume_id) $resume_id = $resumeModel->getResumeField(array('user_id' => $user_id), 'id');
        $resumeWorkModel = D('Admin/ResumeWork');
        $resumeEduModel = D('Admin/ResumeEdu');
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $recruitResumeModel = D('Admin/RecruitResume');
        $educationModel = D('Admin/Education');
        if(!$id) $id = D('Admin/Interview')->getInterviewField(array('id' => $interview_id), 'recruit_resume_id');
        $recruit_where = array('id' => $id);
        $recommend_info = $recruitResumeModel->getRecruitResumeField($recruit_where, 'recruit_id,recommend_label,recommend_voice,id,hr_user_id');
        $resume_where = array('id' => $resume_id);
        $resumeDetail = $resumeModel->getResumeInfo($resume_where);
        if(!$resumeDetail && $user_id == $resumeDetail['user_id']) $this->apiReturn(V(0, '您还没有填写简历！'));
        $resumeDetail['head_pic'] = $resumeDetail['head_pic'] ? $resumeDetail['head_pic'] : DEFAULT_IMG;
        $introduced_detail = array('introduced_voice' => strval($resumeDetail['introduced_voice']), 'introduced_time' => strval($resumeDetail['introduced_time']), 'introduced' => strval($resumeDetail['introduced']));
        $resume_career = explode(',', $resumeDetail['career_label']);
        $resume_career = array_filter($resume_career);
        $tags = array();
        foreach($resume_career as &$val){
            $tags[] = array('tags_name' => $val, 'sel' => 1);
        }
        unset($val);
        $where = array('resume_id' => $resume_id);
        $resumeWorkList = $resumeWorkModel->getResumeWorkList($where);
        $resumeEduList = $resumeEduModel->getResumeEduList($where);

        $m = 1;
        foreach($resumeWorkList as &$wval){
            $wval['starttime'] = time_format($wval['starttime'], 'Y-m-d');
            $wval['endtime'] = $wval['endtime'] ? time_format($wval['endtime'], 'Y-m-d') : '至今';
            $wval['sort'] = $m;
            $m++;
            $eval['describe'] = autoBreak($wval['describe']);
        }
        unset($wval);
        $edu_list = $educationModel->select();
        $edu_help = array();
        foreach($edu_list as &$edu_val){
            $edu_help[$edu_val['education_name']] = $edu_val['suffix_img'];
        }
        unset($edu_val);
        foreach($resumeEduList as &$eval){
            $eval['starttime'] = time_format($eval['starttime'], 'Y-m-d');
            $eval['endtime'] = $eval['endtime'] ? time_format($eval['endtime'], 'Y-m-d') : '至今';
            $eval['suffix_img'] = $edu_help[$eval['degree']] ? C('IMG_SERVER').$edu_help[$eval['degree']] : '';
            $eval['describe'] = autoBreak($eval['describe']);
        }
        unset($eval);
        $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
        $sum = array_sum(array_values($resumeEvaluation));
        $avg = round($sum/(count($resumeEvaluation)), 2);
        $recommend_info['interview_id'] = $interview_id;
        $recommend_info['auth_id'] = $auth_id;
        $hrModel = D('Admin/HrResume');
        $auth_model = D('Admin/ResumeAuth');
        $hr_info = $hrModel->getHrResumeInfo(array('hr_user_id' => UID, 'resume_id' => $resume_id));
        $hr_auth_info = $auth_model->getResumeAuthInfo(array('resume_id' => $resume_id, 'hr_id' => UID));
        if($user_id != $resumeDetail['user_id'] && !$hr_info && !$hr_auth_info){
            if(!$is_open) $resumeDetail['mobile'] = '****';
        }
        //获取悬赏金额
        $recruit_id = $recommend_info['recruit_id'];
        $commission = M('Recruit')->where(array('id'=>$recruit_id))->getField('commission');
        $get_resume_money = C('GET_RESUME_MONEY');
        $work_resume_money = fen_to_yuan($commission) - $get_resume_money;
        $resumeDetail['get_resume_money'] = $get_resume_money;
        $resumeDetail['work_resume_money'] = $work_resume_money;
        $resumeDetail['age'] = time_format($resumeDetail['age'], 'Y-m-d');
        if(!$resumeDetail['job_intension']) $resumeDetail['job_intension'] = M('Position')->where(array('id' => $resumeDetail['position_id']))->getField('position_name');
        $resumeDetail['job_area'] = rtrim($resumeDetail['job_area'], ',');
        $resumeDetail['address'] = rtrim($resumeDetail['address'], ',');
        $userModel = D('Admin/User');
        $user_type = $userModel->getUserField(array('user_id' => $user_id), 'user_type');
        $return = array('detail' => $resumeDetail, 'resume_work' => $resumeWorkList, 'resume_edu' => $resumeEduList, 'resume_evaluation' => $resumeEvaluation, 'evaluation_avg' => $avg, 'recruit_resume' => $recommend_info, 'is_open' => $is_open, 'introduce' => $introduced_detail, 'career_label' => $tags);
        if(1 == $user_type){
            $hr_voice = D('Admin/HrResume')->getHrResumeField(array('user_id' => $user_id, 'resume_id' => $resume_id), 'recommend_voice');
            $return['hr_voice'] = $hr_voice;
            $return['recruit_resume']['recommend_voice'] = $hr_voice;
        }
        $this->apiReturn(V(1, '简历获取成功！', $return));
    }

    /**
     * @desc 保存职业标签
     */
    public function saveCareerLabel(){
        $data = I('post.', '');
        $where = array('user_id' => UID);
        $res = M('resume')->where($where)->save($data);
        if(false !== $res){
            $this->apiReturn(V(1, '保存成功！'));
        }
        else{
            $this->apiReturn(V(0, '保存失败！'));
        }
    }


    /**
     * @desc 简历认证列表
     */
    public function authResumeList(){
        $where = array('a.hr_id' => UID);
        $model = D('Admin/ResumeAuth');
        $list = $model->getResumeAuthList($where);
        $this->apiReturn(V(1, '认证列表', $list['info']));
    }

    /**
     * @desc 简历认证确认/放弃
     */
    public function confirmResumeAuth(){
        /*if(!check_is_auth(UID)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }*/
        $id = I('post.id');
        $auth_result = I('post.auth_result');
        if(1 == $auth_result) $this->apiReturn(V(1, '操作成功！'));
        $recommend_label = I('post.recommend_label');
        if(!in_array($auth_result, array(1, 2))) $this->apiReturn(V(0, '认证状态有误！'));
        $user_where = array('user_id' => UID);
        $userModel = D('Admin/User');
        $user_info = $userModel->getUserInfo($user_where);
        $resume_auth_where = array('id' => $id, 'hr_id' => UID);
        $resumeAuthModel = D('Admin/ResumeAuth');
        $resume_auth_info = $resumeAuthModel->getResumeAuthInfo($resume_auth_where);
        if(!$resume_auth_info || $resume_auth_info['hr_mobile'] != $user_info['mobile']) $this->apiReturn(V(0, '认证信息有误！'));
        if($resume_auth_info['auth_result'] != 0) $this->apiReturn(V(0, '该简历已经被认证过！'));
        $save_data['auth_result'] = $auth_result;
        $save_data['auth_time'] = NOW_TIME;
        $resumeWorkModel = D('Admin/ResumeWork');
        M()->startTrans();
        $res = $resumeAuthModel->saveResumeAuthData($resume_auth_where, $save_data);
        if(1 == $auth_result){
            if(false !== $res){
                $resumeWorkModel->saveResumeWorkData(array('id' => $resume_auth_info['work_id']), array('state' => 2));
                M()->commit();
                $this->apiReturn(V(1, '操作成功！'));
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, '操作失败！'));
            }
        }
        else{
            $hr_resume_model = D('Admin/HrResume');
            $data = array();
            $data['hr_user_id'] = UID;
            $data['resume_id'] = $resume_auth_info['resume_id'];
            $data['recommend_label'] = $recommend_label;
            $create = $hr_resume_model->create($data, 1);
            if(false !== $create){
                $hr_resume_result = $hr_resume_model->add($data);
                if(false !== $hr_resume_result && false !== $res){
                    $task_id = 5;
                    add_task_log(UID, $task_id);
                    add_key_operation(8, $resume_auth_info['resume_id']);
                    refreshUserTags(UID, $resume_auth_info['resume_id']);
                    $resumeWorkModel->saveResumeWorkData(array('id' => $resume_auth_info['work_id']), array('state' => 1));
                    M()->commit();
                    $this->apiReturn(V(1, '认证操作成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, $hr_resume_model->getError()));
                }
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, $hr_resume_model->getError()));
            }
        }
    }

    /**
     * @desc 简历详情 简历认证确认/放弃
     */
    public function detailResumeAuth(){
        /*if(!check_is_auth(UID)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }*/
        $resume_id = I('post.resume_id');
        $auth_result = I('post.auth_result');
        if(1 == $auth_result) $this->apiReturn(V(1, '操作成功！'));
        if(!in_array($auth_result, array(1, 2))) $this->apiReturn(V(0, '认证状态有误！'));
        $user_where = array('user_id' => UID);
        $userModel = D('Admin/User');
        $user_info = $userModel->getUserInfo($user_where);
        $resume_auth_where = array('hr_mobile' => $user_info['mobile'], 'hr_id' => UID, 'resume_id' => $resume_id);
        $resumeAuthModel = D('Admin/ResumeAuth');
        $resume_auth_info = $resumeAuthModel->getResumeAuthInfo($resume_auth_where);
        if(!$resume_auth_info) $this->apiReturn(V(0, '你不是该简历申请的HR！'));
        if($resume_auth_info['auth_result'] != 0) $this->apiReturn(V(0, '该简历已经被认证过！'));
        $save_data['auth_result'] = $auth_result;
        $save_data['auth_time'] = NOW_TIME;
        $resumeWorkModel = D('Admin/ResumeWork');
        M()->startTrans();
        $res = $resumeAuthModel->saveResumeAuthData($resume_auth_where, $save_data);
        if(1 == $auth_result){
            if(false !== $res){
                $resumeWorkModel->saveResumeWorkData(array('id' => $resume_auth_info['work_id']), array('state' => 2));
                M()->commit();
                $this->apiReturn(V(1, '认证操作成功！'));
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, '认证操作失败！'));
            }
        }
        else{
            $hr_resume_model = D('Admin/HrResume');
            $data = array();
            $data['hr_user_id'] = UID;
            $data['resume_id'] = $resume_id;
            $create = $hr_resume_model->create($data, 1);
            if(false !== $create){
                $hr_resume_result = $hr_resume_model->add($data);
                if(false !== $hr_resume_result && false !== $res){
                    $task_id = 5;
                    add_task_log(UID, $task_id);
                    add_key_operation(8, $resume_id);
                    refreshUserTags(UID, $resume_id);
                    $resumeWorkModel->saveResumeWorkData(array('id' => $resume_auth_info['work_id']), array('state' => 1));
                    M()->commit();
                    $this->apiReturn(V(1, '认证操作成功！'));
                }
                else{
                    M()->rollback();
                    $this->apiReturn(V(0, $hr_resume_model->getError()));
                }
            }
            else{
                M()->rollback();
                $this->apiReturn(V(0, $hr_resume_model->getError()));
            }
        }
    }

    /**
     * @desc 删除简历认证
     */
    public function delResumeAuth(){
        $auth_id = I('auth_id', 0, 'intval');
        $auth_model = D('Admin/ResumeAuth');
        $auth_info = $auth_model->getResumeAuthInfo(array('id' => $auth_id, 'hr_id' => UID));
        if(!$auth_info) $this->apiReturn(V(0, '不属于你的认证简历！'));
        if($auth_info['auth_result'] != 0) $this->apiReturn(V(0, '仅可删除待审核认证简历'));
        $res = $auth_model->where(array('id' => $auth_id))->delete();
        if(false !== $res){
            $this->apiReturn(V(1, '操作成功！'));
        }
        else{
            $this->apiReturn(V(0, '操作有误！'));
        }
    }

    /**
     * @desc 简历技能评分详情
     */
    public function resumeEvaluationDetail(){
        $resume_id = I('resume_id', 0, 'intval');
        //if(!$resume_id) $this->apiReturn(V(0, '简历标识不能为空！'));
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $resumeModel = D('Admin/Resume');
        $resumeWorkModel = D('Admin/ResumeWork');
        $userModel = D('Admin/User');
        $user_type = $userModel->getUserField(array('user_id' => UID), 'user_type');
        $where = array('resume_id' => $resume_id);
        $resume_where = array('id' => $resume_id);
        $resume_info = $resumeModel->getResumeInfo($resume_where);
        //if(!$resume_info) $this->apiReturn(V(0, '简历详情获取失败！'));
        $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
        $sum = array_sum(array_values($resumeEvaluation));
        $avg = round($sum/(count($resumeEvaluation)), 2);
        $resumeWorkList = $resumeWorkModel->getResumeWorkList($where, 'company_name,position,starttime,endtime', 'endtime desc');
        $total_time = 0;
        foreach($resumeWorkList as &$val){
            $val['time_differ'] = $val['endtime'] - $val['starttime'];
            $total_time += $val['endtime'] - $val['starttime'];
            $val['year_limit'] = year_limit($val['starttime'], $val['endtime']);
            unset($val['starttime']);
            unset($val['endtime']);
        }
        unset($val);
        foreach($resumeWorkList as &$val){
            $val['percent'] = round($val['time_differ'] / $total_time * 100, 2);
        }
        unset($val);
        $return = array('evaluation' => $resumeEvaluation, 'avg' => $avg, 'work_list' => $resumeWorkList);
        //if(1 == $user_type){
            $self = $resumeEvaluationModel->getResumeEvaluationInfo(array('resume_id' => $resume_id, 'user_id' => UID));
            if(!$self) {
                $db = $resumeEvaluationModel->getDbFields();
                $a_k = array_values($db);
                $self = array();
                foreach($a_k as &$a_keys){
                    $self[$a_keys] = 0;
                }
                unset($a_keys);
            }
            $return['self'] = $self;
        //}
        $this->apiReturn(V(1,  '评价详情获取成功！', $return));
    }

    /**
     * @desc hr人才库列表/悬赏推荐人才列表
     */
    public function getHrResumeList(){
        $where = array('h.hr_user_id' => UID);
        $recruit_id = I('recruit_id', 0, 'intval');
        //悬赏参数/根据悬赏筛选人才库
        if($recruit_id){
            $recruitModel = D('Admin/Recruit');
            $recruitWhere = array('id' => $recruit_id);
            $recruit_info = $recruitModel->getRecruitInfo($recruitWhere, 'position_id,job_area');
            $job_area = $recruit_info['job_area'];
            $position = $recruit_info['position_id'];
            $where1 = array();
            if($job_area){
                $limit_position = strrpos($job_area, ',');
                $where1[] = 'r.`job_area` like \''.$job_area.'%\' or r.`job_area` = \''.substr($job_area, 0, $limit_position).'\' or r.`job_area` = \''.substr($job_area, 0, $limit_position + 1).'\'';
            }
            $where2 = array();
            if($position){
                $where2[] = 'r.`position_id` = '.$position;
            }
            $position_string = implode(' or ', $where2);
            $area_string = implode(' or ', $where1);
            $map = '('.$position_string.') and ('.$area_string.')';
            if(count($where1) == 0) $map = $position_string;
            if(count($where2) == 0) $map = $area_string;
            $where['_string'] = $map;
            $where['r.is_incumbency'] = 1;//接受推荐[根据悬赏筛选人才列表]
        }
        $model = D('Admin/HrResume');
        $keywords = I('keywords', '', 'trim');
        $where['r.is_audit'] = 1;
        if($keywords) $where['r.true_name'] = array('like', '%'.$keywords.'%');
        $list = $model->getHrResumeList($where);
        foreach($list['info'] as &$val){
            $val['add_time'] = time_format($val['add_time']);
            $val['sel'] = 0;
            $val['age'] = time_to_age($val['age']);
        }
        $this->apiReturn(V(1, '人才列表获取成功！', $list['info']));
    }

    /**
     * @desc 根据简历获取到悬赏列表
     */
    public function personalRecommendResume(){
        $resume_id = I('resume_id', 0, 'intval');
        $model = D('Admin/Resume');
        $recruitModel = D('Admin/Recruit');
        $resume_where = array('id' => $resume_id);
        $hr_resume_info = $model->getResumeInfo($resume_where);
        $job_area = $hr_resume_info['job_area'];//工作地区
        $position = $hr_resume_info['job_intension'];//工作职位
        $job_arr = explode('|', $job_area);
        $pos_arr = explode('|', $position);
        $where1 = array();
        if($job_area){
            foreach($job_arr as &$val){
                $where1[] = '`job_area` like \'%'.$val.'%\'';
            }
            unset($val);
        }
        $where2 = array();
        if($position){
            foreach($pos_arr as &$val){
                $where2[] = '`position_name` like \'%'.$val.'%\'';
            }
            unset($val);
        }
        $position_string = implode(' or ', $where2);
        $area_string = implode(' or ', $where1);
        $map = '('.$position_string.') and ('.$area_string.')';
        if(count($where1) == 0) $map = $position_string;
        if(count($where2) == 0) $map = $area_string;
        $recruit_where = array('_string' => $map);
        $recruit_where['status'] = 1;
        $recruit_list = $recruitModel->getRecruitList($recruit_where);
        $this->apiReturn(V(1, '悬赏列表获取成功！', $recruit_list['info']));
    }

    /**
     * @desc 确认向悬赏推荐简历
     * @extra resume_id string 多个简历同时推荐使用,分开
     */
    public function confirmRecruitResume(){
        if(!check_is_auth(UID)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }
        $data = I('post.');
        $hr_user_id = UID;
        $recruitModel = D('Admin/Recruit');
        $resumeModel = D('Admin/Resume');
        $recruitResumeModel = D('Admin/RecruitResume');
        $hrResumeModel = D('Admin/HrResume');
        $recruit_where = array('id' => $data['recruit_id']);
        $recruit_info = $recruitModel->getRecruitInfo($recruit_where);
        $data['resume_id'] = str_replace('undefined', '', $data['resume_id']);
        if(!$data['resume_id']) $this->apiReturn(V(0, '请选择推荐人才！'));
        if(!$recruit_info) $this->apiReturn(V(0, '获取不到对应的悬赏信息！'));
        if($hr_user_id == $recruit_info['hr_user_id']) $this->apiReturn(V(0, '不能向自己推荐简历！'));
        if($recruit_info['is_post'] == 2) $this->apiReturn(V(0, '该悬赏职位已招满！'));
        //$resume_where = array('id' => $data['resume_id']);
        //$resume_info = $resumeModel->getResumeInfo($resume_where);
        $data['hr_user_id'] = $hr_user_id;
        $data['recruit_hr_uid'] = $recruit_info['hr_user_id'];
        $data['add_time'] = NOW_TIME;
        if(false !== strpos($data['resume_id'], ',')){
            $addAllArr = array();
            $resume_arr = explode(',', $data['resume_id']);
            $resume_arr = array_unique($resume_arr);
            foreach($resume_arr as &$val){
                $hr_recommend_where = array('resume_id' => $val, 'hr_user_id' => UID);
                $hr_resume_info = $hrResumeModel->getHrResumeInfo($hr_recommend_where);
                $valid_info = $recruitResumeModel->getRecruitResumeInfo(array('recruit_id' => $data['recruit_id'], 'resume_id' => $val, 'hr_user_id' => UID));
                $resume_incumbency = $resumeModel->getResumeField(array('id' => $val), 'is_incumbency');
                if($resume_incumbency != 1) continue;
                if($valid_info) continue;
                $data['recommend_label'] = $hr_resume_info['recommend_label'];
                $data['resume_id'] = $val;
                $addAllArr[] = $data;
            }
            if(count($addAllArr)  == 0) $this->apiReturn(V(1, '推荐成功！'));
            $res = $recruitResumeModel->addAll($addAllArr);
            if($res){
                add_key_operation(6, $data['recruit_id']);
                $this->apiReturn(V(1, '推荐成功！'));
            }
            $this->apiReturn(V(0, '推荐失败！'));
        }
        else{
            $hr_recommend_where = array('resume_id' => $data['resume_id'], 'hr_user_id' => UID);
            $hr_resume_info = $hrResumeModel->getHrResumeInfo($hr_recommend_where);
            $data['recommend_label'] = $hr_resume_info['recommend_label'];
            $valid_info = $recruitResumeModel->getRecruitResumeInfo(array('recruit_id' => $data['recruit_id'], 'resume_id' => $data['resume_id'], 'hr_user_id' => UID));
            $resume_incumbency = $resumeModel->getResumeField(array('id' => $data['resume_id']), 'is_incumbency');
            if($resume_incumbency != 1) $this->apiReturn(V(0, '该简历已关闭推荐功能。'));
            if($valid_info) $this->apiReturn(V(0, '你已经向该悬赏推荐过此人！'));
            $create = $recruitResumeModel->create($data);
            if(false !== $create){
                $res = $recruitResumeModel->add($data);
                if($res){
                    add_key_operation(6, $data['recruit_id']);
                    $this->apiReturn(V(1, '推荐成功！'));
                }
                else{
                    $this->apiReturn(V(0, $recruitResumeModel->getError()));
                }
            }
            else{
                $this->apiReturn(V(0, $recruitResumeModel->getError()));
            }
        }
    }

    /**
     * @desc HR录入推荐语
     */
    public function entryResumeRecommend(){
        $resume_id = I('resume_id', 0, 'intval');
        $model = D('Admin/HrResume');
        $user_id = UID;
        $data['recommend_voice'] = I('post.recommend_voice', '', 'trim');
        if(empty($data['recommend_voice'])) $this->apiReturn(V(0, '推荐语不能为空'));
        $data['recommend_label'] = I('post.recommend_label', '', 'trim');
        $res = $model->where(array('hr_user_id' => $user_id, 'resume_id' => $resume_id))->save($data);
        if(false !== $res){
            $this->apiReturn(V(1, '推荐语录入成功！'));
        }
        else{
            $this->apiReturn(V(0, '推荐语录入失败！'));
        }
    }

    /**
     * @desc 发起面试
     */
    public function launchInterview(){
        $data = I('post.');
        $data['hr_user_id'] = UID;
        $model = D('Admin/Interview');
        $create = $model->create($data, 1);
        if(false !== $create){
            $res = $model->add($data);
            if($res){
                add_key_operation(7, $res);
                $this->apiReturn(V(1, '面试发起成功！'));
            }
            else{
                $this->apiReturn(V(0, $model->getError()));
            }
        }
        else{
            $this->apiReturn(V(0, $model->getError()));
        }
    }

    /**
     * @desc 获取面试管理列表
     */
    public function getInterviewList(){
        $where = array('i.hr_user_id' => UID);
        $model = D('Admin/Interview');
        $resumeInterviewList = $model->getInterviewList($where);
        foreach($resumeInterviewList['info'] as &$val){
            $val['update_time'] = time_format($val['update_time']);
            $val['resume_time'] = time_format($val['resume_time']);
            $val['state_string'] = interview_state($val['state']);
            $val['age'] = time_to_age($val['age']);
        }
        unset($val);
        $this->apiReturn(V(1, '面试列表获取成功！', $resumeInterviewList['info']));
    }

    /**
     * @desc 入职/放弃
     */
    public function updateInterviewState(){
        $id = I('post.id');
        $state = I('post.state',0, 'intval');
        if(!in_array($state, array(1, 2))) $this->apiReturn(V(0, '面试状态不正确！'));
        $where = array('hr_user_id' => UID, 'id' => $id);
        $model = D('Admin/Interview');
        $save_data = array('state' => $state);
        $interviewInfo = $model->getInterviewInfo($where);
        if(!$interviewInfo || $interviewInfo['state'] != 0) $this->apiReturn(V(0, '面试状态不对！'));
        if($state == 1){
            $recruitResumeModel = D('Admin/RecruitResume');
            $recruit_resume_info = $recruitResumeModel->getRecruitResumeInfo(array('id' => $interviewInfo['recruit_resume_id']));
            $recruitModel = D('Admin/Recruit');
            $recruit_info = $recruitModel->getRecruitInfo(array('id' => $recruit_resume_info['recruit_id']));
            if($recruit_info['is_post'] == 2) $this->apiReturn(V(0, '该悬赏已经招聘完成！'));
        }
        $res = $model->saveInterviewData($where, $save_data);
        if(false !== $res){
            if($state == 1){
                $recruitModel->recruitPayOff($interviewInfo['recruit_resume_id'], 2);
            }
            $this->apiReturn(V(1, '操作成功！'));
        }
        else{
            $this->apiReturn(V(0, '操作失败！'));
        }
    }

    /**
     * @desc 生成二维码
     */
    public function getInterviewCodeDetail(){
        $hr_user_id = UID;
        $resume_id = I('resume_id', 0, 'intval');
        if(!$hr_user_id || !$resume_id) $this->apiReturn(V(0, '传入合法的参数！'));
        $resume_model = D('Admin/Resume');
        $resume_where = array('id' => $resume_id);
        $resume_info = $resume_model->getResumeInfo($resume_where, 'true_name');
        if(!$resume_info) $this->apiReturn(V(0, '获取不到相关的简历信息！'));
        $hr_company_model = D('Admin/CompanyInfo');
        $hr_company_where = array('user_id' => $hr_user_id);
        $company_info = $hr_company_model->getCompanyInfoInfo($hr_company_where, 'company_name');
        if(!$company_info) $this->apiReturn(V(0, '获取不到相关的公司信息！'));
        $company_name = $company_info['company_name'];
        $true_name = $resume_info['true_name'];

        header("Content-Type: text/html;charset=utf-8");
        //引入二维码生成插件
        vendor("phpqrcode.phpqrcode");

        // 生成的二维码所在目录+文件名
        $path = "Uploads/Picture/QRcode/";//生成的二维码所在目录
        if(!file_exists($path)){
            mkdir($path, 0700,true);
        }
        $time = time().'.png';//生成的二维码文件名
        $fileName = $path.$time;//1.拼装生成的二维码文件路径

        $data = C('IMG_SERVER').'/index.php/Invite/Invite/index/true_name/'.$true_name.'/company_name/'.$company_name.'/hr_id/'.$hr_user_id.'/resume_id/'.$resume_id;//2.生成二维码的数据(扫码显示该数据)

        $level = 'L';  //3.纠错级别：L、M、Q、H

        $size = 10;//4.点的大小：1到10,用于手机端4就可以了

        ob_end_clean();//清空缓冲区
        \QRcode::png($data, $fileName, $level, $size);//生成二维码
        //文件名转码
        $file_name = iconv("utf-8","gb2312",$time);
        $file_path = $_SERVER['DOCUMENT_ROOT'].'/'.$fileName;
        //获取下载文件的大小
        $file_size = filesize($file_path);
        //
        $file_temp = fopen ( $file_path, "r" );
        //返回的文件
        header("Content-type:application/octet-stream");
        //按照字节大小返回
        header("Accept-Ranges:bytes");
        //返回文件大小
        header("Accept-Length:".$file_size);
        //这里客户端的弹出对话框
        header("Content-Disposition:attachment;filename=".$time);

        fread ( $file_temp, filesize ( $file_path ) );
        fclose ( $file_temp );
        $this->apiReturn(V(1, '二维码内容获取成功！', array('url' => C('IMG_SERVER').'/'.$fileName)));
    }



    /**
     * 会员充值
     */
    public function recharge() {
        $recharge_money = I('recharge_money', '');
        if (!$recharge_money || $recharge_money < 1) {
            $this->apiReturn(V(0, '充值金额不能小于1元！'));
        }
        $regex = '/^\d+(\.\d{1,2})?$/';
        if(!preg_match($regex, $recharge_money)){
            $this->apiReturn(V(0, '充值金额小数点最多两位！'));
        }
        $code = I('wx_code', '');
        if (!$code) {
            $this->apiReturn(V(0,'wx_code不能为空'));
        }
        require_once("Plugins/WxPay2/example/jsapi.php");
        $rechargeSn = 'C' . date('YmdHis', time()) . '-' . UID;

        $wxData['order_no'] = $rechargeSn;
        $wxData['payment_money'] = $recharge_money;
        $wxData['notify_url'] = C('Wxpay')['notify_url'];
        $open_id = $this->getOpenid($code);
        $wxPay = new \WXPay();
        $doResult = $wxPay->index($open_id,$wxData);
        $this->apiReturn(V(1,'支付信息',json_decode($doResult)));

    }

    /**
     * @return openid
     */
    protected function getOpenid($code)
    {
        $wxConfig = C('WxPay');
        $appid = $wxConfig['app_id'];
        $secret = $wxConfig['appsecret'];
        $userModel= M('User');
        $openid = $userModel->where(array('user_id'=>UID))->getField('open_id');
        if ($openid) {
            return $openid;
        } else {
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";

            $res = $this->_httpGet($url);
            //取出openid
            $data = json_decode($res,true);
            $this->data = $data;
            $openid = $data['openid'];
            $userModel->where(array('user_id'=>UID))->setField('open_id',$openid);
            return $openid;
        }

    }

    protected function _httpGet($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT,500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST , false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }
    /**
     * @desc 充值提现列表
     */
    public function getWithRechargeList(){
        $user_id = UID;
        $where['user_id'] = UID;
        $where['change_type'] = array('in', [0,1,4,5,7]);
        $data = D('Admin/AccountLog')->getAccountLogByPage($where,'change_type as type,user_money as money,change_time as add_time, change_desc,order_sn');
        $account_model = D('Admin/UserAccount');
        foreach($data['info'] as &$val){
            $val['add_time'] = time_format($val['add_time']);
            if($val['type'] == 1){
                $val['state'] = $account_model->getAccountField(array('id' => $val['order_sn']), 'state');
                $type_string = '提现-'.C('ACCOUNT_STATE')[$val['state']];
            }
            elseif($val['type'] == 0){
                $type_string = '充值';
            }
            else{
                $type_string = $val['change_desc'];
            }
            $val['type_string'] = $type_string;
            $val['money'] = fen_to_yuan($val['money']);
        }
        unset($val);
        $user_account = D('Admin/User')->getUserInfo(array('user_id' => $user_id), 'user_money,withdrawable_amount');
        $total_account = fen_to_yuan($user_account['user_money']);
        $user_withdraw = fen_to_yuan($user_account['withdrawable_amount']);
        $this->apiReturn(V(1, '', array('account' => $total_account, 'can_account' => $user_withdraw, 'list' => $data['info'])));
    }

    /**
     * @desc 城市选择回调
     */
    public function cityNameCallback(){
        $user_id = UID;
        $city_name = I('city_name', '', 'trim');
        $where = array('user_id' => $user_id);
        $save = array('city_name' => $city_name);
        $res = D('Admin/User')->saveUserData($where, $save);
        if(false !== $res){
            $this->apiReturn(V(1, '保存成功！'));
        }
        else{
            $this->apiReturn(V(0, '保存失败！'));
        }
    }

    /**
     * @desc HR注册顺序排名/HR简历库数量排名
     */
    public function hrRanking(){
        $user_id = UID;
        $user_model = D('Admin/User');
        $hr_resume = D('Admin/HrResume');
        $userRanking = $user_model->getUserRankingInfo($user_id);
        $hrResumeRanking = $hr_resume->getHrResumeRankingInfo($user_id);
        $userFields = $user_model->getUserInfo(array('user_id'=>UID), 'head_pic,nickname,user_name');
        if($userFields['head_pic']) {
            $head_pic = $userFields['head_pic'];
        } else {
            $head_pic = 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png';
        };
        if(!empty($userFields['nickname'])) {
            $nickname = $userFields['nickname'];
        } else {
            $nickname = $userFields['user_name'];
        }
        if(!$hrResumeRanking) $hrResumeRanking = '999+';
        $this->apiReturn(V(1, '', array('user_ranking' => $userRanking, 'resume_ranking' => $hrResumeRanking,'head_pic'=>$head_pic,'nickname'=>$nickname)));
    }

    /**
     * @desc HR简历库页统计
     */
    public function hrResumeStatistic(){
        $user_id = UID;
        $hr_resume_where = array('hr_user_id' => $user_id);
        $hr_resume_model = D('Admin/HrResume');
        $hr_resume_count = $hr_resume_model->getHrResumeCount($hr_resume_where);

        $add_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $recruit_where = array('add_time' => array('gt', $add_time));
        $recruit_model = D('Admin/Recruit');
        $recruit_count = $recruit_model->getRecruitCount($recruit_where);

        $interview_where = array('r.hr_user_id' => $user_id, 'i.state' => 0);
        $interview_model = D('Admin/Interview');
        $interview_count = $interview_model->getInterviewCount($interview_where);

        $auth_where = array('hr_id' => $user_id);
        $auth_model = D('Admin/ResumeAuth');
        $auth_count = $auth_model->getResumeAuthCount($auth_where);

        $return_data = array();
        $return_data['hr_resume'] = $hr_resume_count;
        $return_data['recruit_num'] = $recruit_count;
        $return_data['auth_num'] = $auth_count;
        $return_data['interview_num'] = $interview_count;
        $this->apiReturn(V(1, '', $return_data));
    }
    //获取工作地区
    public function getJobAreaList() {
        $where['user_id'] = array('eq', UID);
        $info = D('Admin/Resume')->getResumeInfo($where, 'job_area');
        $data = [];
        if (!empty($info['job_area'])) {
            $area = explode(',', $info['job_area']);
            foreach ($area as $k=>$v) {
                $data[$k]['id'] = $k;
                $data[$k]['tags_name'] = $v;
                $data[$k]['sel'] = 1;
            }
        }

        $this->apiReturn(V(1, '工作地区', $data));
    }
    public function saveJobArea() {
        $job_area = I('job_area', '');
        $res = M('Resume')->where(array('user_id'=>UID))->setField('job_area', $job_area);
        if ($res ===false) {
            $this->apiReturn(V(0, '保存失败'));
        }else {
            $this->apiReturn(V(1, '保存成功'));
        }
    }



    /**
     * @desc 身份认证信息
     */
    public function certInfo(){
        $user_id = UID;
        $status = -1;
        $desc = '资料未上传';
        $model = D('Admin/UserAuth');
        $info = $model->getAuthInfo(array('user_id' => $user_id));
        if($info){
            $status = $info['audit_status'];
            $desc = $info['audit_desc'];
            if($status == 1) $desc = '审核通过';
        }
        $this->apiReturn(V(1, '', array('status' => $status, 'desc' => $desc)));
    }

    /**
     * @desc 已注册账号绑定微信账号
     */
    public function bindThirdNumber(){
        $user_id = UID;
        $wx_code = I('wx_code', '', 'trim');
        $open_id = getOpenId($wx_code);
        $user_model = D('Admin/User');
        $type_info = $user_model->getUserInfo(array('user_id' => UID));
        $user_type = $type_info['user_type'];
        if($type_info['wx']) $this->apiReturn(V(0, '账号已经绑定微信账号！'));
        $user_info = $user_model->getUserInfo(array('wx' => $open_id, 'user_type' => $user_type));
        if($user_info) $this->apiReturn(V(0, '微信账号已被其他手机号绑定！'));
        $save = array('wx' => $open_id);
        $save_res = $user_model->saveUserData(array('user_id' => $user_id), $save);
        if(false !== $save_res){
            $this->apiReturn(V(1, '绑定成功！'));
        }
        else{
            $this->apiReturn(V(0, '绑定出现错误！'));
        }
    }

    public function sendMessage(){
        if($this->checkSignature()) $this->apiReturn(V(1, '成功'));
        $this->apiReturn(V(0, '失败'));
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = C('ACCESS_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }

}