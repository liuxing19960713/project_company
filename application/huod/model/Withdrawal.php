<?php
namespace app\index\model;

use think\Model;
use think\Db;
use think\Exception;

class Withdrawal
{
    public function select($where = array(), $field = "",$order = '',$page = 1,$num = 20)
    {
        try {
            $db = Db::name('withdrawal')->where($where);
            if (!empty($field)) {
                $db = $db->field($field);
            }
            if(!empty($order)){
                $db = $db->order($order);
            }
            $list = $db->page($page,$num)->select();
            return $list;
        } catch (Exception $e) {
            return false;
        }
    }

    public function find($where = array(), $field = "",$order = '')
    {
        try {
            $db = Db::name('withdrawal')->where($where);
            if (!empty($field)) {
                $db = $db->field($field);
            }
            if(!empty($order)){
                $db = $db->order($order);
            }
            return $db->find();
        } catch (Exception $e) {
            return false;
        }
    }


    public function set($where, $save)
    {
        try {
            $set = Db::name('withdrawal')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('withdrawal')->insert($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('withdrawal')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            return false;
        }
    }
}
