<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/12/9
 * Time: 14:24
 */
namespace app\doctor\controller;

use app\index\controller\Base;
use think\Db;
use app\doctor\model\User as UserModel;
use app\doctor\model\Timeline as TimelineModel;
use think\Exception;

class Timeline extends Base
{
    public $TimelineModel;
    public $UserModel;
    private $text = '800:yes|830:yes|900:yes|930:yes|1000:yes|1030:yes|1100:yes|1130:yes|1200:yes|1230:yes|1300:yes|1330:yes|1400:yes|1430:yes|1500:yes|1530:yes|1600:yes|1630:yes|1700:yes|1730:yes|1800:yes|1830:yes|1900:yes|1930:yes|2000:yes|2030:yes|2100:yes|2130:yes|2200:yes|2230:yes';

    public function __construct()
    {
        parent::__construct();
        $this->UserModel = new UserModel();
        $this->TimelineModel = new TimelineModel();
    }

    //获取个人时间表
    public function getSetting()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $where['d_id'] = $d_id;
        $line = $this->TimelineModel->find($where, "schedule");
        if (!$line) {
            return $this->private_result('40001');
        }

        $array = $this->getValues($line['schedule']);

        return $this->private_result("0001", $array);
    }

    //设置时间表
    public function update()
    {
        $token = get_token();
        $content = get_post_value("content", "");//传逗号隔开字符串
        if (empty($token) || empty($content)) {
        }
        $validateResult = validate_regex($content, '/^((yes,|no,){29}(yes|no){1})$/');
        $content = explode(",", $content);
        if (!$validateResult || count($content) != 30) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $where['d_id'] = $d_id;
        $line = $this->TimelineModel->find($where, "schedule");
        if (!$line) {
            return $this->private_result('40001');
        }
        $array = customSerialize(array_combine($this->getKeys($this->text), $content));
        $update = $this->TimelineModel->set($where, array("schedule" => $array));
        if ($update === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result("0001");
    }

    //获取医生时间表  包括已预约
    public function getTimeLine()
    {
        $d_id = get_post_value("d_id");
        $type = get_post_value("type");
        if (empty($d_id) || empty($type)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_regex($type, '/^(today|tomorrow)$/') && validate_number($d_id);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        $where['d_id'] = $d_id;
        $line = $this->TimelineModel->find($where, "schedule,today_s,tomorrow_s");
        if (!$line) {
            $this->TimelineModel->insert(array("d_id" => $d_id));//todo::  前期需要  后期删除
            $line = $this->TimelineModel->find($where, "schedule,today_s,tomorrow_s");
            //return $this->private_result('40001');
        }
        $schedule1 = $this->getValues($line['schedule']);//医生当前设置

        if ($type == "today") {
            $schedule2 = $line['today_s'];
        } else {
            $schedule2 = $line['tomorrow_s'];
        }
        $schedule2 = $this->getValues($schedule2);// 今日 或 明日 预约
        $schedule = array();
        for ($i = 0; $i < count($schedule1); $i++) {
            if ($schedule1[$i] == "yes" && $schedule2[$i] == "yes") {
                $schedule[$i] = "yes";
            } else {
                $schedule[$i] = "no";
            }
        }

        $array = array_values($schedule);

        return $this->private_result("0001", $array);
    }

    //每天00：00更新预约表 后台使用
    public function updateAll()
    {
        $token = get_post_value("token", "");
        $validateResult = empty($token);
        if ($validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }
        if (ADMIN_TOKEN != $token) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $save['today_s'] = "`tomorrow_s`";
        $save['tomorrow_s'] = $this->text;
        try {
            $is_update = Db::query("UPDATE `yyb_timeline` SET `today_s`=`tomorrow_s` WHERE 1");
            $is_update = Db::query("UPDATE `yyb_timeline` SET `tomorrow_s`= '" . $this->text . "' WHERE 1");
        } catch (Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result("0001");
    }

    private function getKeys($array)
    {

        $array = customUnserialize($array);
        return array_keys($array);
    }

    private function getValues($array)
    {

        $array = customUnserialize($array);
        return array_values($array);
    }
}
