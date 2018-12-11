<?php
/**
 * Created by PhpStorm.
 * User: Airon
 * Date: 2016/12/5
 * Time: 10:41
 * Comments: 聊天常用模板
 */
namespace app\doctor\model;

use think\Db;
use think\Model;
use think\Exception;

class ChatTemplates
{

    public function get($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('chat_templates')->where($where)->select();
            } else {
                $company_info = Db::name('chat_templates')->where($where)->field($field)->select();
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
                $company_info = Db::name('chat_templates')->where($where)->find();
            } else {
                $company_info = Db::name('chat_templates')->where($where)->field($field)->find();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }


    public function update($where, $save)
    {
        try {
            $set = Db::name('chat_templates')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('chat_templates')->insert($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('chat_templates')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            return false;
        }
    }
}
