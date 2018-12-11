<?php
//用户登录model
namespace app\index\model;

use think\Db;
use think\Model;
use think\Exception;

class Chat
{

    public function select($where = array(), $field = "", $page = 1, $num = 20, $lastId = null, $order = 'DESC')
    {
        try {
            if ($lastId !== null) {
                $lastInfo = Db::name('chat_record')->field(['cr_id', 'create_time'])->where($where)->where('cr_id', $lastId)->find();
            }

            $query = Db::name('chat_record');
            if (!empty($field)) {
                $query = $query->field($field);
            }
            $query = $query->where($where);

            if ($lastId === null) {
                $query = $query->order("create_time " . $order)->page($page, $num);
            } else {
                if (empty($lastInfo)) {
                    return [];
                }
                $orderId = $lastInfo['create_time'];
                $whereOrder = ($order == 'DESC') ? 'elt' : 'egt';
                $query = $query->where('cr_id', 'neq', $lastId)->where("create_time", $whereOrder, $orderId)->order("create_time " . $order)->limit($num);
            }
            $chat_list = $query->select();
            return $chat_list;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('chat_record')->insert($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getExtend($con_id, $d_id, $page = 1, $num = 20)
    {
        try {
            $field = "chat.cr_id as cr_id,chat.c_id as c_id,chat.d_id as d_id,chat.con_id as con_id,chat.content as content,chat.msg_type as msg_type,chat.create_time as create_time,chat.chat_to as chat_to";
            $list = Db::name('ex_consultation')->alias('ex')->field($field)
                ->join('chat_record chat', 'chat.con_id = b_con_id')
                ->where("ex.d_id = $d_id AND ex.con_id = $con_id AND ex.create_time > chat.create_time")
                ->order("chat.create_time DESC")
                ->page($page, $num)
                ->select();
            return $list;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getExtendInfo($con_id, $d_id)
    {
        try {
            $field = "do.real_name, do.avatar, do.d_id, ex.b_con_id, con.evaluation, con.comment_time, con.state";
            $list = Db::name('ex_consultation')->alias('ex')->field($field)
                ->join('doctor do', 'ex.b_d_id = do.d_id')
                ->join('consultation con', 'ex.b_con_id = con.con_id')
                ->where("ex.d_id = $d_id AND ex.con_id = $con_id")
                ->find();
            return $list;
        } catch (Exception $e) {
            return false;
        }
    }

}
