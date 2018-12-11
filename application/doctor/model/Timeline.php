<?php
namespace app\doctor\model;

use think\Model;
use think\Db;
use think\Exception;

class Timeline
{
    public function get($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('timeline')->where($where)->select();
            } else {
                $company_info = Db::name('timeline')->where($where)->field($field)->select();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }

    public function find($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('timeline')->where($where)->find();
            } else {
                $company_info = Db::name('timeline')->where($where)->field($field)->find();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }


    public function set($where, $save)
    {
        try {
            $set = Db::name('timeline')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('timeline')->insert($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('timeline')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            return false;
        }
    }
}
