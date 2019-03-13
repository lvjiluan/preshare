<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class IndexController extends HrCommonController{
    public function Index(){
        require_once(APP_PATH . '/NewHr/Conf/menu.php');
        $menus = array();
        foreach($modules as $key => $val){
            $menus[$key]['label'] = $val['label'];
            foreach($val['items'] as $skey => $sval){
                $menus[$key]['items'][$skey]['label'] = $sval['label'];
                $menus[$key]['items'][$skey]['action'] = $sval['action'];
                $menus[$key]['items'][$skey]['class'] = $sval['class'];
            }
        }

        $user_id = HR_ID;
        $user_model = D('Admin/User');
        $company_model = D('Admin/CompanyInfo');
        $userFields = $user_model->getUserInfo(array('user_id' => $user_id), 'head_pic,nickname,user_name');
        if($userFields['head_pic']) {
            $head_pic = $userFields['head_pic'];
        } else {
            $head_pic = 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png';
        }
        if(!empty($userFields['nickname'])) {
            $nickname = $userFields['nickname'];
        } else {
            $nickname = $userFields['user_name'];
        }
        $company_info = $company_model->getCompanyInfoInfo(array('user_id' => $user_id));
        $returnArray = array('nickname' => $nickname, 'head_pic' => $head_pic, 'company_logo' => $company_info['company_logo'], 'company_name' => $company_info['company_name']);
        $this->info = $returnArray;
        $this->menu_list = $menus;
        $this->display();
    }
    public function welcome(){
        $user_id = HR_ID;
        $recruit_model = D('Admin/Recruit');
        //$tags = user_tags($user_id);

        $user_model = D('Admin/User');
        $hr_resume = D('Admin/HrResume');
        $accountLogModel = D('Admin/AccountLog');
        $company_model = D('Admin/CompanyInfo');
        $company_info = $company_model->getCompanyInfoInfo(array('user_id' => $user_id));
        $userRanking = $user_model->getUserRankingInfo($user_id);
        $hrResumeRanking = $hr_resume->getHrResumeRankingInfo($user_id);
        $userFields = $user_model->getUserInfo(array('user_id' => $user_id), 'head_pic,nickname,user_name');
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
        $resume_number = $hr_resume->getHrResumeCount(array('hr_user_id' => $user_id));
        if(!$hrResumeRanking) $hrResumeRanking = '999+';
        $_time = time_list(3);
        $start_time = $_time['start'];
        $end_time = $_time['end'];
        $account_where = array('change_type' => array('in', array(2, 3, 6)), 'change_time' => array('between', array($start_time, $end_time)), 'user_id' => $user_id);
        $sum_money = $accountLogModel->getAccountLogMoneySum($account_where);
        $_year_time = time_list(4);
        $_year_start = $_year_time['start'];
        $_year_end = $_year_time['end'];
        $_year_account_where = array('change_type' => array('in', array(2, 3, 6)), 'change_time' => array('between', array($_year_start, $_year_end)), 'user_id' => $user_id);
        unset($_year_account_where['change_time']);
        $_year_money = $accountLogModel->getAccountLogMoneySum($_year_account_where);
        $return_array = array('user_ranking' => $userRanking, 'resume_ranking' => $hrResumeRanking,'head_pic'=>$head_pic,'nickname'=>$nickname, 'resume_number' => $resume_number, 'month_amount' => fen_to_yuan($sum_money), 'year_amount' => fen_to_yuan($_year_money));

        $string_where = D('Admin/HrResume')->getHrTags($user_id, '');
        if(is_array($string_where)){
            $map = false;
        }
        else{
            $map = $string_where;
        }
        /*$map = '';
        if(count($tags) > 0){
            $where1 = array();
            foreach($tags as &$val){
                $val['job_area'] = rtrim($val['job_area'], ',');
                if(false !== strpos($val['job_position'], '|')){
                    $pos = 'in ('.str_replace('|', ',', $val['job_position']).')';
                }
                else{
                    $pos = '= '.$val['job_position'];
                }
                $where1[] = ' (`job_area` like \''.$val['job_area'].'%\' and `position_id` '.$pos.') ';
            }
            unset($val);
            $map = implode(' or ', $where1);
        }*/
        if($map) $where['_string'] = $map;
        if(!$map) $where['_string'] = 'r.id < 1';//无符合条件悬赏展示所有的悬赏
        //$where['r.hr_user_id'] = array('neq', $user_id);
        $where['r.is_post'] = array('lt', 2);
        $where['r.is_shelf'] = 1;
        $where['r.status'] = 1;

        $recruit_model = D('Admin/Recruit');
        $position_model = D('Admin/Position');
        $list = $recruit_model->getHrRecruitList($where,'r.id, r.position_name, r.recruit_num, r.commission, r.add_time, r.position_id,c.company_name,r.job_area,r.experience,r.base_pay,r.merit_pay,r.language_ability,r.nature');

        $recruit_id = $list['id'];
        foreach($list['info'] as &$val){
            $t_parent_id = $position_model->getPositionField(array('id' => $val['position_id']), 'parent_id');
            $position_name = $position_model->getPositionField(array('id' => $t_parent_id), 'position_name');
            $val['position_name'] = $position_name .'-'. $val['position_name'];
            if(in_array($val['id'], $recruit_id)) $val['resume_matching'] = 1;
        }
        unset($val);
        $this->company_info = $company_info;
        $this->ranking = $return_array;
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
}