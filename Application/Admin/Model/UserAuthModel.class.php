<?php
/**
 * 用户验证信息模型类
 */
namespace Admin\Model;

use Think\Model;

class UserAuthModel extends Model {
    protected $insertFields = array('true_name', 'cert_type', 'idcard_number', 'idcard_down', 'hand_pic', 'add_time', 'idcard_up', 'business_license', 'user_id', 'audit_status', 'audit_desc', 'company_name', 'license_number');
    protected $updateFields = array('true_name', 'cert_type', 'idcard_number', 'idcard_down', 'hand_pic', 'add_time', 'idcard_up', 'business_license', 'user_id', 'audit_status', 'audit_desc', 'company_name', 'license_number');
    protected $_validate = array(
        array('company_name', 'companyEmpty', '请填写公司名称', 1, 'callback', 3),
        array('company_name', '0,50', '公司名称长度不能超过50', 2, 'length', 3),
        array('true_name', 'require', '真实姓名不能为空！', 1, 'regex', 3),
        array('true_name', 'checkTypeLength', '真实姓名不能超过18字！', 2, 'callback', 3),
        array('cert_type', 'require', '证件类型不能为空！', 1, 'regex', 3),
        array('idcard_number', 'require', '证件号码不能为空！', 1, 'regex', 3),
        array('license_number', 'license_number', '组织代码不能为空！', 1, 'callback', 3),
        array('idcard_up', 'require', '证件照正面不能为空！', 1, 'regex', 3),
        //array('idcard_down', 'require', '证件照反面不能为空！', 1, 'regex', 3),
        array('hand_pic', 'require', '手持证件照不能为空!', 1, 'regex', 3),
        array('business_license', 'business_license', '营业执照不能为空!', 1, 'callback', 3),
    );

    /**
     * 验证过滤词长度
     */
    protected function checkTypeLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 18) {
            return false;
        }
        return true;
    }

    protected function business_license($info){
        if(USER_TYPE == 1 && !$info['business_license']) return false;
        return true;
    }

    protected function license_number($info){
        if(USER_TYPE == 1 && !$info['license_number']) return false;
        return true;
    }

    protected function companyEmpty($info){
        if(USER_TYPE == 1 && !$info['company_name']) return false;
        return true;
    }

    /**
     * @desc 获取身份验证信息
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getAuthInfo($where, $field = false){
        $info = $this->where($where)->field($field)->find();
        return $info;
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $uid = UID;
        $data['add_time'] = NOW_TIME;
        $data['audit_status'] = 0;
        $where = array('user_id' => $uid);
        $data['user_id'] = $uid;
        $res = $this->where($where)->find();
        if($data['cert_type'] == 1){
            if(!isCard($data['idcard_number'])){
                $this->error = '不是合法的身份证号！';
                return false;
            }
        }
        if($res) $this->where($where)->delete();
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
        $data['audit_status'] = 0;//上传凭证修改  修改为待审核
        if($data['cert_type'] == 1){
            if(!isCard($data['idcard_number'])){
                $this->error = '不是合法的身份证号！';
                return false;
            }
        }
    }

}