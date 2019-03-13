<?php
/**
 * Created by PhpStorm.
 * User: jipingzhao liuniukeji.com
 * Date: 6/30/17
 * Time: 2:53 PM
 */
namespace Api\Model;
use Think\Model;
class UserAddressModel extends Model
{
    protected $insertFields = array('consignee','address','province','city','district','mobile','zipcode','is_default');
    protected $updateFields = array('address_id','consignee','address','province','city','district','mobile','zipcode','is_default');
    protected $selectFields = array('address_id','consignee','address','province','city','district','mobile','zipcode','is_default');

    protected $_validate = array(
        array('consignee', '1,25', '收货人姓名不正确, 请输入1到25位字符', self::MUST_VALIDATE, 'length', 1),

        array('mobile', 'isMobile', '手机号不是11位合法的手机号', self::MUST_VALIDATE, 'function', 3),

        array('province', '1,25', '您选择的省份不正确', self::MUST_VALIDATE, 'length', 3),
        array('city', '1,25', '您选择的城市不正确', self::MUST_VALIDATE, 'length', 3),
        array('district', '1,25', '您选择的区域不正确', self::MUST_VALIDATE, 'length', 3),

        array('address', '5,125', '为了您到正常收到商品, 请正确填写详细地址', self::MUST_VALIDATE, 'length', 3),

//        array('zipcode', 'checkZipcode', '邮政编码为6位数字', self::VALUE_VALIDATE, 'callback', 3),

//        array('is_default', array(0,1), '默认收货地址不合法', self::VALUE_VALIDATE, 'in', 3),
    );

    /**
     * 验证邮编
     */
    protected function checkZipcode($data) {
        $length = strlen($data);
        $type = is_numeric($data);
        if ($length != 6 || $type == false) {
            return false;
        }
        return true;
    }

    public function userAddressList(){
        $where['user_id'] = UID;
        $list = $this->where($where)->field($this->selectFields)->order('is_default desc')->limit(10)->select();
        if (!empty($list)) {
            // 如果没有默认地址,把列表中的第一个地址设为默认地址
            $count = $this->where($where)->where(array('is_default' => 1))->count();
            if ($count <= 0) {
                $list[0]['is_default'] = 1;
            }
        }
        return $list;
    }

    public function getUserAddressInfo($address_id){
        $where['address_id'] = $address_id;
        $where['user_id'] = UID;
        $info = $this->where($where)->field($this->selectFields)->find();
        return $info;
    }

    // 重置用户的默认地址
    public function resetIsDefault(){
        $this->where('user_id='. UID)->data(array('is_default'=>0))->save();
    }

    // 物理删除用户地址
    public function del($id){
        $where['id'] = $id;
        $where['user_id'] = UID;
        return $this->where($where)->delete();
    }

    protected function _before_insert(&$data, $option){
        $count = $this->where('user_id='. UID)->count();
        if ($count >= 10) {
            $this->error = '最多只能保存10条收货地址';
            return false;
        }
        if ($data['is_default'] == 1) {
            $this->resetIsDefault(); // 重置默认地址
        }

        $data['user_id'] = UID;
    }

    protected function _before_update(&$data, $option){
        if ($data['is_default'] == 1) {
            $this->resetIsDefault(); // 重置默认地址
        }

        $data['user_id'] = UID;
    }

}