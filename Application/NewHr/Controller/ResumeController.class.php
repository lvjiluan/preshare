<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
use Think\Verify;

class ResumeController extends HrCommonController {

    /**
     * @desc 获取hr简历人才库列表
     */
    public function listHrResume(){
        $resume_name = I('keywords', '', 'trim');
        $model = D('Admin/HrResume');
        $where = array('_string' => 'h.hr_user_id = '.HR_ID.' or r.user_id = '.HR_ID);
        //多功能检索处理
        $true_name = I('true_name', '', 'trim');
        $mobile = I('mobile', '', 'trim');
        if($true_name) $resume_name = $true_name;
        if($mobile) $resume_name = $mobile;
        $email = I('email', '', 'trim');
        if($email) $where['r.email'] = array('like', '%'.$email.'%');
        $sex = I('sex', 0, 'intval');
        if(in_array($sex, array(1, 2))) $where['r.sex'] = $sex;
        $age_min = I('age_min', '', 'trim');
        $age_max = I('age_max', '', 'trim');
        if($age_min && $age_max){
            $age_min = strtotime($age_min);
            $age_max = strtotime($age_max);
            $where['r.age'] = array('between', array($age_min, $age_max));
        }
        else if($age_min){
            $age_min = strtotime($age_min);
            $where['r.age'] = array('egt', $age_min);
        }
        else if($age_max){
            $age_max = strtotime($age_max);
            $where['r.age'] = array('elt', $age_max);
        }
        $post_nature = I('post_nature', '', 'trim');
        if($post_nature) $where['r.post_nature'] = $post_nature;
        $province = I('province', '', 'trim');
        $job_area = I('job_area', '', 'trim');
        $county = I('county', '', 'trim');
        if($county) $where['r.job_intension'] = $province.','.$job_area.','.$county;
        $job_area = I('job_area', '', 'trim');
        if($job_area) $where['r.job_area'] = array('like', '%'.$job_area.'%');
        $position_id = I('position_id', 0, 'intval');
        if($position_id) $where['position_id'] = $position_id;
        if($resume_name) $where['r.true_name|r.mobile'] = array('like', '%'.$resume_name.'%');
        $list = $model->getHrResumeListWeb($where, 'h.id as hr_resume_id,h.hr_user_id,r.*, IFNULL(h.hr_user_id,'.HR_ID.') as hr_user_id_null');
        foreach($list['info'] as &$val){
            $val['hr_user_id'] = $val['hr_user_id_null'];
            if($val['hr_user_id'] == $val['user_id']) $val['is_edit'] = 1;
            if(!$val['job_intension']) $val['job_intension'] = D('Admin/Position')->getPositionField(array('id' => $val['position_id']), 'position_name');
        }
        unset($val);
        $this->keywords = $resume_name;
        $this->page = $list['page'];
        $this->info = $list['info'];
        $this->display();
    }

