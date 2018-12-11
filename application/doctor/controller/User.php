<?php
namespace app\doctor\controller;

use app\index\controller\Base;
use think\Db;
use app\doctor\model\User as UserModel;
use app\doctor\model\Hospital;
use app\common\model\Qiniu;
use app\common\model\Easemob;
use app\doctor\model\Timeline;
use app\index\model\Finance;

class User extends Base
{
    public $UserModel;
    public $Hospital;
    public $TimeLine;

    public function __construct()
    {
        parent::__construct();
        $this->UserModel = new UserModel();
        $this->Hospital = new Hospital();
        $this->TimeLine = new Timeline();
    }

    //获取用户信息
    public function userInfo()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $where = "d_id = $d_id";
        $doctor = $this->UserModel->getDoctorInfo($where);
        if (!$doctor) {
            return $this->private_result('30001');
        }
        $doctor['follower_count'] = $this->UserModel->getFollowerCount($d_id);
        $doctor['consult_money'] = $this->UserModel->getConsultMoney($d_id);
        $doctor['impression_count'] = $this->UserModel->getImpressionCount($d_id);
        $where .= " AND is_default = 'yes'";
        $hospital = $this->Hospital->find($where, "de_id,department_phone,hospital,department1,department2");

        $doctor['de_id'] = empty($hospital['de_id'])?0:$hospital['de_id'];
        $doctor['department_phone'] = empty($hospital['department_phone']) ? "" : $hospital['department_phone'];
        $doctor['hospital'] = empty($hospital['hospital']) ? "" : $hospital['hospital'];
        $doctor['department1'] = empty($hospital['department1']) ? "" : $hospital['department1'];
        $doctor['department2'] = empty($hospital['department2']) ? "" : $hospital['department2'];
        if ($doctor) {
            return $this->private_result('0001', $doctor);
        } else {
            return $this->private_result('10006');
        }
    }

    /**
     * 用户注册接口
     * POST   mobile 手机
     * password  密码
     * key    通过短信验证返回的key
     */
    public function register()
    {
        $mobile = get_post_value('mobile', '');
        $password = get_post_value('password', '');
        $key = get_post_value('key', '');
        if (empty($mobile) || empty($key) || empty($password)) {
            return $this->private_result('10001');
        }
        $validateResult = validate_number($mobile) && validate_is($key, 'alphaNum');
        if (!$validateResult) {
            return $this->private_result('10002');
        }
        $data = Db::name('doctor')->where("mobile_num=$mobile")->field('d_id,reg_code')->find();
        $d_id = $data['d_id'];
        if ($key != md5($data['reg_code'])) {
            return $this->private_result('20008');
        }
        unset($data);
        $invite_code = empty($_POST['invite_code']) ? "" : $_POST['invite_code'];
        if (!empty($invite_code)) {
            $invite = Db::name('doctor')->where('invite_code', $invite_code)->field("d_id")->find();
            if (empty($invite)) {
                return $this->private_result("30011");//邀请码错误
            }
            $data['invite_d_id'] = $invite['d_id'];
        }
        trans_start();
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $easemob_account = $easemob->regChatUser($token, $d_id, 'doctor');
        if (empty($easemob_account)) {
            trans_rollback();
            return $this->private_result('30010');
        }
        $data['easemob_username'] = $easemob_account['username'];
        $data['easemob_password'] = $easemob_account['password'];
        $data['invite_code'] = $this->get_unique_invite_code();
        $data['password'] = md5($password);
        $data['mobile_num'] = $mobile;
        $data['create_time'] = time();
        $data['reg_code'] = 1;
        $reg = Db::name('doctor')->where("mobile_num=$mobile")->update($data);
        $insert_timeline = $this->TimeLine->insert(array("d_id"=>$d_id));
        if ($reg && $insert_timeline) {
            trans_commit();
            return $this->private_result('0001');
        } else {
            trans_rollback();
            return $this->private_result('30010');
        }
    }

    /**
     * 用户登录接口
     * POST   mobile 手机
     * password  密码
     */
    public function userLogin()
    {
        $where['mobile_num'] = get_post_value('mobile', '');//手机号
        $where['password'] = md5($_POST['password']);//密码
        $device = get_device();
        $channel_id = get_post_value('channel_id', '');
        if (empty($where['mobile_num']) || empty($where['password']) || empty($channel_id) || empty($device)) {
            return $this->private_result('10001');
        }
        $validateResult = validate_number($where['mobile_num']) && validate_is($channel_id, 'alphaNum');
        if (!$validateResult) {
            return $this->private_result('10002');
        }
        $doctor = db('doctor', [],
            false)->where($where)->field('d_id,mobile_num,real_name,easemob_username,easemob_password')->find();
        if ($doctor) {
            // 调用自定义的render方法,0001代表正确返回
            db('doctor', [], false)->where("d_id={$doctor['d_id']}")->setField('last_login_time', time());
            $token = $this->UserModel->setToken($doctor['d_id'], $device, $channel_id);
            $doctor['token'] = $token;

            return $this->private_result('0001', $doctor);
        } else {
            return $this->private_result('30005');
        }
    }

    //修改密码
    public function modifyPassword()
    {
        $token = get_token();
        if (empty($token) || empty($token) || empty($_POST['newPassword']) || empty($_POST['oldPassword'])) {
            return $this->private_result("10001");
        }
        $uid = $this->UserModel->valiToken($token);
        if (!$uid) {
            return $this->private_result('10003');
        }
        $newPassword = md5($_POST['newPassword']);
        $oldPassword = md5($_POST['oldPassword']);
        $temp = db('doctor', [], false)->where("d_id='$uid' AND password='$oldPassword'")->find();
        if (!$temp) {
            return $this->private_result('30014');
        }
        if ($newPassword == $oldPassword) {
            return $this->private_result('30013');
        }
        $temp['password'] = $newPassword;
        $update = db('doctor', [], false)->where("d_id='$uid' AND password='$oldPassword'")->update($temp);
        if ($update === 0 || !empty($update)) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //找回密码
    public function resetPassword()
    {
        $mobile = get_post_value('mobile', '');;
        $new_password = md5($_POST['password']);
        $key = get_post_value('key', '');
        if (empty($new_password) || empty($mobile) || empty($key)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_number($mobile) && validate_is($key, 'alphaNum');
        if (!$validateResult) {
            return $this->private_result('10002');
        }
        $pass = db('doctor', [], false)->where("mobile_num='$mobile'")->find();
        if ($key != md5($pass['pass_code'])) {
            return $this->private_result('20008');
        }
        $data['password'] = $new_password;
        $data['pass_code'] = '-1';
        $update = db('doctor', [], false)->where("mobile_num='$mobile'")->update($data);
        if ($update === 0 || !empty($update)) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //退出登录
    public function userLogout()
    {
        $token = get_token();
        $d_id = $this->UserModel->valiToken($token);
        if (!empty($d_id)) {
            $this->UserModel->disableToken($d_id, $token);
        }
        return $this->private_result('0001');
    }

    //上传认证信息
    public function Authentication()
    {
        $token = get_token();
        $validate['qualification_front'] = get_post_value('qualification_front');
        $validate['qualification_back'] = get_post_value('qualification_back');
        if (empty($validate['qualification_front']) || empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $where['d_id'] = $is_token;
        $validate['time'] = time();
        trans_start();
        $get = Db::name('doctor_audit')->where($where)->find();
        if (empty($get)) {
            $validate['d_id'] = $is_token;
            $insert = Db::name('doctor_audit')->insertGetId($validate);
        } else {
            if ($get['status'] == 'yes') {
                return $this->private_result('40013');
            }
            if ($get['status'] == 'wait') {
                return $this->private_result('40014');
            }
            $validate['status'] = 'wait';
            $insert = Db::name('doctor_audit')->where($where)->update($validate);
        }
        $set = Db::name('doctor')->where($where)->update(array("audit_status" => "wait"));
        if ($insert && $set !== false) {
            trans_commit();
            return $this->private_result('0001');
        } else {
            trans_rollback();
            return $this->private_result('10006');
        }
    }

    //设置所在城市
    public function userSetCity()
    {
        $token = get_token();
        $data['area1'] = !isset($_POST['area1']) ? "" : $_POST['area1'];
        $data['area2'] = !isset($_POST['area2']) ? "" : $_POST['area2'];
        if (empty($data['area1']) || empty($token) || empty($data['area2'])) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $update = $this->UserModel->saveDoctor(array('d_id' => $is_token), $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //设置所在个人简介
    public function userSetintro()
    {
        $token = get_token();
        $data['intro1'] = !isset($_POST['intro1']) ? "" : $_POST['intro1'];
        $data['intro2'] = !isset($_POST['intro2']) ? "" : $_POST['intro2'];
        $data['intro3'] = !isset($_POST['intro3']) ? "" : $_POST['intro3'];

        if (empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);

        if (!$is_token) {
            return $this->private_result('10003');
        }

        $update = $this->UserModel->saveDoctor(array('d_id' => $is_token), $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //设置擅长疾病
    public function userSetGooodAt()
    {
        $token = get_token();
        $goood_at = isset($_POST['good_at']) ? $_POST['good_at'] : "";

        if (empty($token) || empty($goood_at)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);

        if (!$is_token) {
            return $this->private_result('10003');
        }
        $data = $this->UserModel->getDoctor("d_id = $is_token", "good_at");
        if (!empty($data['good_at'])) {
            $array = empty($data['good_at']) ? "" : explode("|", $data['good_at']);
            for ($i = 0; $i < count($array); $i++) {
                if ($array[$i] == $goood_at) {
                    return $this->private_result('40005');
                }
            }
            if (count($array) < 10) {
                $array[] = $goood_at;
            } else {
                return $this->private_result('30012');
            }
            $data['good_at'] = empty($array) ? "" : implode("|", $array);
        } else {//添加第一个标签
            $data['good_at'] = $goood_at;
        }
        $update = $this->UserModel->saveDoctor("d_id = $is_token", $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //删除擅长疾病
    public function userDelGooodAt()
    {
        $token = get_token();
        $goood_at = isset($_POST['good_at']) ? $_POST['good_at'] : "";

        if (empty($token) || empty($goood_at)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);

        if (!$is_token) {
            return $this->private_result('10003');
        }
        $data = $this->UserModel->getDoctor("d_id = $is_token AND `good_at` like '%" . $goood_at . "%'", "good_at");
        if (!$data) {
            return $this->private_result('40001');
        }
        $old_array = empty($data['good_at']) ? "" : explode("|", $data['good_at']);
        for ($i = 0; $i < count($old_array); $i++) {
            if ($old_array[$i] != $goood_at) {
                $array[] = $old_array[$i];
            }
        }
        $data['good_at'] = empty($array) ? "" : implode("|", $array);
        $update = $this->UserModel->saveDoctor("d_id = $is_token", $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //设置个人基本信息
    public function userSetInfo()
    {
        $token = get_token();
        $validateResult = true;
        $data = array();
        if (isset($_POST['real_name'])) {
            $data['real_name'] = get_post_value('real_name');
        }
        if (isset($_POST['title'])) {
            $data['title'] = get_post_value('title');
        }
        if (isset($_POST['gender'])) {
            $data['gender'] = get_post_value('gender');
            $validateResult = validate_regex($data['gender'], '/^(男|女)$/') && $validateResult;
        }
        if (isset($_POST['birthday'])) {
            $data['birthday'] = get_post_value('birthday');
            $validateResult = validate_number($data['birthday']) && $validateResult;
        }
        if (isset($_POST['id_card'])) {
            $data['id_card'] = get_post_value('id_card');
        }
        if (isset($_POST['email'])) {
            $data['email'] = get_post_value('email');
            $validateResult = validate_is($data['email'], 'email') && $validateResult;
        }
        if (isset($_POST['avatar'])) {
            $data['avatar'] = $_POST['avatar'];
        }
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$validateResult) {
            return $this->private_result('10002');
        }

        if (!$is_token) {
            return $this->private_result('10003');
        }
        $update = $this->UserModel->saveDoctor(array('d_id' => $is_token), $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //修改 科室信息
    public function userSetHospital()
    {
        $token = get_token();
        if (empty($token) || !isset($_POST['de_id']) || !isset($_POST['hospital']) || !isset($_POST['department1'])) {
            return $this->private_result('10001');
        }
        if (empty($_POST)) {
            return $this->private_result('10002');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }

        $where['de_id'] = $_POST['de_id'];//department ID
        $where['d_id'] = $is_token;
        $is_get = $this->Hospital->find($where, 'de_id,is_audited');
        if (!$is_get) {
            return $this->private_result('40001');
        }
        if ($is_get['is_audited'] == 'wait') {
            return $this->private_result('40014');
        }
        $data['audit_hospital'] = $_POST['hospital'];
        $data['audit_department_phone'] = !isset($_POST['department_phone']) ? "" : $_POST['department_phone'];
        $data['audit_department1'] = $_POST['department1'];
        $data['audit_department2'] = !isset($_POST['department2']) ? "" : $_POST['department2'];
        $data['is_audited'] = 'wait';
        $data['feedback'] = '';


        $update = $this->Hospital->set($where, $data);
        if ($update !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //获取 科室信息
    public function userGetHospital()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);

        if (!$is_token) {
            return $this->private_result('10003');
        }

        $where['d_id'] = $is_token;
        $get = $this->Hospital->get($where);
        if ($get !== false) {
            return $this->private_result('0001', $get);
        } else {
            return $this->private_result('10006');
        }
    }

    //新增 科室信息
    public function userAddHospital()
    {
        $token = get_token();
        $data['audit_hospital'] = !isset($_POST['hospital']) ? "" : $_POST['hospital'];
        $data['audit_department_phone'] = !isset($_POST['department_phone']) ? "" : $_POST['department_phone'];
        $data['audit_department1'] = !isset($_POST['department1']) ? "" : $_POST['department1'];
        $data['audit_department2'] = !isset($_POST['department2']) ? "" : $_POST['department2'];
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);

        if (!$is_token) {
            return $this->private_result('10003');
        }
        $data['d_id'] = $is_token;
        $get = $this->Hospital->get(array("d_id" => $is_token), 'de_id');
        if ($get === false) {
            return $this->private_result('10006');
        } else {
            if (count($get) == 3) {
                return $this->private_result('40002');
            } else {
                if (count($get) == 0) {
                    $data['is_default'] = 'yes';
                } else {
                    $data['is_default'] = 'no';
                }
            }
        }
        $data['is_audited'] = "wait";
        $add = $this->Hospital->add($data);
        if ($add !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //删除 科室信息
    public function userDelHospital()
    {
        $token = get_token();
        $where['de_id'] = get_post_value('de_id');
        if (empty($token) || empty($where['de_id'])) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }

        $where['d_id'] = $is_token;
        $get = $this->Hospital->get($where, 'is_default,is_audited');
        if (!$get) {
            return $this->private_result('40001');
        } else {
            if ($get[0]['is_default'] == 'yes') {
                return $this->private_result('40003');
            }
            if ($get[0]['is_audited'] == 'wait') {
                return $this->private_result('40014');
            }
        }
        $add = $this->Hospital->delete($where);
        if ($add !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //设置默认 科室信息
    public function setDefaultHospital()
    {
        $token = get_token();
        $de_id = get_post_value('de_id');
        if (empty($token) || empty($de_id)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        trans_start();
        $info = $this->Hospital->find("de_id = $de_id AND d_id = $is_token", "is_audited");//设置默认科室信息
        if (empty($info)) {
            return $this->private_result('40001');
        }
        if ($info['is_audited'] != 'yes') {
            return $this->private_result('40012');//未审核
        }
        $where['d_id'] = $is_token;
        $where['is_default'] = 'yes';
        $save['is_default'] = 'no';
        $save_cancel = $this->Hospital->set($where, $save);//先取消默认科室信息
        if (empty($save_cancel)) {
            trans_rollback();
        }
        unset($where['is_default']);
        $where['de_id'] = $de_id;
        $where['is_audited'] = 'yes';//审核通过才能修改
        $save['is_default'] = 'yes';
        $save_defautl = $this->Hospital->set($where, $save);//设置默认科室信息
        if (!empty($save_defautl)) {
            trans_commit();
            return $this->private_result('0001');
        } else {
            trans_rollback();
            return $this->private_result('10006');
        }
    }

    //修改推送状态
    public function setPush()
    {
        $token = get_token();
        $is_push = get_post_value('is_push');
        if (empty($token) || empty($is_push)) {
            return $this->private_result('10001');
        }
        $validate= validate_regex($is_push, '/^(yes|no)$/');
        if (!$validate) {
            return $this->private_result('10002');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $where['d_id'] = $is_token;
        $save['is_push'] = $is_push;
        $save = $this->UserModel->saveDoctor($where, $save);
        if ($save !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //设置短信发送时间
    public function setSendTime()
    {
        $token = get_token();
        $string = get_post_value('data', "");
        if (empty($token) || empty($string)) {
            return $this->private_result('10001');
        }
        $array = explode(",", $string);
        $is_array = is_array($array) && count($array) == 4;
        $bool = true;
        $keys = array("morning", "noon", "afternoon", "night");
        for ($i = 0; $i < count($array); $i++) {
            if (!validate_regex($array[$i], '/^(yes|no)$/')) {
                $bool = false;
                break;
            }
        }
        if (!$bool || !$is_array) {
            return $this->private_result('10002');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $save['is_send'] = array_combine($keys, $array);
        $save['is_send'] = customSerialize($save['is_send']);
        $where['d_id'] = $is_token;
        $save = $this->UserModel->saveDoctor($where, $save);
        if ($save !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }
    }

    //获取短信发送时间
    public function getSendTime()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $where['d_id'] = $is_token;
        $save = $this->UserModel->getDoctor($where, "is_send");
        $send = customUnserialize($save['is_send']);
        if ($save !== false) {
            return $this->private_result('0001', $send);
        } else {
            return $this->private_result('10006');
        }
    }

    //设置我的服务
    public function setMyService()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $data = array();
        $validateResult = true;
        if (isset($_POST['is_open_image'])) {
            $data['is_open_image'] = $_POST['is_open_image'];
            $validateResult = validate_regex($data['is_open_image'], '/^(yes|no)$/') && $validateResult;
        }
        if (isset($_POST['is_open_video'])) {
            $data['is_open_video'] = $_POST['is_open_video'];
            $validateResult = validate_regex($data['is_open_video'], '/^(yes|no)$/') && $validateResult;
        }
        if (isset($_POST['is_open_private'])) {
            $data['is_open_private'] = $_POST['is_open_private'];
            $validateResult = validate_regex($data['is_open_private'], '/^(yes|no)$/') && $validateResult;
        }
        if (isset($_POST['is_open_guidance'])) {
            $data['is_open_guidance'] = $_POST['is_open_guidance'];
            $validateResult = validate_regex($data['is_open_guidance'], '/^(yes|no)$/') && $validateResult;
        }
        if (isset($_POST['is_open_phone'])) {
            $data['is_open_phone'] = $_POST['is_open_phone'];
            $validateResult = validate_regex($data['is_open_phone'], '/^(yes|no)$/') && $validateResult;
        }
        if (empty($data)) {
            return $this->private_result('10001');
        }
        if (!$validateResult) {
            return $this->private_result('10002');
        }
        $is_token = $this->UserModel->valiToken($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }

        $where['d_id'] = $is_token;
        $save = $this->UserModel->saveDoctor($where, $data);
        if ($save !== false) {
            return $this->private_result('0001');
        } else {
            return $this->private_result('10006');
        }

    }

    //获取七牛配置文件
    public function BucketDomain()
    {
        //读取七牛域名
        header("content-type:text/html;charset=utf-8");
        //文件路径
        $private_key = APP_PATH . 'qiniu.php';
        //
        $info = array();
        $info['BucketDomain'] = include($private_key);
        unset($info['BucketDomain']['AccessKey']);
        unset($info['BucketDomain']['SecretKey']);
        return $this->private_result('0001', $info);
    }

    //获取七牛TOKEN
    public function qiniu()
    {
        $qiniu = new Qiniu();
        $token = $qiniu->getToken();
        if ($token) {

            return $this->private_result('0001', array("token" => $token));
        } else {
            return $this->private_result('90001');
        }

    }

    //验证Token
    public function valiToken()
    {
        $token = get_token();
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        return $this->private_result('0001');
    }

    public function finance(){
        $token = get_token();
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }
        $finance = new Finance();
        $data = $finance->count($d_id);
        return $this->private_result(RESPONSE_SUCCESS,$data);
    }

    //账户明细
    public function getFinance(){
        $token = get_token();

        $page = get_post_value("page",1);
        $num = get_post_value("num",20);
        $type = get_post_value('type','');
        if (empty($token)|| empty($type)) {
            return $this->private_result("10001");
        }
        $validateResult = validate_regex($type, '/^(invite|gift|private|guidance|video|phone|image|all)$/');

        if(!$validateResult){
            return $this->private_result("10002");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $finance = new Finance();
        $list = $finance->select($d_id,'doctor',$type,$page,$num);
        if($list !== false){
            return $this->private_result(RESPONSE_SUCCESS,$list);
        }else{
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //收到心意
    public function gift(){
        $token = get_token();
        $page = get_post_value("page",1);
        $num = get_post_value("num",20);
        if (empty($token)) {
            return $this->private_result("10001");
        }
        $d_id = $this->UserModel->valiToken($token);
        if (!$d_id) {
            return $this->private_result('10003');
        }

        $list = $this->UserModel->getGift($d_id,$page,$num);
        if($list !== false){
            return $this->private_result(RESPONSE_SUCCESS,$list);
        }else{
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }
    /**
     * 创建一个邀请码
     * @return string
     */
    private function create_invite_code()
    {
        return substr(md5(microtime(1) . microtime(0)), 0, 6);
    }

    /**
     * 获得唯一验证码
     * @return string
     */
    private function get_unique_invite_code()
    {
        while (true) {
            $new_invite_code = $this->create_invite_code();
            $where['invite_code'] = $new_invite_code;
            $member = db('doctor', [], false)->where($where)->select();
            if (empty($member)) {
                return $new_invite_code;
            }
        }
        return false;
    }

//    public function temp(){
//        $easemob = new Easemob();
//        $token = $easemob->getToken();
//        return var_dump($easemob_account = $easemob->sendChatUser("yyb_doctor_35", "yyb_customer_37","这是服务器测试，收到请回答",array("test"=>"拓展拓展","msg"=>"拓展拓展拓展"),"txt",$token));
//    }
}
