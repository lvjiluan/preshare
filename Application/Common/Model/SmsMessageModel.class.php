<?php
/**
 * Created by PhpStorm.
 * User: jipingzhao liuniukeji.com
 * Date: 6/30/17
 * Time: 2:53 PM
 */
namespace Common\Model;
use Think\Model;
class SmsMessageModel extends Model
{
    // 短信记录添加
    public function addSmsMessage($data){
        $data['add_time'] = NOW_TIME;
        $this->data($data)->add();
    }

    /**
     * 验证 短信验证是否正确
     * @param unknown_type $code
     * @param unknown_type $mobile
     */
    public function checkSmsMessage($code, $mobile) {
        //echo C('SMS_REGIStER_CODE_LEN');
        if (strlen($code) != C('SMS_CODE_LEN')){
            return V(0, '短信验证码长度有误');
        }
        if (!isMobile($mobile)){
            return V(0, '手机号码长度有误');
        }
        $where['sms_code'] = $code;
        $where['mobile'] = $mobile;
        $where['add_time'] = array('EGT', NOW_TIME - C('SMS_EXPIRE_TIME') * 60);
        $where['is_used'] = 0;
        $smsInfo = $this->where($where)->find();

        if (count($smsInfo) > 0) {
            $this->where($where)->setField('is_used', 1);
            return V(1, '短信验证码正确', $mobile);
        } else {
            return V(0, '短信验证码不正确或已失效');
        }
    }
}