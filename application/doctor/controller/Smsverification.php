<?php
namespace app\doctor\controller;

use app\index\controller\Base;
use think\Db;

class SMSVerification extends Base
{

    /**
     * 手机验证码验证统一接口
     */
    public function index()
    {
        $mobile = $_POST['mobile']; // 手机号码
        $code = $_POST['code'];
        $type = $_POST['type']; // 类型 注册:register 找回密码:ResetPassword
        if (empty($mobile) || empty($type) || empty($code)) {
            return $this->private_result('10001');
        }
        switch ($type) {
            case 'pass':
                return $this->ResetPassword($mobile, $code);
                break;
            case 'reg':
                return $this->Register($mobile, $code);
                break;
            default:
                return $this->private_result('10002');
        }
    }

    /**
     * 找回密码 手机验证
     */
    private function ResetPassword($mobile, $code)
    {
        $pass = Db::name('doctor')->where("mobile_num", $mobile)->find(); // 获取mobilecode 格式为code|time
        if (empty($pass)) {
            return $this->private_result('30001');
        }
        if ($pass['pass_code'] == '-1') {
            return $this->private_result('20007');
        }
        $mobCode = explode("|", $pass['pass_code']); // 切割获取的passcode
        $codetime = $mobCode[1];
        $this->valid($codetime);
        if ($code == $mobCode[0]) {
            return $this->private_result('0001', array("key" => md5($pass['pass_code'])));
        } else {
            return $this->private_result('20006');
        }
    }

    /**
     * 账号注册 手机验证
     */
    private function Register($mobile, $code)
    {
        $mobile = Db::name('doctor')->where("mobile_num", $mobile)->find(); // 获取mobilecode 格式为code|time
        if($mobile['reg_code'] == 1){
            return $this->private_result('30002');
        }
        if (!$mobile) {
            return $this->private_result('20007');
        }
        $mobCode = explode("|", $mobile['reg_code']); // 切割获取的passcode
        $codetime = $mobCode[1];
        $valid = $this->valid($codetime);
        if($valid){
            return $this->private_result('20005');
        }

        if ($code != $mobCode[0]) {
            return $this->private_result('20006');
        }
        return $this->private_result('0001', array("key" => md5($mobile['reg_code'])));
    }

    /**
     * 判断短信发送间隔时间
     */
    private function valid($time)
    {
        if ((time() - $time) >= 600) {
            return true;
        }
        return false;
    }
}
