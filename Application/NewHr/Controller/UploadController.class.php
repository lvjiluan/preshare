<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class UploadController extends HrCommonController {

    public function uploadFile(){
        ini_set('max_execution_time', '0');
        $config = array(
            'rootPath' => '.'.C('UPLOAD_URL').'Resume/',
            'savePath' => '/',
            'maxSize' => 10 * 1024 * 1024,
            'exts' => '',
        );
        $dir = './'.C('UPLOAD_URL').'Resume/';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->ajaxReturn(V(0, '创建目录失败'));
            }
        }
        $config['saveName'] = randCode(11, 99).randNumber(4);
        $config['autoSub'] = true;//TODO windows环境下改为false
        $user_id = HR_ID;
        $Upload = new \Think\Upload($config);
        $file_arr = $_FILES['resume_file'];
        $file_name = $file_arr['name'];
        if(preg_match("/[\':;*?~`!@#$%^&+=)(<{}]|\]|\[|\/|\\\|\"|\|/", $file_name)){
            $this->ajaxReturn(V(0, '文件名中有特殊字符'));
        }
        $model = D('Admin/ResumeUploads');
        $exists = $model->checkResumeFileExists(array('user_id' => $user_id, 'original_name' => $file_name));
        if($exists) $this->ajaxReturn(V(0, '此文件您已经上传过！'));
        $info = $Upload->upload();
        if ($info === false) {
            $this->ajaxReturn(V(0, $Upload->getError()));
        } else {
            // 返回成功信息
            foreach($info as $file){
                $path = '.'.C('UPLOAD_URL').'Resume'.$file['savepath'].$file['savename'];
                $local_path = trim($path, '.');
                $data['name'] = $local_path;
            }
            add_uploaded_file($local_path, $file_name, $user_id);
            $this->ajaxReturn(V(1, 'success', $data));
        }
    }
}
