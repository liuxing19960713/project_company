<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/12/6
 * Time: 17:48
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\Exception;
use app\doctor\model\User as UserModel;
use app\common\model\Baidupush;

class Extend extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->baiduPush = new Baidupush();
    }

    public function select($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('ex_consultation')->where($where)->select();
            } else {
                $company_info = Db::name('ex_consultation')->where($where)->field($field)->select();
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
                $company_info = Db::name('ex_consultation')->where($where)->find();
            } else {
                $company_info = Db::name('ex_consultation')->where($where)->field($field)->find();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }


    public function set($where, $save)
    {
        try {
            $set = Db::name('ex_consultation')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('ex_consultation')->insertGetId($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }
}
