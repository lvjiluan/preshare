<?php
/**
 * 用户管理类 
 */
namespace Admin\Controller;
use Think\Controller;
class UserController extends CommonController {
    //会员HR列表
    public function listUser(){
        $keyword = I('keyword', '');
        $type = I('type', 0, 'intval');
        $is_auth = I('is_auth', -1, 'intval');
        $userModel = D('Admin/User');
       /// $where = array('user_type' => $type);
        if($is_auth != -1){
            if($is_auth == 1) $where['u.is_auth'] = 1;
            if($is_auth == 0){
                $where['u.is_auth'] = 0;
                $where['a.audit_status'] = 0;
            }
        }
        if ($keyword) {
            $where['mobile|user_name|nickname'] = array('like','%'.$keyword.'%');
        }
        //查询所有的用户
        $data = $userModel->getUsersList($where);
        foreach($data['userslist'] as &$val){
            $val['user_name'] = $val['nickname'] ? $val['nickname'] : $val['user_name'];
        }
        unset($val);
        $this->userslist = $data['userslist'];
        $this->page = $data['page'];
        $this->keyword = $keyword;
        $this->user_type = $type;
        $this->is_auth = $is_auth;
        $this->display('listUsers');
    }

    /**
     * @desc 用户导出
     */
    public function user_export(){
        $type = I('type', 0, 'intval');
        $where = array('user_type' => $type, 'register_time' => array('between', array(NOW_TIME - 30*86400, NOW_TIME)));
        $userModel = D('Admin/User');
        $data = $userModel->getUserExportList($where, 'user_name,mobile,nickname,email,register_time');
        $leader_arr = array('用户名', '手机号', '邮箱', '注册时间');
        $export_arr = array();
        foreach($data as &$val){
            $export_arr[] = array($val['nickname'], $val['mobile'], $val['email'], time_format($val['register_time']));
        }
        array_unshift($export_arr, $leader_arr);
        create_xls($export_arr, '闪荐科技近一个月注册用户.xls', '闪荐科技近一个月注册用户', '闪荐科技近一个月注册用户', array('A', 'B', 'C', 'D'));
    }

    /**
     * 用户详情
     */
    public function userDetail(){
        $user_id = I('user_id', 0, 'intval');
        $where['user_id'] = $user_id;
        $userModel = D('Admin/User');
        $authModel = D('Admin/UserAuth');
        $userInfo = $userModel->getUserInfo($where);
        $auth = $authModel->getAuthInfo($where);

        $auth['cert_type'] = C('CERT_TYPE')[$auth['cert_type']];
        $this->userInfo = $userInfo;
        $this->info = $auth;

        $this->display();
    }

    /**
     *用户管理启用，禁用方法
     */
    public function changeDisabled(){
        $user_id = I('user_id', 0, 'intval');
        $updateInfo = D('Admin/User')->changeDisabled($user_id);
        $this->ajaxReturn($updateInfo);
    }


