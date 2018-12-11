<?php
namespace app\doctor\model;

use think\Db;

class Appointment
{
    public function getList($d_id, $type)
    {
        try {
            $field = [
                "ap.ap_id", "ap.cp_id", "ap.d_id", "ap.c_id", "ap.type", "ap.price", "ap.appoint_time", "ap.during",
                "cu.avatar", "cp.*",
            ];
            $dbHandle = Db::name('appointment')->alias('ap')->field($field);
            $dbHandle = $dbHandle->join('yyb_consultation_profile cp', 'ap.cp_id = cp.cp_id');
            $dbHandle = $dbHandle->join('yyb_customer cu', 'ap.c_id = cu.c_id');
            $resultSet = $dbHandle->where('d_id', $d_id)->where('status', 'wait')->where('type', $type)->select();

            return $resultSet;
        } catch (\Exception $e) {
            return false;
        }
    }
}