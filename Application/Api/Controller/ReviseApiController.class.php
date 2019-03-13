<?php
/**
 * @desc 闪荐二期修改接口功能
 */
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;

class ReviseApiController extends ApiUserCommonController{

    /**
     * @desc 更改求职状态
     * @param incumbency 1、接收推荐 0、不接受推荐
     */
    public function setIncumbency(){
        $user_model = D('Admin/User');
        $incumbency = I('incumbency', 0, 'intval');
        $save = array('is_incumbency' => $incumbency);
        $user_where = array('user_id' => UID);
        $user_mobile = $user_model->getUserField($user_where, 'mobile');
        $res = $user_model->where($user_where)->save($save);
        if(false !== $res){
            $message = 1 == $incumbency ? C('SHAN_ACCEPT_RECOMMEND') : C('SHAN_REFUSED_RECOMMEND');
            sendMessageRequest($user_mobile ,$message);
            D('Admin/Resume')->saveResumeData($user_where, $save);
            $this->apiReturn(V(1, '求职状态更改成功！'));
        }
        else{
            $this->apiReturn(V(0, '求职状态更改失败！'));
        }
    }

    /**
     * @desc 获取首页数据
     * @param position_id int 导航栏目位置 1、HR  0、求职者
     * @param ad_position int banner图位置 1、首页[默认] 2、合伙人
     * @extra nav:导航栏目 notice:公告列表 banner:banner轮播图
     */
    public function getHomeData(){
        $return_list = array();
        $user_model = D('Admin/User');
        $user_type = $user_model->getUserField(array('user_id' => UID), 'user_type');
        $return_list['nav'] = $this->navList();
        $return_list['notice'] = $this->noticeList($user_type);
        $return_list['banner'] = $this->bannerList($user_type);
        $return_list['layout'] = $this->recruitTaskBanner();
        $this->apiReturn(V(1, '首页数据', $return_list));
    }

    /**
     * @desc 获取HR信息
     */
    public function hrAuxData(){
        $user_model = D('Admin/User');
        $user_info = $user_model->getUserInfo(array('user_id' => UID), 'is_incumbency,withdrawable_amount');
        $user_info['withdrawable_amount'] = fen_to_yuan($user_info['withdrawable_amount']);
        $this->apiReturn(V(1, '用户信息', $user_info));
    }

    /**
     * @desc 获取公告详情
     */
    public function getArticleInfo(){
        $article_id = I('id', 0, 'intval');
        $this->apiReturn(V(1, '', C('IMG_SERVER').'/index.php/Api/PublicApi/noticeInfo/id/'.$article_id));
    }

    /**
     * @desc 申请成为合伙人
     */
    public function applyPartner(){
        $data = I('post.', '');
        $model = D('Admin/Partner');
        $create = $model->create($data, 1);
        if(false !== $create){
            $res = $model->add($data);
            if($res){
                $this->apiReturn(V(1, '合伙人申请成功！'));
            }
        }
        $this->apiReturn(V(0, $model->getError()));
    }

    /**
     * @desc 每日任务页面
     * @extra task:任务列表 rank:HR排名信息/HR基本资料/HR公司上传logo month_amount:本月收益
     */
    public function getTaskHomeData(){
        $user_id = UID;
        $company_model = D('Admin/CompanyInfo');
        $accountLogModel = D('Admin/AccountLog');
        $userModel = D('Admin/User');
        $return_list = array();
        $return_list['task'] = $this->taskList();
        $rank_list = $this->rankList();
        $company_logo = $company_model->getCompanyInfoField(array('user_id' => $user_id), 'company_logo');
        $rank_list['company_logo'] = $company_logo;
        $return_list['rank'] = $rank_list;
        $_time = time_list(3);
        $start_time = $_time['start'];
        $end_time = $_time['end'];
        $account_where = array('change_type' => array('in', array(2, 3, 6)), 'change_time' => array('between', array($start_time, $end_time)), 'user_id' => $user_id);
        $sum_money = $accountLogModel->getAccountLogMoneySum($account_where);
        unset($account_where['change_time']);
        $total_money = $accountLogModel->getAccountLogMoneySum($account_where);
        $can_money = $userModel->getUserField(array('user_id' => $user_id), 'withdrawable_amount');
        $money = array('month_amount' => fen_to_yuan($sum_money), 'can_money' => fen_to_yuan($can_money), 'total_amount' => fen_to_yuan($total_money));
        $return_list['money'] = $money;
        $this->apiReturn(V(1, '任务首页内容', $return_list));
    }

