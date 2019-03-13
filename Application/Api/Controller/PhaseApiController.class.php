<?php
/**
 * @desc 闪荐三期修改接口功能
 */
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;
class PhaseApiController extends ApiUserCommonController{

    /**
     * @desc 任务是否开启
     */
    public function taskPhaseOn(){
        $on = C('IS_TASK');
        $return = array('on' => $on);
        $this->apiReturn(V(1, '任务开启状态', $return));
    }

    /**
     * @desc 三期任务开启首页内容
     */
    public function taskPhaseHomeData(){
        $on = C('IS_TASK');
        if(!$on) $this->apiReturn(V(0, '任务尚未开启'));
        $profit_rate = C('TASK_RATE');
        $reward = C('TASK_REWARD');
        //简历上传|成功招聘|成功推荐
        $profit_rate = explode('|', $profit_rate);
        $user_id = UID;
        $month_time = time_list(3);
        //Q&A列表
        $question_list = $this->getQuestionList();
        //任务banner
        $banner_list = $this->getPhaseBanner();
        //任务滚动公告
        $notice_list = $this->getNoticeList();
        //预计收益[简历上传]
        $task_id = 3;
        $task_where = array('finish_time' => array('between', array($month_time['start'], $month_time['end'])), 'task_id' => $task_id);
        $task_model = D('Admin/TaskLog');
        $field = 'sum(task_id) as sum_count, sum(if(user_id = '.$user_id.', task_id, 0)) as user_sum';
        $task_log_data = $task_model->getTaskLogCount($task_where, $field);
        //后台奖金单位为元，比例不用除100
        $estimated_revenue_resume = floor(($task_log_data['user_sum'] / $task_log_data['sum_count']) * ($profit_rate[0] * $reward)) / 100;
        $recruit_field = 'sum(1) as total_count, sum(if(hr_user_id = '.$user_id.', 1, 0)) as user_count';
        $recruit_where = array('status' => 1, 'is_shelf' => 1, 'add_time' => array('between', array($month_time['start'], $month_time['end'])));
        $recruit_info = D('Admin/Recruit')->getRecruitInfo($recruit_where, $recruit_field);
        $recruit_user_num = $recruit_info['user_count'];
        $recruit_num = $recruit_info['total_count'];

        $recruit_resume_field = 'sum(1) as total_count, sum(if(hr_user_id = '.$user_id.', 1, 0)) as user_count';
        $recruit_resume_where = array('add_time' => array('between', array($month_time['start'], $month_time['end'])));
        $recruit_resume_info = D('Admin/RecruitResume')->getRecruitResumeInfo($recruit_resume_where, $recruit_resume_field);
        $recommended_user_num = $recruit_resume_info['user_count'];
        $recommended_num = $recruit_resume_info['total_count'];
        /*$recruit_recommend_model = D('Admin/RecruitRecommend');
        $recruit_recommend_where = array('finish_time' => array('between', array($month_time['start'], $month_time['end'])), 'operate_type' => array('gt', 0));
        $recruit_recommend_field = 'operate_type,user_id';
        $recruit_recommend_data = $recruit_recommend_model->getRecruitRecommendList($recruit_recommend_where, $recruit_recommend_field);
        $recommended_num = 0;
        $recommended_user_num = 0;
        $recruit_num = 0;
        $recruit_user_num = 0;
        foreach($recruit_recommend_data as &$val){
            if(1 == $val['operate_type']){
                $recruit_num++;
                if($val['user_id'] == $user_id) $recruit_user_num++;
            }
            if(2 == $val['operate_type']){
                $recommended_num ++;
                if($val['user_id'] == $user_id) $recommended_user_num++;
            }
        }
        unset($val);*/
        //预计收益[成功推荐]
        $estimated_revenue_recommended = floor(($recommended_user_num / $recommended_num) * ($profit_rate[2] * $reward)) / 100;
        //预计收益[成功招聘]
        $estimated_revenue_recruit = floor(($recruit_user_num / $recruit_num) * ($profit_rate[1] * $reward)) / 100;

        //预计总收益
        $estimated_total = $estimated_revenue_resume + $estimated_revenue_recommended + $estimated_revenue_recruit;
        $return_arr = array(
            'question' => $question_list,
            'banner' => $banner_list,
            'notice' => $notice_list,
            'estimated_task' => $estimated_revenue_resume,
            'estimated_recommended' => $estimated_revenue_recommended,
            'estimated_recruit' => $estimated_revenue_recruit,
            'estimated_total' => $estimated_total
            );
        $this->apiReturn(V(1, '任务列表', $return_arr));
    }

    /**
     * @desc 获取公告详情
     */
    public function getArticleInfo(){
        $article_id = I('id', 0, 'intval');
        $this->apiReturn(V(1, '', C('IMG_SERVER').'/index.php/Api/PublicApi/noticeInfo/id/'.$article_id));
    }

    /**
     * @desc 任务banner
     * @return mixed
     */
    private function getPhaseBanner(){
        $model = D('Admin/Ad');
        $ad_position = 6;
        $where = array('ad.position_id' => $ad_position, 'display' => 1);
        $field = 'title,content';
        $list = $model->getAdlist($where, $field);
        foreach($list['info'] as &$val){
            $val['content'] = C('IMG_SERVER').$val['content'];
        }
        return $list['info'];
    }

    /**
     * @desc 任务滚动公告
     * @return mixed
     */
    private function getNoticeList(){
        $model = D('Admin/Article');
        $cat_id = 12;
        $notice_where = array('article_cat_id' => $cat_id, 'display' => 1);
        $field = 'article_id,title';
        $list = $model->getArticleList($notice_where, $field, 'sort asc');
        return $list['articlelist'];
    }

    /**
     * Q&A
     * @return mixed
     */
    private function getQuestionList(){
        $model = D('Admin/QuestionAnswer');
        $where = array('display' => 1);
        $list = $model->getQuestionAnswerList($where);
        return $list;
    }
}