    //充值列表
    public function rechargeList(){
        $keyword  = I('mobile', '', 'trim');
    	$where['ua.type'] = 0;
        $where['ua.status'] = 1;
        if ($keyword) {
            $where['u.mobile'] = array('like','%'.$keyword.'%');
        }
        $field = 'ua.id,ua.money,ua.add_time,ua.user_note,ua.payment,u.mobile';
        $list = D('Admin/UserAccount')->getUserAccountList($where, $field);
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    //会员提现列表
    public function withdrawList(){
        $keyword  = I('mobile', '', 'trim');
        $accountType = I('type', 0, 'intval');
        $where['ua.type'] = 1;
        $where['ua.status'] = 1;
        if ($keyword) {
            $where['u.mobile'] = array('like','%'.$keyword.'%');
        }
        if(1 == $accountType){
            $where['ua.state'] = array('in', array(1,3,4));
        }
        $field = 'ua.id, ua.money, ua.add_time, ua.state, u.user_name, u.user_money';
        $list = D('Admin/UserAccount')->getUserAccountList($where, $field);

        $tpl = 'withdrawList';
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->account_type = $accountType;
        $this->display($tpl);
    }

    /***
     * @desc 用户身份验证
     */
    public function editUsersAuth() {
        $id = I('user_id', 0, 'intval');
        $where['user_id'] = $id;
        $user_model = D('Admin/User');
        $result = D('Admin/UserAuth')->getAuthInfo($where);
        if (IS_POST) {
            if ($id > 0) {
                if($result['audit_status'] == 1) $this->ajaxReturn(V(0, '已审核'));
                $auth_data['audit_status'] = I('state', 2, 'intval');
                $auth_data['audit_desc'] = I('audit_desc', '', 'trim');
                $data['is_auth'] = 0;
                if($auth_data['audit_status'] == 1) $data['is_auth'] = 1;
                if($auth_data['audit_status'] == 2 && !$auth_data['audit_desc']) $this->ajaxReturn(V(0, '审核意见不能为空！'));
                $result = M('User')->where($where)->save($data);
                $auth_res = M('UserAuth')->where($where)->save($auth_data);
                if(false !== $result && false !== $auth_res){
                    $task_id = M('TaskLog')->where(array('task_id' => 1, 'user_id' => $id))->getField('id');
                    $account = D('Admin/AccountLog')->where(array('user_id' => $id, 'change_type' => 6, 'order_sn' => $task_id))->getField('user_money');
                    if($account > 0 && $auth_data['audit_status'] == 1){
                        $withdraw_amount = $account;
                    }
                    else{
                        $withdraw_amount = M('Task')->where(array('id' => 1))->getField('reward');
                    }
                    $user_model->decreaseUserFieldNum($id, 'frozen_money', $withdraw_amount);
                    $user_model->increaseUserFieldNum($id, 'withdrawable_amount', $withdraw_amount);
                    $user_mobile = D('Admin/User')->getUserField($where, 'mobile');
                    $message = $auth_data['audit_status'] == 1 ? C('SHAN_CERT_TRUE') : C('SHAN_CERT_FALSE');
                    sendMessageRequest($user_mobile, $message);
                    $this->ajaxReturn(V(1, '操作成功'));
                }
                $this->ajaxReturn(V(0, '修改失败请稍后重试！'));
            }
            else{
                $this->ajaxReturn(V(0, '资料未上传！'));
            }
        } else {
            $user_type = D('Admin/User')->getUserField($where, 'user_type');
            $result['cert_type'] = C('CERT_TYPE')[$result['cert_type']];
            $audit_desc = C('AUDIT_DESC');
            $audit_arr = array();
            $val = array_values($audit_desc);
            foreach($val as &$v) {
                $audit_arr[] = array('id' => $v, 'name' => $v);
            }
            unset($v);
            $this->audit_desc = $audit_arr;
            $this->user_type = $user_type;
            $this->assign('info', $result);
            $this->display();
        }
        
    }

    //提现通过
    public function editWithdraw(){
        $id = I('id', 0, 'intval');
        $UserAccountModel = D('Admin/UserAccount');
        if(IS_POST){
            if($id > 0){
                if(empty(I('admin_note'))) $this->ajaxReturn(V(0, '管理员备注不能为空！'));
                if($UserAccountModel->create()){
                    $result = $UserAccountModel->save();
                    if($result === false){
                        $this->ajaxReturn(V(0, '操作失败'));
                    }
                    $state = I('post.state', 1, 'intval');
                    if($state == 2){
                        $info = $UserAccountModel->getUserAccountDetail(array('id' => $id));
                        $user_model = D('Admin/User');
                        $user_model->decreaseUserFieldNum($info['user_id'], 'frozen_money', $info['money']);
                        $user_model->increaseUserFieldNum($info['user_id'], 'withdrawable_amount', $info['money']);
                    }
                    $this->ajaxReturn(V(1, '操作成功', $id));
                }
                else{
                    $this->ajaxReturn(V(0, $UserAccountModel->getError()));
                }
            }
        }
        $where['id'] = $id;
        $accountInfo = $UserAccountModel->getUserAccountInfo($where);
        $this->assign('info', $accountInfo);
        $this->display();
    }

    //审核提现
    public function returnWithdraw(){
        $id = I('id', 0, 'intval');
        $UserAccountModel = D('Admin/UserAccount');
        if (IS_POST) {
            $state = I('return_state', 1);
            if ($id > 0) {
                $data = I('post.', '');
                if(empty($data['return_desc'])) $this->ajaxReturn(V(0, '备注信息不能为空！'));
                if(empty($data['return_number'])) $this->ajaxReturn(V(0, '请认真填写银行回执单号！'));
                $state_arr = array(1 => 3, 0 => 4);
                $data['state'] = $state_arr[$data['return_state']];
                if($UserAccountModel->create()){
                    M()->startTrans();// 开启事务
                    $result = $UserAccountModel->save($data);
                    if($result === false){
                        M()->rollback(); // 事务回滚
                        $this->ajaxReturn(V(0, '操作失败'));
                    }
                    if ($state == 1) { //完成打款
                        $where['id'] = $id;
                        $accountInfo = D('Admin/UserAccount')->getUserAccountDetail($where, 'user_id, money');
                        //减少会员冻结余额
                        $setUserMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setDec('frozen_money', $accountInfo['money']);
                        if ($setUserMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        M('AccountLog')->where(array('order_sn'=>"$id"))->setField('change_desc', '提现-已完成');

                    }
                    else{
                        $where['id'] = $id;
                        $accountInfo = D('Admin/UserAccount')->getUserAccountDetail($where, 'user_id, money');

                        //减少会员冻结余额
                        $setUserForzenMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setDec('frozen_money', $accountInfo['money']);
                        if ($setUserForzenMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        //增加会员余额
                        $setUserMoney = D('Admin/User')->where('user_id='.$accountInfo['user_id'])->setInc('withdrawable_amount', $accountInfo['money']);
                        if ($setUserMoney === false) {
                            M()->rollback(); // 事务回滚
                            $this->ajaxReturn(V(0, '操作失败'));
                        }
                        //更新资金记录
                        M('AccountLog')->where(array('order_sn'=>"$id"))->setField('change_desc', '提现-被拒绝');
                    }
                    M()->commit(); // 事务提交
                    $this->ajaxReturn(V(1, '操作成功', $id));
                } else {
                    $this->ajaxReturn(V(0, $UserAccountModel->getError()));
                }

            }
        } else {
            $where['id'] = $id;
            $accountInfo = $UserAccountModel->getUserAccountInfo($where);
            $this->assign('info', $accountInfo);
            $this->display();
        }

    }

    public function del() {
        $id = I('id', 0);
        $result = V(0, '删除失败, 未知错误');
        if($id != 0){
            $where['user_id'] = array('in', $id);
            $data['status'] = 0;
            if( M('User')->data($data)->where($where)->save() !== false){
                $result = V(1, '删除成功');
            }
        }
        $this->ajaxReturn($result);
    }
}