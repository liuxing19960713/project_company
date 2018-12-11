<?php
//用户登录model
namespace app\doctor\model;

use think\Db;
use think\Model;
use think\Exception;

class Hospital
{

    public function get($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('department')->where($where)->select();
            } else {
                $company_info = Db::name('department')->where($where)->field($field)->select();
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
                $company_info = Db::name('department')->where($where)->find();
            } else {
                $company_info = Db::name('department')->where($where)->field($field)->find();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }


    public function set($where, $save)
    {
        try {
            $set = Db::name('department')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function add($data)
    {
        try {
            $add = Db::name('department')->insert($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('department')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            return false;
        }
    }
}