    /**
     * @desc 获取HR基本资料
     */
    public function getHrInfo(){
        $user_id = UID;
        $user_model = D('Admin/User');
        $user_where = array('user_id' => $user_id);
        $array = array('head_pic', 'nickname', 'sex', 'age', 'like_tags');
        $user_info = $user_model->getUserInfo($user_where, $array);
        $list = D('Admin/QuestionType')->getQuestionTypeList(array(), true, 'id,type_name as tags_name');
        $user_tags = explode(',', $user_info['like_tags']);
        foreach($list as &$val){
            $val['sel'] = 0;
            if(in_array($val['id'], $user_tags)) $val['sel'] = 1;
        }
        unset($val);
        $user_info['age'] = time_format($user_info['age'], 'Y-m-d');
        if(!$user_info){
            $user_info = array();
            foreach($array as &$val) $user_info[$val] = ''; unset($val);
        }
        $this->apiReturn(V(1, '用户信息', array('user_info' => $user_info, 'list' => $list)));
    }

    /**
     * @desc 获取HR公司信息
     */
    public function getHrCompanyInfo(){
        $user_id = UID;
        $where = array('user_id' => $user_id);
        $id = I('id', 0, 'intval');
        if($id) $where = array('id' => $id);
        $array = array('id','user_id', 'company_logo', 'company_name','company_size','company_nature','company_mobile','company_email','company_industry','company_address');
        $info = D('Admin/CompanyInfo')->getCompanyInfoInfo($where);

        if(!$info) {
            foreach($array as &$value) $info[$value] = '';
            $info['company_pic'] = array();
            $info['company_address_p'] = '';
        }
        else{
            $address = explode(' ' ,$info['company_address']);
            if(count($address) > 0){
                $info['company_address_p'] = $address[0];
                unset($address[0]);
                $info['company_address'] = str_replace($info['company_address_p'].' ', '', $info['company_address']);
                $info['company_address_p'] = str_replace(',', ' ', $info['company_address_p']);
            }
        }
        $this->apiReturn(V(1 ,'编辑个人资料',$info));
    }

    /**
     * @desc 编辑HR基本资料
     */
    public function editHrDoc(){
        $user_id = UID;
        $data = I('post.', '');
        $model = D('Admin/User');
        $create = $model->create($data, 4);
        if(false !== $create){
            $data['age'] = strtotime($data['age']);
            if($data['age'] > time()) $this->apiReturn(V(0, '选择合适的出生日期'));
            $res = $model->saveUserData(array('user_id' => $user_id), $data);
            if(false !== $res){
                $this->apiReturn(V(1, '保存成功！'));
            }
        }
        $this->apiReturn(V(0, $model->getError()));
    }

    /**
     * @desc 编辑HR公司信息
     */
    public function editHrCompanyInfo(){
        $user_id = UID;
        $data = I('post.', '');
        $model = D('Admin/CompanyInfo');
        $where = array('user_id' => $user_id);
        $company_info = $model->getCompanyInfoInfo($where);
        $data['user_id'] = $user_id;
        $data['company_address_p'] = rtrim($data['company_address_p'], ',');
        $data['company_address'] = $data['company_address_p'].' '.$data['company_address'];
        if($company_info){
            $create = $model->create($data, 2);
            if(false !== $create){
                $res = $model->where($where)->save($data);
                if(false !== $res){
                    $this->apiReturn(V(1, '保存成功！'));
                }
            }
        }
        else{
           $create = $model->create($data, 1);
           if(false !== $create){
               $res = $model->add($data);
               if($res){
                   $this->apiReturn(V(1, '保存成功！'));
               }
           }
        }
        $this->apiReturn(V(0, $model->getError()));
    }

