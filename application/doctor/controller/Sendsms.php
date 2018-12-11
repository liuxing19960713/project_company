<?php
namespace app\doctor\controller;

use app\index\controller\Base;
use think\Db;
use vendor\alidayu\request\Smsnumsend;
use app\common\model\Alidayu;
use vendor\alidayu\Alidayuclient;

class Sendsms extends Base
{
    /**
     * 发送短信方法
     * 通过type来判断发送什么类型的短信
     */
    public function index()
    {
        if (!$_POST) {
            exit;
        }
        $mobile = $_POST['mobile'];
        $type = $_POST['type'];//reg  pass

        if (empty($mobile) || empty($type)) {
            return $this->private_result("10001");
        }

        if ($type == 'reg') {
            $is_register = Db::name('doctor')->where("mobile_num = $mobile AND reg_code = 1")->find();
            if ($is_register) {
                return $this->private_result("30002");
            }
        }

        if ($type == 'pass') {
            $is = Db::name('doctor')->where("mobile_num=$mobile AND reg_code = 1")->find();
            if (!$is) {
                return $this->private_result("30001");
            }
        }

        if ($type != 'reg' && $type != 'pass') {
            return $this->private_result('10002');
        }
        $alidayu = new Alidayu();
        $code = rand_string();
        $array = $alidayu->send($mobile, $code, $type);

        if ($array['errcode'] != '0') {
            return $this->private_result('20001');
        }
        $sql = false;
        if ($type == 'reg') {
            $is_reg_key = Db::name('doctor')->where("mobile_num", $mobile)->find();
            if (!$is_reg_key) {
                $data['mobile_num'] = $mobile;
                $data['reg_code'] = $code . "|" . time();
                $sql = Db::name('doctor')->where($mobile)->insertGetId($data);
            } else {
                $data['reg_code'] = $code . "|" . time();
                $sql = Db::name('doctor')->where('mobile_num', $mobile)->update($data);
            }
        } else if ($type == 'pass') {
            $data['mobile_num'] = $mobile;
            $data['pass_code'] = $code . "|" . time();
            $sql = Db::name('doctor')->where("mobile_num = $mobile")->update($data);
        }
        if ($sql) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('20001');
        }
    }

}
