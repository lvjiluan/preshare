<?php
namespace Core\Controller;
use Common\Controller\CommonController;
/**
 * 结算
 * 1、悬赏推荐/入职所得金额7日自动到账
 * 2、悬赏推荐30日自动入职
 * 3、未认证简历30日发短信验证
 * 4、悬赏30日退还剩余赏金
 */
class SettleTestController extends CommonController {
    public function __construct(){
        parent::__construct();
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','5120M');
    }

    /**
     * @desc 用户冻结金额/可提现金额变动
     */
    public function userFrozenMoneyRelieve(){
        $account_model = D('Admin/AccountLog');
        $user_model = D('Admin/User');
        $account_time_limit = NOW_TIME - ( 5 * 86400);
        $account_where = array('diss' => 0, 'user_id' => array('gt', 0), 'change_type' => array('in', array(2, 3)),
            //'change_time' => array('lt', $account_time_limit)
        );
        $now_time = NOW_TIME;
        $list = $account_model->getAccountLogFrozenList($account_where);
        foreach($list as &$val){
            //$t_time_days = get_days($val['change_time'], $now_time);
            //if($t_time_days >= 7){
                M()->startTrans();
                //释放用户冻结金额
                $decrease_res = $user_model->decreaseUserFieldNum($val['user_id'], 'frozen_money', $val['user_money']);
                //增加用户可提现金额
                $increase_res = $user_model->increaseUserFieldNum($val['user_id'], 'withdrawable_amount', $val['user_money']);
                //修改用户资金记录状态
                $account_res = $account_model->where(array('log_id' => $val['log_id']))->setField('diss', 1);
                if(false !== $decrease_res && false !== $increase_res && false !== $account_res){
                    M()->commit();
                }
                else{
                    M()->rollback();
                }
            //}
            //else{
                //continue;
            //}
        }
        unset($val);
    }

    /**
     * @desc 悬赏30日自动入职
     */
    public function recruitInterview(){
        $interviewModel = D('Admin/Interview');
        $recruitModel = D('Admin/Recruit');
        $limit_time = NOW_TIME - 30 * 86400;
        $recruit_where = array('is_post' => array('lt', 2), 'status' => 1, 'add_time' => array('lt', $limit_time));
        $recruit_list = $recruitModel->getRecruitList($recruit_where, false, '', false);
        foreach($recruit_list as &$value){
            $interview_where = array('i.state' => 0, 'r.recruit_id' => $value['id']);
            $list = $interviewModel->interviewList($interview_where);
            if(count($list) > 0){
                foreach($list as &$val){
                    $recruitInfo = $recruitModel->getRecruitInfo(array('id' => $value['id']));
                    $interviewCount = $interviewModel->interviewRecruitCount(array('r.recruit_id' => $value['id'], 'i.state' => 1));
                    if($interviewCount >= $recruitInfo['recruit_num']) continue;
                    M()->startTrans();
                    $interview_res = $interviewModel->saveInterviewData(array('id' => $val['id']), array('state' => 1));
                    $recruit_res = $recruitModel->where(array('id' => $value['id']))->setInc('recruit_num');
                    $recruit_status = 1;
                    if($recruitInfo['recruit_num'] - 1 == $interviewCount) $recruit_status = 2;
                    $recruit_post_res = $recruitModel->where(array('id' => $value['id']))->save(array('is_post' => $recruit_status));
                    if(false !== $interview_res && false !== $recruit_res && false !== $recruit_post_res){
                        M()->commit();
                    }
                    else{
                        M()->rollback();
                    }
                }
                unset($val);
            }
            else{
                $recruitModel->where(array('id' => $value['id']))->save(array('status' => 0));
            }
        }
        unset($value);
    }