    /**
     * @desc 悬赏列表
     */
    public function getRecruitList(){
        $user_id = UID;
        $user_model = D('Admin/User');
        $user_type = $user_model->getUserField(array('user_id' => $user_id), 'user_type');
        if($user_type == 1){
            $list = $this->hrRecruitList();
        }
        else{
            $list = $this->normalRecruitList();
        }
        $this->apiReturn(V(1, '悬赏列表', $list));
    }

    /**
     * @desc 求职者投递简历
     */
    public function delivery(){
        $user_id = UID;
        $recruit_id = I('recruit_id', 0, 'intval');
        $recruit_model = D('Admin/Recruit');
        $resume_model = D('Admin/Resume');
        $user_where = array('user_id' => $user_id);
        $resume_info = $resume_model->getResumeInfo($user_where);
        if(!$resume_info) $this->apiReturn(V(0, '请先完善简历信息！'));
        /*if(!check_is_auth($user_id)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }*/
        $recruit_where = array('id' => $recruit_id);
        $recruit_info = $recruit_model->getRecruitInfo($recruit_where);
        if(!$recruit_info) $this->apiReturn(V(0, '悬赏信息获取失败！'));
        if($recruit_info['is_post'] == 2) $this->apiReturn(V(0, '该悬赏职位已招满！'));
        if($recruit_info['position_id'] != $resume_info['position_id']) $this->apiReturn(V(0, '求职岗位与悬赏不匹配！'));
        $recruit_area = explode(',', $recruit_info['job_area']);
        $resume_area = explode(',', $resume_info['job_area']);
        if($recruit_area[0] != $resume_area[0] || $recruit_area[1] != $resume_area[1]) $this->apiReturn(V(0, '求职地区与悬赏不匹配！'));
        $recruitResumeModel = D('Admin/RecruitResume');
        $valid_info = $recruitResumeModel->getRecruitResumeInfo(array('recruit_id' => $recruit_id, 'resume_id' => $resume_info['id'], 'hr_user_id' => $user_id));
        if($valid_info) $this->apiReturn(V(0, '该悬赏你已投递！'));
        $data = array('recruit_id' => $recruit_id, 'recruit_hr_uid' => $recruit_info['hr_user_id'], 'resume_id' => $resume_info['id'], 'hr_user_id' => $user_id, 'is_open' => 1);
        $res = $recruitResumeModel->add($data);
        if($res){
            $this->apiReturn(V(1, '投递成功！'));
        }
        else{
            $this->apiReturn(V(0, $recruitResumeModel->getError()));
        }
    }

    /**
     * @desc 求职者投递历史
     */
    public function deliveryHistory(){
        $user_id = UID;
        $model = D('Admin/RecruitResume');
        $where = array('r.hr_user_id' => $user_id);
        $field = 'r.add_time,c.company_name,c.id,re.position_name,re.position_id,c.company_logo,re.id as recruit_id,re.base_pay,re.merit_pay,re.job_area';
        $list = $model->getDeliveryHistory($where, $field);
        $position_model = D('Admin/Position');
        foreach($list['info'] as &$val){
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d H:i:s');
            if(!$val['position_name']) $val['position_name'] = $position_model->getPositionField(array('id' => $val['position_id']), 'position_name');
            $end = round(($val['base_pay']) / 1000, 2) . 'K';
            $start = round($val['base_pay'] / 1000, 2) .'K';
            $val['salary'] = $start.'-'.$end;
            $val['job_area'] = rtrim(substr($val['job_area'], strpos($val['job_area'], ',') + 1), ',');
        }
        unset($val);
        $this->apiReturn(V(1, '投递历史', $list['info']));
    }