    /**
     * @desc 编辑简历
     */
    public function editResume(){
        $model = D('Admin/Resume');
        $userModel = D('Admin/User');
        $resume_id = I('resume_id', 0, 'intval');
        $data = I('post.');
        $resume_where = array('id' => $resume_id);
        $info = $model->getResumeInfo($resume_where);
        if(IS_POST){
            $data['user_id'] = HR_ID;
            $data['age'] = strtotime($data['birthday_year'].'-'.$data['birthday_month']);
            $data['job_area'] = $data['province'].','.$data['job_area'].','.$data['county'];
            $data['job_area'] = rtrim($data['job_area'], ',');
            $data['address'] = $data['t_province'].','.$data['t_job_area'];
            $data['address'] = rtrim($data['address'], ',');
            $op_arr = array('career_label');
            foreach($op_arr as &$op){
                if($data[$op]) $data[$op] = implode(',', $data[$op]);
            }
            if($resume_id){
                $data['id'] = $resume_id;
                $create = $model->create($data, 1);
                if(false !== $create){
                    $user_info = $userModel->getUserInfo(array('mobile' => $data['mobile']));
                    if($user_info) $this->ajaxReturn(V(0, '该手机号已在C端注册，请前往小程序认证获得！'));
                    $res = $model->where(array('id' => $resume_id))->save($data);
                    if(false !== $res){
                        header('Content-Type:application/json; charset=utf-8');
                        $str = '保存成功！';
                        if($info['is_audit'] != 1) $str = '保存成功，等待后台审核！';
                        echo json_encode(V(1, $str, $resume_id));
                        //审核通过，更新HR tags标签
                        if($info['is_audit'] == 1){
                            fastcgi_finish_request();
                            set_time_limit(0);
                            refreshUserTags(false, $resume_id, array('job_position' => $data['position_id'], 'job_area' => $data['job_area']));
                        }
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
                else{
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
            else{
                M()->startTrans();
                $data['is_audit'] = 0;
                $create = $model->create($data, 2);
                if(false !== $create){
                    $str = '保存成功，等待后台审核！';
                    $user_info = $userModel->getUserInfo(array('mobile' => $data['mobile']));
                    if($user_info) $this->ajaxReturn(V(0, '该手机号已在C端注册，请前往小程序认证获得！'));
                    //if(!$this->firstValid()) $str = '简历保存成功，请前往小程序推荐！';
                    $res = $model->add($data);
                    if(false !== $res){
                        sendMessageRequest($data['mobile'], C('SHAN_RESUME_NOTICE'));
                        M()->commit();
                        $this->ajaxReturn(V(1, $str, $res));
                    }
                }
                M()->rollback();
                $this->ajaxReturn(V(0, $model->getError()));
            }
        }
        else{
            $work_nature = C('WORK_NATURE');
            $language = C('SHAN_LANGUAGE');
            $arr_values = array_values($work_nature);
            $lang_values = array_values($language);
            $nature_arr = array();
            $language_arr = array();
            $tags_where = array('tags_type' => array('in', array(1,2,5)));
            $tagsModel = D('Admin/Tags');
            $tags_info = $tagsModel->getTagsList($tags_where, 'id,tags_name,tags_type');
            $tags_career = array();
            $tags_recommend = array();
            foreach($tags_info as &$val){
                if(1 == $val['tags_type']) $tags_career[] = array('id' => $val['id'], 'name' => $val['tags_name']);
                if(5 == $val['tags_type']) $tags_recommend[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            }
            unset($val);
            foreach($arr_values as &$val){
                $nature_arr[] = array('id' => $val, 'name' => $val);
            }
            unset($val);
            foreach($lang_values as &$val){
                $language_arr[] = array('id' => $val, 'name' => $val);
            }
            unset($val);
            $area = D('Admin/Region')->getRegionList(array('level' => 2), 'id,name');
            $edu_list = D('Admin/Education')->getEducationList(array('id' => array('gt', 0)));
            if(!$info['age']){
                $info['birthday_year'] = date('Y') - 10;
                $info['birthday_month'] = 1;
            }
            else{
                $info['birthday_year'] = date('Y', $info['age']);
                $info['birthday_month'] = date('m', $info['age']);
            }
            if($info['address']){
                $address = explode(' ' ,$info['address']);
                if(count($address) > 0){
                    $info['address_p'] = $address[0];
                    $add_help = explode(',', $address[0]);
                    $info['address1'] = $add_help[0];
                    $info['address2'] = $add_help[1];
                    $info['address3'] = $add_help[2];
                    unset($address[0]);
                    $info['address'] = str_replace($info['address_p'].' ', '', $info['address']);
                }
            }
            $industry = D('Admin/Industry')->getIndustryList();
            if($info['language_ability']){
                $language = explode(',', $info['language_ability']);
                foreach($language_arr as &$lan_val){
                    $lan_val['sel'] = 0;
                    if(in_array($lan_val['name'], $language)) $lan_val['sel'] = 1;
                }
                unset($lan_val);
            }
            $info['position_parent'] = 0;
            if(!$info['position_id']) $info['position_id'] = 0;
            if($info['position_id']) $info['position_parent'] = D('Admin/Position')->getPositionField(array('id' => $info['position_id']), 'parent_id');
            if($info['job_area']){
                $job_area = explode(',', $info['job_area']);
                $info['job_area1'] = $job_area[0];
                $info['job_area2'] = $job_area[1];
                $info['job_area3'] = $job_area[2];
            }

            //教育经历
            $resume_edu_where = array('resume_id' => $resume_id);
            $resume_edu_model = D('Admin/ResumeEdu');
            $resume_edu_list = $resume_edu_model->getResumeEduList($resume_edu_where);
            foreach($resume_edu_list as &$val){
                $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
                if($val['endtime']){
                    $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
                }
                else{
                    $val['endtime'] = '至今';
                }
            }
            unset($val);
            //工作经历
            $resume_work_where = array('resume_id' => $resume_id);
            $resume_work_model = D('Admin/ResumeWork');
            $resume_work_list = $resume_work_model->getResumeWorkList($resume_work_where);
            foreach($resume_work_list as &$val){
                $val['starttime'] = time_format($val['starttime'], 'Y-m-d');
                if($val['endtime']){
                    $val['endtime'] = time_format($val['endtime'], 'Y-m-d');
                }
                else{
                    $val['endtime'] = '至今';
                }
            }

            $is_marry = returnArrData(C('SHAN_RESUME_MARRY'));
            $this->resume_edu_list = $resume_edu_list;
            $this->resume_work_list = $resume_work_list;
            $this->marry = $is_marry;
            $this->industry = $industry;
            $this->info = $info;
            $this->lang = $language_arr;
            $this->edu_list = $edu_list;
            $this->recommend = $tags_recommend;
            $this->area = $area;
            $this->career = $tags_career;
            $this->nature = $nature_arr;
            $this->display();
        }
    }

    /**
     * 简历详情
     */
    public function seeResumeDetail(){
        $resume_id = I('id', 0, 'intval');
        $resumeModel = D('Admin/Resume');
        $resumeWorkModel = D('Admin/ResumeWork');
        $resumeEduModel = D('Admin/ResumeEdu');
        $resumeEvaluationModel = D('Admin/ResumeEvaluation');
        $resume_where = array('id' => $resume_id);
        $resumeDetail = $resumeModel->getResumeInfo($resume_where);
        $resumeDetail['age'] = time_to_age($resumeDetail['age']);
        if(!$resumeDetail['head_pic']) $resumeDetail['head_pic'] = DEFAULT_IMG;
        $where = array('resume_id' => $resume_id);
        $resumeWorkList = $resumeWorkModel->getResumeWorkList($where);
        $resumeEduList = $resumeEduModel->getResumeEduList($where);
        foreach($resumeWorkList as &$wval){
            $wval['starttime'] = time_format($wval['starttime'], 'Y-m-d');
            $wval['endtime'] = $wval['endtime'] ? time_format($wval['endtime'], 'Y-m-d') : '至今';
        }
        unset($wval);
        foreach($resumeEduList as &$eval){
            $eval['starttime'] = time_format($eval['starttime'], 'Y-m-d');
            $eval['endtime'] = $eval['endtime'] ? time_format($eval['endtime'], 'Y-m-d') : '至今';
        }
        unset($eval);
        $resumeEvaluation = $resumeEvaluationModel->getResumeEvaluationAvg($where);
        $sum = array_sum(array_values($resumeEvaluation));
        $avg = round($sum/(count($resumeEvaluation)), 2);
        $return = array('detail' => $resumeDetail, 'resume_work' => $resumeWorkList, 'resume_edu' => $resumeEduList, 'resume_evaluation' => $resumeEvaluation, 'evaluation_avg' => $avg);
        $this->info = $return;
        $this->display();
    }

    /**
     * @desc 检索条件
     */
    public function researchResume(){
        $work_nature = C('WORK_NATURE');
        $arr_values = array_values($work_nature);
        $nature_arr = array();
        $tags_where = array('tags_type' => array('in', array(1,2,5)));
        $tagsModel = D('Admin/Tags');
        $tags_info = $tagsModel->getTagsList($tags_where, 'id,tags_name,tags_type');
        $tags_intension = array();
        $tags_career = array();
        $tags_recommend = array();
        foreach($tags_info as &$val){
            if(1 == $val['tags_type']) $tags_career[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(2 == $val['tags_type']) $tags_intension[] = array('id' => $val['id'], 'name' => $val['tags_name']);
            if(5 == $val['tags_type']) $tags_recommend[] = array('id' => $val['id'], 'name' => $val['tags_name']);
        }
        unset($val);
        foreach($arr_values as &$val){
            $nature_arr[] = array('id' => $val, 'name' => $val);
        }
        unset($val);

        $industry = D('Admin/Industry')->getIndustryList();
        $this->recommend = $tags_recommend;
        $this->industry = $industry;
        $this->intension = $tags_intension;
        $this->career = $tags_career;
        $this->nature = $nature_arr;
        $this->display();
    }

    /**
     * @desc 意见反馈
     */
    public function editFeedBack(){
        $data = I('post.', '');
        $data['user_id'] = HR_ID;
        if(IS_POST){
            if(!(isMobile($data['mobile']) || is_email($data['mobile']))) $this->ajaxReturn(V(0, '请填写正确的手机号或者邮箱！'));
            $model = D('Admin/FeedBack');
            $create = $model->create($data, 1);
            if(false !== $create){
                $res = $model->add($data);
                if($res){
                    $this->ajaxReturn(V(1, '反馈成功！'));
                }
                else{
                    $this->ajaxReturn(V(0, $model->getError()));
                }
            }
            else{
                $this->ajaxReturn(V(0, $model->getError()));
            }
        }
        $this->display();
    }

    public function upload_resume(){
        if(IS_POST){
            $files = I('post.upload_resume', '');
            if(!$files || count($files) == 0) $this->ajaxReturn(V(0, '请选择上传文件！'));
            $model = D('Admin/ResumeUploads');
            //TODO windows环境下替换config_url 替换/为\
            $config_url = C('RESUME_UPLOADS');
            $secret_key = C('YOU_YUN_SECRET');
            $file_explode = implode(',', $files);
            $file_list = $model->getResumeUploadsList(array('upload_url' => array('in', $file_explode)), false, '', false);
            $file_content = $this->operate_file_content($file_list);
            $file_list = $this->operate_file_list($file_list);
            $false_string = false;
            foreach($files as &$val){
                $original_url = $val;
                //$val = $config_url.str_replace('/', '\\', $val);
                if($file_content[$original_url]){
                    $analysis_result = $file_content[$original_url];
                }
                else{
                    $val = $config_url.$val;
                    $analysis_result = resume_analysis($val, $secret_key);
                    M('ResumeUploads')->where(array('upload_url' => $original_url))->save(array('content' => $analysis_result));
                }
                $analysis_result = json_decode($analysis_result, true);
                if($analysis_result['error_code'] != 0) $this->ajaxReturn(V(0, $analysis_result['error_msg']));
                $need_analysis = $analysis_result['data']['cv_parse'];
                $data = $this->analysis_uploaded_resume($need_analysis);
                if($data['status'] == 0 && $file_list[$original_url]) $false_string .= $file_list[$original_url].$data['info'].',';
            }
            unset($val);
            if(false !== $false_string){
                $false_string = rtrim($false_string, ',');
                $this->ajaxReturn(V(0, $false_string));
            }
            else{
                $this->ajaxReturn(V(1, '保存成功'));
            }
        }
        $this->display();
    }

    /**
     * @desc 根据简历解析数据返回数据库所需字段并处理
     * @param $data array 简历解析数据
     * @return array
     */
    private function analysis_uploaded_resume($data){
        $region_model = D('Admin/Region');
        $basic_info = $data['basic_info'];//基本信息
        $contacts_info = $data['contact'];//联系方式
        $education_list = $data['educations'];//教育经历
        $work_list = $data['occupations'];//工作经历
        $job_objective = $data['job_objective'];//期望工作
        $introduce = $data['self_evaluate'];//自我介绍
        $language = $data['languages'];
        $marry_help = array('已婚' => 1, '未婚' => 2);
        $gender_help = array('男' => 1, '女' => 2);
        $user_id = HR_ID;
        if(!isMobile($contacts_info['mobile'])) return V(0, '未解析到合法手机号');
        $marry_status = $marry_help[$basic_info['marriage_status']] ? $marry_help[$basic_info['marriage_status']] : 0;
        $sex = $gender_help[$basic_info['gender']] ? $gender_help[$basic_info['gender']] : 0;
        $basic_info['hukou']['province'] = $region_model->getRegionName(array('parent_id' => 1, 'name' => array('like', $basic_info['hukou']['province'].'%')), 'name');
        $basic_info['hukou']['city'] = $region_model->getRegionName(array('name' => array('like',$basic_info['hukou']['city'].'%')), 'name');
        $job_objective_location = $region_model->getRegionInfo(array('parent_id' => 1, 'name' => array('like', $job_objective['expect_locations'][0]['province'].'%')), 'name,id');
        $job_objective['expect_locations'][0]['province'] = $job_objective_location['name'];
        $job_objective_city = $region_model->getRegionInfo(array('parent_id' => $job_objective_location['id'], 'name' => array('like', $job_objective['expect_locations'][0]['city'].'%')), 'name,id');
        $job_objective['expect_locations'][0]['city'] = $job_objective_city['name'];
        $job_area = $job_objective['expect_locations'][0]['province'].','.$job_objective['expect_locations'][0]['city'];
        if($job_objective['expect_locations'][0]['district']){
            $job_objective['expect_locations'][0]['district'] = $region_model->getRegionName(array('parent_id' => $job_objective_city['id'], 'name' => array('like', $job_objective['expect_locations'][0]['city'].'%')), 'name');
            $job_area .= ','.$job_objective['expect_locations'][0]['district'];
        }
        $job_area = rtrim($job_area, ',');
        //简历基本信息
        $resume_info = array(
            'true_name' => $basic_info['name'],
            'mobile' => $contacts_info['mobile'],
            'email' => $contacts_info['email'],
            'head_pic' => $data['img_info']['img_url'],
            'expect_salary' => $job_objective['expect_salary'],
            'is_marry' => $marry_status,
            'sex' => $sex,
            'industry_id' => 0,//行业
            'position_id' => 0,//职业  行业|职业选项自行选择,
            'age' => strtotime($basic_info['birthday']),
            'post_nature' => $job_objective['expect_worktype'],
            'first_degree' => $basic_info['highest_degree'],
            'language_ability' => implode(',', $language['language']),
            'address' => rtrim($basic_info['hukou']['province'].','.$basic_info['hukou']['city'], ','),
            'job_area' => $job_area,
            'introduce' => $introduce,
            'user_id' => $user_id,
            'is_audit' => 0,
            'is_uploaded' => 1
        );
        $resume_model = D('Admin/Resume');
        $resume_work_model = D('Admin/ResumeWork');
        $resume_edu_model = D('Admin/ResumeEdu');
        $create = $resume_model->create($resume_info, 1);
        if(false !== $create){
            $res = $resume_model->add($resume_info);
            if($res){
                $resume_work = array();
                foreach($work_list as &$val){
                    $val['start_time'] = str_replace('/', '-', $val['start_time']);
                    $temp_arr = array(
                        'company_name' => $val['company'],
                        'position' => $val['title'],
                        'starttime' => strtotime($val['start_time']),
                        'describe' => $val['desc'],
                        'resume_id' => $res
                    );
                    if($val['not_ended']) $temp_arr['endtime'] = 0;
                    if(!$val['not_ended']){
                        $val['end_time'] = str_replace('/', '-', $val['end_time']);
                        $temp_arr['endtime'] = strtotime($val['end_time']);
                    }
                    if(!$temp_arr['starttime']) $temp_arr['starttime'] = time();
                    $resume_work[] = $temp_arr;
                }
                unset($val);

                $resume_edu = array();
                foreach($education_list as &$val){
                    $val['start_time'] = str_replace('/', '-', $val['start_time']);
                    if(false === strpos($val['start_time'], '-')) $val['start_time'] .= '-01';
                    $temp_arr = array(
                        'school_name' => $val['school'],
                        'starttime' => strtotime($val['start_time']),
                        'degree' => $val['degree'],
                        'major' => $val['major'],
                        'resume_id' => $res
                    );
                    if($val['not_ended']) $temp_arr['endtime'] = 0;
                    if(!$val['not_ended']){
                        $val['end_time'] = str_replace('/', '-', $val['end_time']);
                        $temp_arr['endtime'] = strtotime($val['end_time']);
                    }
                    $resume_edu[] = $temp_arr;
                }
                unset($val);

                $resume_edu_model->addAll($resume_edu);
                $resume_work_model->addAll($resume_work);

                sendMessageRequest($contacts_info['mobile'], C('SHAN_RESUME_NOTICE'));
                return V(1, '保存成功');
            }
        }
        return V(0, $resume_model->getError());
    }

    public function delResumeUploads(){
        $user_id = HR_ID;
        $file_name = I('file_name', '', 'trim');
        $where = array('user_id' => $user_id, 'original_name' => $file_name);
        $model = D('Admin/ResumeUploads');
        $find = $model->where($where)->find();
        if($find['content']) $this->ajaxReturn(V(0, '解析过的简历不可删除'));
        $del = $model->where($where)->delete();
        if(false !== $del){
            $file_link = './' . __ROOT__ . $find['upload_url'];
            @unlink($file_link);
            $this->ajaxReturn(V(1, '删除成功！'));
        }
        else{
            $this->ajaxReturn(V(0, '删除出现错误！'));
        }
    }

    private function operate_file_list($file_list){
        $return_arr = array();
        foreach($file_list as &$val) $return_arr[$val['upload_url']] = $val['original_name']; unset($val);
        return $return_arr;
    }

    private function operate_file_content($file_list){
        $return_arr = array();
        foreach($file_list as &$val) $return_arr[$val['upload_url']] = $val['content']; unset($val);
        return $return_arr;
    }
}