    /**
     * @desc 未认证简历短信发送提醒
     */
    public function resumeAuth(){
        $model = D('Admin/ResumeAuth');
        $messageModel = D('Admin/SmsMessage');
        $limit_time = NOW_TIME - 30 * 86400;
        $where = array('auth_result' => 0,
            //'add_time' => array('lt', $limit_time)
        );
        $list = $model->resumeAuthList($where);
        $start = mktime(0,0,0,date('m'),1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
        foreach($list as &$val){
            //$message_limit = $messageModel->where(array('mobile' => $val['hr_mobile'], 'add_time' => array('between', array($start, $end)), 'type' => 99))->find();
            //if($message_limit) continue;
            $message_content = '《闪荐科技》简历认证邀请您进行认证！';
            $send_res = sendMessageRequest($val['hr_mobile'], $message_content);
            if($send_res['status']){
                $data = array(
                    'mobile' => $val['hr_mobile'],
                    'sms_content' => $message_content,
                    'sms_code' => '0000',
                    'add_time' => NOW_TIME,
                    'send_status' => $send_res['status'],
                    'user_type' => 1,
                    'type' => 99,
                    'send_response_msg' => $send_res['info']
                );
                $messageModel->add($data);
            }
        }
        unset($val);
    }

    /**
     * @desc 悬赏30日自动结算冻结赏金退还至余额
     * @extra 1、[2018-12-15] 之前的悬赏根据悬赏推荐/资金记录退还悬赏剩余赏金
     *        2、[2015-12-15] 之后的悬赏根据悬赏表last_token退还悬赏剩余赏金
     *        3、用户冻结资金减少/退还至余额
     */
    public function recruitAutomaticSettlement(){
        $recruitModel = D('Admin/Recruit');
        $user_model = D('Admin/User');
        $recruit_resume_model = D('Admin/RecruitResume');
        $limit_time = NOW_TIME - 30 * 86400;
        $dividing_time = mktime(0, 0, 0, 12, 15, 2018);
        $recruit_where = array('is_post' => array('lt', 2), 'status' => 1,
            //'add_time' => array('lt', $limit_time)
            );
        $field = 'id,hr_user_id,add_time,commission,last_token';
        $recruit_list = $recruitModel->getRecruitList($recruit_where, $field, '', false);
        foreach($recruit_list as &$val){
            if($val['add_time'] < $dividing_time){
                $recommend_where = array('a.change_type' => array('in', array(2, 3)), 't.id' => $val['id']);
                $recommend_field = 'sum(user_money) as money';
                $recommend_result = $recruit_resume_model->getRecommendAccountLog($recommend_where, $recommend_field);
                $last_money = intval($recommend_result['money']);
                M()->startTrans();
                //减少用户冻结资金
                $decrease = $user_model->decreaseUserFieldNum($val['hr_user_id'], 'frozen_money', $last_money);
                //增加用户余额资金
                $increase = $user_model->increaseUserFieldNum($val['hr_user_id'], 'user_money', $last_money);
                account_log($val['hr_user_id'], $last_money, 5, '悬赏赏金退还', $val['id']);
                //清空悬赏剩余赏金
                $recruit_result = $recruitModel->saveRecruitData(array('id' => $val['id']), array('last_token' => 0));
                if(false !== $recruit_result && false !== $decrease && false !== $increase){
                    M()->commit();
                }
                else{
                    M()->rollback();
                }
            }
            else{
                if($val['last_token'] == 0) continue;
                M()->startTrans();
                //减少用户冻结资金
                $decrease = $user_model->decreaseUserFieldNum($val['hr_user_id'], 'frozen_money', $val['last_token']);
                //增加用户余额资金
                $increase = $user_model->increaseUserFieldNum($val['hr_user_id'], 'user_money', $val['last_token']);
                account_log($val['hr_user_id'], $val['last_token'], 5, '悬赏赏金退还', $val['id']);
                //清空悬赏剩余赏金
                $recruit_result = $recruitModel->saveRecruitData(array('id' => $val['id']), array('last_token' => 0));
                if(false !== $decrease && false !== $increase && false !== $recruit_result){
                    M()->commit();
                }
                else{
                    M()->rollback();
                }
            }
        }
        unset($val);
    }

    /**
     * @desc 更新用户简历标签内容
     */
    public function refreshUserTags($hr_user_id){
        if(!$hr_user_id) return true;
        $where = array();
        if($hr_user_id){
            $tags_model = M('UserTags');
            $where['h.hr_user_id'] = $hr_user_id;
            $field = 'position_id,hr_user_id,job_area';
            $resume_list = M('HrResume')->alias('h')->field($field)->join('__RESUME__ as r on h.resume_id = r.id')->where($where)->select();
            if(count($resume_list) > 0){
                $tags_model->where(array('user_id' => $hr_user_id))->delete();
                $area_help_arr = array();
                $area_arr = array();
                foreach($resume_list as &$val){
                    if(!in_array($val['position_id'] ,$area_help_arr[$val['job_area']])){
                        $area_help_arr[$val['job_area']][] = $val['position_id'];
                        if(count($area_arr[$val['job_area']]) > 0){
                            $t_pos = implode('|', $area_arr[$val['job_area']]);
                            if(strlen($t_pos.$val['position_id']) > 155){
                                $tags_model->add(array('user_id' => $hr_user_id, 'job_area' => $val['job_area'], 'job_position' => $area_arr[$val['job_area']]));
                                $area_arr[$val['job_area']] = array();
                            }
                        }

                        $area_arr[$val['job_area']][] = $val['position_id'];
                    }
                }
                unset($val);
                $tags_add_arr = array();
                $arr_keys = array_keys($area_arr);
                foreach($arr_keys as &$k_val){
                    $t_position = implode('|', $area_arr[$k_val]);
                    $tags_add_arr[] = array('user_id' => $hr_user_id, 'job_area' => $k_val, 'job_position' => $t_position);
                }
                unset($k_val);
                $res = $tags_model->addAll($tags_add_arr);
                return $res;
            }
            return true;
        }
        else{
            //暂不支持全部修改
            return true;
        }
    }
}