    /**
     * @desc 收益明细
     */
    public function getHrAccountLog() {
        $where['user_id'] = UID;
        $where['change_type'] = array('in', [2,3,5,6]);
        $data = D('Admin/AccountLog')->getAccountLogByPage($where,'log_id,user_id,user_money,change_time,change_desc,change_type,order_sn', 'change_time desc');
        $info = $data['info'];
        $return = array();
        foreach($info as &$val){
            $val['change_month'] = substr($val['change_time'], 0, 7);
            $return[$val['change_month']]['data'] = $val['change_month'];
            $return[$val['change_month']]['account_list'][] = $val;
        }
        unset($val);
        $return_info = array();
        foreach($return as &$val){
            $return_info[] = $val;
        }
        unset($val);
        $this->apiReturn(V(1, '收益明细', $return_info));
    }

    /**
     * @desc 修改密码
     */
    public function modifyPassword(){
        $password = I('password', '', 'trim');
        $re_password = I('re_password', '', 'trim');
        if(!$password) $this->apiReturn(V(0, '请输入登录密码'));
        if($re_password != $password) $this->apiReturn(V(0, '两次密码输入不一致'));
        if(strlen($password) < 6 || strlen($password) > 18) $this->apiReturn(V(0, '密码长度6-18位'));
        $model = D('Admin/User');
        $save = $model->where(array('user_id' => UID))->save(array('password' => $password));
        if(false !== $save){
            $this->apiReturn(V(1, '保存成功！'));
        }
        else{
            $this->apiReturn(V(0, '保存错误，请稍后重试~'));
        }
    }

    /**
     * @desc HR悬赏列表
     * @return mixed
     */
    private function hrRecruitList(){
        $keywords = I('keywords', '', 'trim');
        $city_name = I('city_name', '', 'trim');
        $user_id = UID;
        //$tags = user_tags($user_id);
        $string_where = D('Admin/HrResume')->getHrTags($user_id);
        if(is_array($string_where)){
            $map = false;
        }
        else{
            $map = $string_where;
        }
        /*if(count($tags) > 0){
            $where1 = array();
            foreach($tags as &$val){
                $val['job_area'] = rtrim($val['job_area'], ',');
                if(false !== strpos($val['job_position'], '|')){
                    $pos = 'in ('.str_replace('|', ',', $val['job_position']).')';
                }
                else{
                    $pos = '= '.$val['job_position'];
                }
                $where1[] = ' (r.`job_area` like \''.$val['job_area'].'%\' and r.`position_id` '.$pos.') ';
            }
            unset($val);
            $map = implode(' or ', $where1);
        }*/
        if($map) $where['_string'] = $map;
        if(!$map) $where['_string'] = 'r.id < 1';//无符合条件悬赏展示所有的悬赏
        if($keywords) $where['r.position_name'] = array('like', '%'.$keywords.'%');
        if($city_name) $where['r.job_area'] = array('like', '%'.$city_name.'%');
        //$where['r.hr_user_id'] = array('neq', $user_id);
        $where['r.is_post'] = array('lt', 2);
        $where['r.is_shelf'] = 1;
        $where['r.status'] = 1;

        $recruit_model = D('Admin/Recruit');
        $position_model = D('Admin/Position');

        $list = $recruit_model->getHrRecruitList($where,'r.id, r.position_name, r.recruit_num, r.commission, r.add_time, r.position_id,c.company_name,r.job_area,r.experience,r.base_pay,r.merit_pay,r.is_fee');
        $experience = C('WORK_EXP');
        $recruit_id = $list['id'];
        foreach($list['info'] as &$val){
            $t_parent_id = $position_model->getPositionField(array('id' => $val['position_id']), 'parent_id');
            $position_name = $position_model->getPositionField(array('id' => $t_parent_id), 'position_name');
            $val['position_name'] = $position_name .'-'. $val['position_name'];
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $val['commission'] = fen_to_yuan($val['commission']);
            $val['experience'] = $experience[$val['experience']];
            $end = round(($val['merit_pay']) / 1000, 2) . 'K';
            $start = round($val['base_pay'] / 1000, 2) .'K';
            $val['salary'] = $start.'-'.$end;
            $val['resume_matching'] = 0;
            $val['job_area'] = rtrim(substr($val['job_area'], strpos($val['job_area'], ',') + 1), ',');
            if(in_array($val['id'], $recruit_id)) $val['resume_matching'] = 1;
        }
        unset($val);
        return $list['info'];
    }

