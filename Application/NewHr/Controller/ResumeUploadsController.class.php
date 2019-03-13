<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class ResumeUploadsController extends HrCommonController {

    /**
     * @desc HR简历上传记录
     */
    public function listResumeUploads(){
        $user_id = HR_ID;
        $keywords = I('keyword', '', 'trim');
        $where = array('user_id' => $user_id);
        if($keywords) $where['original_name'] = array('like', '%'.$keywords.'%');
        $uploads_model = D('Admin/ResumeUploads');
        $list = $uploads_model->getResumeUploadsList($where);
        $this->keyword = $keywords;
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
}
