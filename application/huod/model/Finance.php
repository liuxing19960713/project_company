<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/12/22
 * Time: 17:12
 */
//用户登录model
namespace app\index\model;

use think\Db;
use think\Model;
use think\Exception;

class Finance
{
    public function select($user_id, $user_type, $type = '', $page = 1, $num = 20)
    {
        $where = "user_id = {$user_id} AND user_type = '".$user_type."'";
        if (!empty($type) && $type != 'all') {
            $where .= " AND `type` = '".$type."'";
        }else if($user_type == 'doctor'){
            $where .= " AND `type` != 'withdrawal' AND `type` != 'refund'";
        }
        try {
            if (empty($field)) {
                $company_info = Db::name('finance')->where($where)->order("create_time DESC")->page($page,
                    $num)->select();
            } else {
                $company_info = Db::name('finance')->where($where)->field($field)->order("create_time DESC")->page($page,
                    $num)->select();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }

    //添加交易记录
    public function insert($user_id, $user_type, $money, $type, $status, $extra = '', $create_time)
    {
        $data = array(
            'user_id' => $user_id,
            'user_type' => $user_type,
            'money' => $money,
            'type' => $type,
            'status' => $status,
            'extra' => $extra,
            'create_time' => $create_time
        );
        try {
            $add = Db::name('finance')->insertGetId($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    //医生财务管理 首页
    public function count($d_id)
    {
        $array = array(
            'withdrawal' => 0,//提现
            'invite' => 0,
            'refund' => 0,//退款
            'gift' => 0,
            'private' => 0,
            'guidance' => 0,
            'video' => 0,
            'phone' => 0,
            'image' => 0,
            'total' => 0,
            'extend'=> 0,
            'balance' => 0
        );
        try {
            $doctor = Db::name('doctor')->where("d_id = $d_id")->field('money')->find();
            $data = Db::name('finance')->where("user_id = {$d_id} AND user_type = 'doctor'")->field('type,round(sum(money),2) as money')->group('type')->select();
            for($i = 0;$i<count($data);$i++){
                $array[$data[$i]['type']] = $data[$i]['money'];
                if($data[$i]['type'] != 'withdrawal' && $data[$i]['type'] != 'refund'){//不属于收益
                    $array['total'] += $data[$i]['money'];
                }
            }
            $array['balance'] = $doctor['money'];
            $array['withdrawal'] = $array['withdrawal'] - $array['refund'];
            unset($array['refund']);
            return $array;
        } catch (\Exception $e) {
            return false;
        }
    }
}
