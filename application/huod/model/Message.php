<?php
//用户登录model
namespace app\index\model;

use think\Db;
use think\Model;
use think\Exception;
use app\doctor\model\User as UserModel;
use app\common\model\Baidupush;

class Message
{
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->baiduPush = new Baidupush();
    }

    public function get($where = array(), $field = "")
    {
        try {
            if (empty($field)) {
                $company_info = Db::name('message')->where($where)->select();
            } else {
                $company_info = Db::name('message')->where($where)->field($field)->select();
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
                $company_info = Db::name('message')->where($where)->find();
            } else {
                $company_info = Db::name('message')->where($where)->field($field)->find();
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }


    public function set($where, $save)
    {
        try {
            $set = Db::name('message')->where($where)->update($save);
            return $set;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insert($data)
    {
        try {
            $add = Db::name('message')->insertGetId($data);
            return $add;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($where)
    {
        try {
            $delete = Db::name('message')->where($where)->delete();
            return $delete;
        } catch (Exception $e) {
            return false;
        }
    }

    public function doctorPush($message)
    {
        $user = $this->userModel->getChannelId($message['user_id'], 'doctor');
        if (empty($user)) {
            return false;
        }
        if ($user['device'] == 'ios') {
            $is_push = $this->baiduPush->iosDoctorPush($user['channel_id'], $message['content']);
        } else if ($user['device'] == 'android') {
            $is_push = $this->baiduPush->androidDoctorPush($user['channel_id'], $message['content'],
                $title = $message['title']);
        } else {
            $is_push = false;
        }

        if ($is_push) {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "yes"));
        } else {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "no"));
        }
        return $is_message;
    }

    public function customerPush($message)
    {
        $user = $this->userModel->getChannelId($message['user_id'], 'customer');
        if (empty($user)) {
            return false;
        }
        if ($user['device'] == 'ios') {
            $is_push = $this->baiduPush->iosCustomerPush($user['channel_id'], $message['content']);
        } else if ($user['device'] == 'android') {
            $is_push = $this->baiduPush->androidCustomerPush($user['channel_id'], $message['content'],
                $title = $message['title']);
        } else {
            $is_push = false;
        }

        if ($is_push) {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "yes"));
        } else {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "no"));
        }
        return $is_message;
    }

    public function allPush($message)
    {
        $is_message = true;

        $is_message &= $this->baiduPush->androidDoctorPushAll($message['content']);
        $is_message &= $this->baiduPush->androidCustomerPushAll($message['content']);

        $is_message &= $this->baiduPush->iosDoctorPushAll($message['content']);
        $is_message &= $this->baiduPush->iosCustomerPushAll($message['content']);

        if ($is_message) {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "yes"));
        } else {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "no"));
        }

        return $is_message;
    }

    public function allDoctorPush($message)
    {
        $is_message = true;

        $is_message &= $this->baiduPush->androidDoctorPushAll($message['content']);
        $is_message &= $this->baiduPush->iosDoctorPushAll($message['content']);

        if ($is_message) {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "yes"));
        } else {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "no"));
        }

        return $is_message;
    }

    public function allCustomerPush($message)
    {
        $is_message = true;

        $is_message &= $this->baiduPush->androidCustomerPushAll($message['content']);
        $is_message &= $this->baiduPush->iosCustomerPushAll($message['content']);

        if ($is_message) {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "yes"));
        } else {
            $is_message = $this->set("me_id = " . $message['me_id'], array("status" => "no"));
        }

        return $is_message;
    }

    public function publicPush($user_id, $user_type, $content = '', $extra = '', $title = '', $sub_type = '')
    {
        $message['user_id'] = empty($user_id) ? -1 : $user_id;
        $message['user_type'] = empty($user_type) ? 'all' : $user_type;
        $message['content'] = $content;
        $message['extra'] = $extra;
        $message['sub_type'] = $sub_type;
        $message['title'] = empty($title) ? '优医惠' : $title;
        $message['create_time'] = time();
        $message['status'] = 'wait';
        $message['me_id'] = $this->insert($message);

        if (!$message['me_id']) {
            return false;
        }

        $is_message = false;

        /* 判断推送类型 并推送*/
        if ($message['user_type'] == 'doctor' && $message['user_id'] != '-1') { //推送医生个人消息
            $is_message = $this->doctorPush($message);
        } else if ($message['user_type'] == 'customer' && $message['user_id'] != '-1') {//推送用户个人消息
            $is_message = $this->customerPush($message);
        } else if ($message['user_type'] == 'all') {//推送 所有端
            $is_message = $this->allPush($message);
        } else if ($message['user_type'] == 'doctor' && $message['user_id'] == '-1') {//推送 医生端
            $is_message = $this->allDoctorPush($message);
        } else if ($message['user_type'] == 'customer' && $message['user_id'] == '-1') {//推送 用户端
            $is_message = $this->allCustomerPush($message);
        }

        if ($is_message) {
            return true;
        } else {
            return false;
        }
    }

    public function insertMassage(
        $user_id,
        $user_type,
        $content = '',
        $extra = '',
        $title = '',
        $sub_type = '',
        $status = 'wait'
    ) {
        $message['user_id'] = empty($user_id) ? -1 : $user_id;
        $message['user_type'] = empty($user_type) ? 'all' : $user_type;
        $message['content'] = $content;
        $message['extra'] = $extra;
        $message['sub_type'] = $sub_type;
        $message['title'] = empty($title) ? '优医惠' : $title;
        $message['create_time'] = time();
        $message['status'] = $status;
        $message['me_id'] = $this->insert($message);

        if (!$message['me_id']) {
            return false;
        }

        return $message;
    }

    public function insertAll($array)
    {
        try {
            $bool = Db::name('message')->insertAll($array);
            return $bool;
        }catch (\Exception $e){
            var_dump($e);
            return false;
        }
    }
}