    /**
     * @desc 求职者悬赏列表
     * @extra keywords string 悬赏检索关键字
     */
    private function normalRecruitList(){
        $user_id = UID;
        $recruit_model = D('Admin/Recruit');
        $position_model = D('Admin/Position');
        $resume_model = D('Admin/Resume');
        $user_where = array('user_id' => $user_id);
        $resume_info = $resume_model->getResumeInfo($user_where);
        //if(!$resume_info) $this->apiReturn(V(1, '悬赏列表！', array()));
        /*if(!check_is_auth($user_id)){
            $string = auth_string();
            $error = '请先通过实名认证！';
            if(false !== $string) $error = $string;
            $this->apiReturn(V(0, $error));
        }*/
        $keywords = I('keywords', '', 'trim');//首页悬赏筛选
        if($resume_info){
            $user_job_area = $resume_info['job_area'];
            $job_area = explode(',', $user_job_area);
            $_job_area_where = $job_area[0].','.$job_area[1];
            $education_model = D('Admin/Education');
            $education_degree = $education_model->getEducationField(array('education_name' => $resume_info['first_degree']), 'id');
            $where = array();
            $where['_string'] = 'r.`job_area` like \''.$_job_area_where.'%\' and r.`position_id` = '.$resume_info['position_id'];
            if($education_degree > 1) $where['_string'] .= ' and r.`degree` <= '.$education_degree;
        }
        else{
            $where['_string'] = 'r.id < 1';
        }
        //$where['r.hr_user_id'] = array('neq', $user_id);
        $where['r.is_post'] = array('lt', 2);
        $where['r.is_shelf'] = 1;
        $where['r.status'] = 1;
        if($keywords) $where['r.position_name'] = array('like', '%'.$keywords.'%');
        $list = $recruit_model->getHrRecruitList($where,'r.id, r.position_name, r.recruit_num, r.commission, r.add_time, r.position_id,c.company_name,r.job_area,r.experience,r.base_pay,r.merit_pay,r.is_fee');
        $experience = C('WORK_EXP');
        $recruit_id = $list['id'];
        foreach($list['info'] as &$val){
            $t_parent_id = $position_model->getPositionField(array('id' => $val['position_id']), 'parent_id');
            $position_name = $position_model->getPositionField(array('id' => $t_parent_id), 'position_name');
            $val['position_name'] = $position_name .'-'. $val['position_name'];
            $val['add_time'] = time_format($val['add_time'], 'Y-m-d');
            $val['commission'] = fen_to_yuan($val['commission']);
            $val['experience'] = $experience[$val['experience']];
            $end = round($val['merit_pay'] / 1000, 2) . 'K';
            $start = round($val['base_pay'] / 1000, 2) .'K';
            $val['salary'] = $start.'-'.$end;
            $val['resume_matching'] = 0;
            $val['job_area'] = rtrim(substr($val['job_area'], strpos($val['job_area'], ',') + 1), ',');
            if(in_array($val['id'], $recruit_id)) $val['resume_matching'] = 1;
        }
        unset($val);
        return $list['info'];
    }

