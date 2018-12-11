<?php
//用户登录model
namespace app\doctor\model;

use think\Db;
use think\Model;
use think\Exception;

class User
{
    /**
     * 登陆时设置token
     *
     * @param $user_id
     * @param $device
     * @param $channel_id
     *
     * @return string
     */
    public function setToken($user_id, $device = '', $channel_id = "")
    {
        $currentTime = time();
        $token = md5($user_id . $currentTime . rand(0, 10000));

        $where['d_id'] = $user_id;
        $check = db('doctor_apisession', [], false)->where($where)->find();

        $addData = array();
        $addData['d_id'] = $user_id;
        $addData['token'] = $token;
        $addData['expiry'] = strtotime('+14 days');//用创建SESSION时间做间隔判断
        $addData['createtime'] = $currentTime;
        $addData['device'] = $device;
        $addData['is_logout'] = 'no';
        $addData['channel_id'] = $channel_id;
        if ($check) {
            db('doctor_apisession', [], false)->where($where)->update($addData);
        } else {
            db('doctor_apisession', [], false)->insert($addData);
        }
        return $token;
    }

    /**
     * 验证token
     *
     * @param $token
     *
     * @return bool
     */
    public function valiToken($token)
    {
        $currentTime = time();
        $apisession = db('doctor_apisession', [], false)
            ->where("token = '{$token}' AND expiry >= {$currentTime} AND is_logout = 'no' ")
            ->field('d_id')->find();

        if (!empty($apisession)) {
            return $apisession['d_id'];
        } else {
            return false;
        }
    }

    /**
     * 设置token失效
     *
     * @param $d_id
     * @param $token
     *
     * @return boolean
     */
    public function disableToken($d_id, $token)
    {
        $is_login = db('doctor_apisession', [], false)->where("d_id = $d_id AND token = '$token'")->find();
        $result = "";
        if ($is_login) {
            $result = db('doctor_apisession', [],
                false)->where("d_id = $d_id AND token = '$token'")->setField('is_logout', 'yes');
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户ChannelId
     * @param $user_id
     * @param $type
     * @return boolean
     */
    public function getChannelId($user_id, $type = "doctor")
    {
        if ($type == "doctor") {
            $apisession = db('doctor_apisession', [], false)
                ->where("d_id = '{$user_id}'")
                ->field('d_id,device,channel_id')->find();

            if (!empty($apisession)) {
                return $apisession;
            } else {
                return false;
            }
        } else {
            $apisession = db('customer_session', [], false)
                ->where("c_id = '{$user_id}'")
                ->field('c_id,device,channel_id')->find();

            if (!empty($apisession)) {
                return $apisession;
            } else {
                return false;
            }
        }
    }

    /**
     * 修改医生账号信息
     *
     * @param array $where 条件
     * @param array $save 修改内容
     *
     * @return bool
     */
    public function saveDoctor($where = array(), $save = array())
    {
        try {
            if (empty($save) || empty($where)) {
                return false;
            } else {
                $company_info = Db::name('doctor')->where($where)->update($save);
            }
            return $company_info;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取医生账号信息
     *
     * @param array $where 条件
     * @param string $field 修改内容
     *
     * @return bool
     */
    public function getDoctorInfo($where = array(), $field = "password,reg_code,pass_code,is_send")
    {
        //$field = empty($field)?"d_id,real_name,avatar,mobile_num,city,title,hospital,department_name,department_num,qualification_back,qualification_back,qualification_front,money,audit_status,age,gender":$field;
        try {
            if (empty($field) || empty($where)) {
                return false;
            } else {
                $company_info = Db::name('doctor')->where($where)->field($field, true)->find();
            }
            if ($company_info) {
                return $company_info;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取医生账号信息
     *
     * @param array $where 条件
     * @param string $field 修改内容
     *
     * @return bool
     */
    public function getDoctor($where = array(), $field = "")
    {
        try {
            if (empty($field) || empty($where)) {
                return false;
            } else {
                $company_info = Db::name('doctor')->where($where)->field($field)->find();
            }
            if ($company_info) {
                return $company_info;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function getFollowerCount($d_id)
    {
        try {
            $where = "d_id = " . $d_id;
            $Count = Db::name('follow')->where($where)->count();
            return $Count;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConsultMoney($d_id)
    {
        try {
            $where = "d_id = " . $d_id . " AND state = '已完成'";
            $Count = Db::name('consultation')->where($where)->sum('money');
            return $Count;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getImpressionCount($d_id)
    {
        try {
            $where = "d_id = " . $d_id . " AND state = '已完成' AND comment_time != 0 ";
            $Count = Db::name('consultation')->where($where)->count();
            return $Count;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getGift($did, $page = 1, $num = 20)
    {
        try {
            $where['g.d_id'] = $did;
            $field = "g.g_id as g_id,g.c_id as c_id,g.d_id as d_id,g.gift as gift,g.title as title,g.content as content,g.create_time as create_time,c.avatar as avatar,c.nick_name as nick_name,c.mobile_num as mobile_num,c.gender as gender";
            $list = Db::name('gift')->alias('g')->join('customer c','c.c_id = g.c_id')->field($field)->where($where)->order("g.create_time DESC")->page($page,$num)->select();
            return $list;
        } catch (\Exception $e) {
            return false;
        }
    }
}