    /**
     * @desc 每日任务列表
     * @return mixed
     */
    private function taskList(){
        $info = D('Admin/Task')->getTaskList();
        $task_log = D('Admin/TaskLog');
        $task_arr = array(1 => '每日限制', 0 => '永久限制', 2 => '每周限制', 3 => '每月限制');
        foreach($info as &$val){
            $val['reward'] = fen_to_yuan($val['reward']);
            $val['can'] = intval($val['can']);
            $t_n = $task_log->validTaskNumber($val['id'], UID, true);
            $type_number = $val['type_number'];
            $val['type_number'] = $task_arr[$val['type']].$val['type_number'].'份/已完成'.$t_n;
            $val['task_icon'] = C('IMG_SERVER').$val['task_icon'];
            if(0 == $t_n) $val['finish_status'] = 0;
            if($type_number > $t_n) $val['finish_status'] = 1;
            if($type_number == $t_n) $val['finish_status'] = 2;
        }
        unset($val);
        return $info;
    }

    /**
     * @desc HR排名信息 HR资料
     * @return array
     */
    private function rankList(){
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
        $resume_number = $hr_resume->getHrResumeCount(array('hr_user_id' => $user_id));
        if(!$hrResumeRanking) $hrResumeRanking = '999+';
        $return_array = array('user_ranking' => $userRanking, 'resume_ranking' => $hrResumeRanking,'head_pic'=>$head_pic,'nickname'=>$nickname, 'resume_number' => $resume_number);
        return $return_array;
    }

    /**
     * @desc 获取导航栏目列表
     * @param 1、HR端 0、求职者端
     */
    private function navList(){
        $nav_model = D('Admin/Nav');
        $position = I('position_id', 1, 'intval');
        $nav_where = array('position' => $position);
        $field = 'link_type,img,title,id,sort';
        $list = $nav_model->navList($nav_where, $field, 'sort asc');
        foreach($list as &$val){
            $val['img'] = C('IMG_SERVER').$val['img'];
        }
        unset($val);
        return $list;
    }

    /**
     * @desc 获取公告列表
     */
    private function noticeList($user_type){
        $model = D('Admin/Article');
        $cat_id = $user_type == 1 ? 6 : 10;
        $notice_where = array('article_cat_id' => $cat_id, 'display' => 1);
        $field = 'article_id,title';
        $list = $model->getArticleList($notice_where, $field, 'sort asc');
        return $list['articlelist'];
    }

    /**
     * @desc 轮播图列表
     */
    private function bannerList($user_type){
        $model = D('Admin/Ad');
        $position = I('ad_position', 1, 'intval');
        //首页分为HR和求职者
        if(1 == $position) $ad_position = 1 == $user_type ? $position : 5;
        //合伙人端仅HR
        if(2 == $position) $ad_position = $position;
        if(0 == $user_type && 2 == $position) return array();
        $where = array('ad.position_id' => $ad_position, 'display' => 1);
        $field = 'title,content';
        $list = $model->getAdlist($where, $field);
        foreach($list['info'] as &$val){
            $val['content'] = C('IMG_SERVER').$val['content'];
        }
        return $list['info'];
    }

    /**
     * @desc 公告下方悬赏发布/每日任务配图
     * @return array
     */
    private function recruitTaskBanner(){
        $model = D('Admin/Ad');
        //发布悬赏配图
        $recruit_position = array('position_id' => 3, 'display' => 1);
        $recruit_info = $model->getAdInfo($recruit_position, 'title,content');
        $recruit_info['content'] = C('IMG_SERVER').$recruit_info['content'];
        //每日任务配图
        $task_position = array('position_id' => 4, 'display' => 1);
        $task_info = $model->getAdInfo($task_position, 'title,content');
        $task_info['content'] = C('IMG_SERVER').$task_info['content'];
        $return_info = array(
            'recruit' => $recruit_info,
            'task' => $task_info
        );
        return $return_info;
    }
}