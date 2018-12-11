<?php

/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/15/16
 * Time: 10:52
 */

namespace app\web\controller;

use app\doctor\model\Consultation;
use app\index\controller\Base;
use app\index\model\Finance;
use app\index\validate\Vali;
use app\web\logic\CustomerLogic;
use app\web\logic\DoctorLogic;
use app\web\logic\FollowLogic;
use app\index\model\Message;
use app\web\logic\OrderLogic;
use think\Db;
use think\Exception;

class Customer extends Base
{
    //账户明细
    // 测试
    public function getFinance(){
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        $page = get_post_value("page",1);
        $num = get_post_value("num",20);
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }
        $finance = new Finance();
        $list = $finance->select($cid,'customer','',$page,$num);
        if($list !== false){
            return $this->private_result(RESPONSE_SUCCESS,$list);
        }else{
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    public function findPassword()
    {
        $mobile = get_post_value('mobile', '');
        $code = get_post_value('code', '');
        $password = get_post_value('password', null);

        $validateResult = validate_number($mobile) && validate_is($code, 'alphaNum');
        if (!$validateResult) {
            if (empty($mobile) || empty($code)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $customer = new CustomerLogic();
        $checkResult = $customer->validateCode($mobile, $code, 'pass');

        if ($checkResult !== true && in_array($checkResult, $this->errKeys)) {
            return $this->private_result($checkResult);
        }

        if ($password == null) {
            // 只验证code
            return $this->private_result(RESPONSE_SUCCESS);
        }

        // 完成注册
        // todo: 验密码规则
        $customer = new CustomerLogic();
        $result = $customer->changePassword($password, null, $mobile);

        if ($result) {
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            return $this->private_result(RESPONSE_FAIL_REGISTER_FAIL);
        }
    }

    public function changePassword()
    {
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $oldPassword = get_post_value('old_password', null);
        $newPassword = get_post_value('new_password', null);

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        if ($oldPassword == $newPassword) {
            return $this->private_result(RESPONSE_FAIL_SAME_PASSWORD);
        }

        // 验旧密码
        $customer = new CustomerLogic();
        $checkResult = $customer->checkPassword($cid, $oldPassword);

        if (!$checkResult) {
            return $this->private_result(RESPONSE_FAIL_INVALID_PASSWORD);
        }

        // 换新密码
        $updateResult = $customer->changePassword($newPassword, $cid);

        if ($updateResult) {
            return $this->private_result(RESPONSE_SUCCESS);
        } else {
            return $this->private_result(RESPONSE_FAIL_CHANGE_PASSWORD_FAIL);
        }
    }

    public function oauthLogin()
    {
        $device = get_device();
        $channel = get_post_value('channel_id', '');
        $oauthType = get_post_value('oauth_type');
        $oauthId = safe_str(get_post_value('oauth_id'));
        $name = safe_str(get_post_value('username', ''));
        $avatar = get_post_value('avatar', '');

        $validateResult = validate_number($channel) &&
                          validate_regex($device, '/^(web|ios|android)$/') &&
                          validate_words($oauthType, ['wechat', 'qq']) &&
                          validate_regex($avatar, '/^[a-zA-Z0-9_:\/\.-]*$/');
        if (!$validateResult) {
            if (empty($oauthType) || empty($oauthId) || empty($device) || empty($channel)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            }
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $customer = new CustomerLogic();
        $sessionInfo = $customer->oauthLogin($oauthType, $oauthId, $name, $avatar);

        if (empty($sessionInfo['token'])) {
            return $this->private_result(RESPONSE_FAIL_LOGIN_FAIL);
        }
        
        $easeMobInfo = $customer->getEaseMobAccount($sessionInfo['c_id'], true);
        if ($easeMobInfo) {
            $sessionInfo = array_merge($sessionInfo, $easeMobInfo);
        }

        return $this->private_result(RESPONSE_SUCCESS, $sessionInfo);
    }

    public function logout()
    {
        $token = get_token();
        $customer = new CustomerLogic();
        $customer->disableSession($token);
        return $this->private_result(RESPONSE_SUCCESS);
    }

    public function update()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $keys = ['nick_name', 'email', 'gender', 'birthday', 'age', 'blood_type', 'marriage', 'career', 'avatar'];
        $keysForValidate = [];
        $data = [];
        foreach ($keys as $key) {
            if (isset($_POST[$key]) && !empty($_POST[$key])) {
                $data[$key] = $_POST[$key];
                $keysForValidate[] = $key;
            }
        }
        if (empty($data)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }
        $validate = new Vali();
        $validate->scene('update', $keysForValidate);
        $validateResult = $validate->scene('update')->check($data);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        // 进行更新
        $customer = new CustomerLogic();
        $result = $customer->updateInfo($cid, $data);

        if ($result === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result(RESPONSE_SUCCESS);
    }

    public function getInfo()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 进行更新
        $customer = new CustomerLogic();
        $result = $customer->getDetail($cid);

        if (!$result) {
            return $this->private_result(RESPONSE_FAIL_CONSULTATION_NOT_FOUND);
        }

        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    /**
     * 用户关注医生
     *
     * @return \think\response\Json
     */
    public function follow()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', '');
        $validateResult = validate_number($did);
        if (!$validateResult) {
            if (empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // 验证医生id有效性
        $doctor = new DoctorLogic();
        $did = $doctor->validateId($did);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

        // 添加关注
        $follow = new FollowLogic();
        $res = $follow->addFollow($cid, $did);
        $message = new Message();

        $customer = new CustomerLogic();
        $result = $customer->getDetail($cid);

        $message->publicPush($did, "customer", "患者" . $result['nick_name'] . "关注了您", "", "", "用户关注");//推送信息
        return $this->private_result(RESPONSE_SUCCESS, $res);
    }

    /**
     * 用户取消关注医生
     *
     * @return \think\response\Json
     */
    public function quitFollow()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', '');
        $validateResult = validate_number($did);
        if (!$validateResult) {
            if (empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // 验证医生id有效性
        $doctor = new DoctorLogic();
        $did = $doctor->validateId($did);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

        // 取消关注
        $follow = new FollowLogic();
        $res = $follow->deleteFollow($cid, $did);
        return $this->private_result(RESPONSE_SUCCESS);
    }
    /**
     * 获取用户关注列表
     *
     * @return \think\response\Json
     */
    public function getFollowList()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');
        $validateResult = validate_number($page) && validate_number($num);
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        // 获取关注列表
        $follow = new FollowLogic();
        $list = $follow->getList($cid, $page, $num);
        return $this->private_result(RESPONSE_SUCCESS, $list);
    }

    public function getCouponList()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $used = get_post_value('used', 'all');
        $did = get_post_value('did', '');
        $page = get_post_value('page', '1');
        $num = get_post_value('num', '20');
        $validateResult = validate_number($page) &&
            validate_number($num) &&
            validate_regex($used, '/^(no|yes|all)$/') &&
            (empty($did) || validate_number($did));
        if (!$validateResult) {
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        // 查看自己的优惠券列表
        $customer = new CustomerLogic();
        $list = $customer->getMyCouponList($cid, $used, $did, $page, $num);
        return $this->private_result(RESPONSE_SUCCESS, $list);
    }

    public function drawCoupon()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', '');
        $type = get_post_value('type', 'scan');
        $validateResult = validate_number($did) && validate_regex($type, '/^(scan|share)$/');
        if (!$validateResult) {
            if (empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // 验证医生id有效性
        $doctor = new DoctorLogic();
        $did = $doctor->validateId($did);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

        // 添加关注
        $follow = new FollowLogic();
        $follow->addFollow($cid, $did);

        // 获取优惠券
        $customer = new CustomerLogic();
        $res = $customer->addCustomerCoupon($cid, $did, $type);

        if (!is_array($res)) {
            return $this->private_result('40008', $res);
        }

        return $this->private_result(RESPONSE_SUCCESS, $res);
    }

    public function checkFollow()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $did = get_post_value('did', '');
        $validateResult = validate_number($did);
        if (!$validateResult) {
            if (empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // 验证医生id有效性
        $doctor = new DoctorLogic();
        $did = $doctor->validateId($did);
        if (!$did) {
            return $this->private_result(RESPONSE_FAIL_RESOURCE_NOT_FOUND);
        }

        // 检查关注状态和优惠券获取状态
        $follow = new FollowLogic();
        $res = $follow->checkFollow($cid, $did);

        return $this->private_result(RESPONSE_SUCCESS, $res);
    }

    public function useCoupon()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $customerCouponId = get_post_value('ccid', '');
        $did = get_post_value('did', '');
        $validateResult = validate_number($customerCouponId) && validate_number($did);
        if (!$validateResult) {
            if (empty($validateResult) || empty($did)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        // todo: 检查 医生id有效性, 优惠券是否过期, 优惠券是否已经使用, 是否已经结束上次问诊
        // 消费优惠券
        trans_start();
        $customer = new CustomerLogic();
        $result = $customer->useCoupon($customerCouponId, $cid, $did);
        if (in_array($result, $this->errKeys)) {
            trans_rollback();
            return $this->private_result($result);
        }

        $result2 = $customer->addImageConsultation($cid, $did, '0');
        if (in_array($result2, $this->errKeys)) {
            trans_rollback();
            return $this->private_result($result2);
        }

        trans_commit();

        return $this->private_result(RESPONSE_SUCCESS, array_merge($result, $result2));
    }

    //修改推送状态
    public function setPush()
    {
        $token = get_token();
        $is_push = get_post_value('is_push');
        if (empty($token) || empty($is_push)) {
            return $this->private_result('10001');
        }
        $validate = validate_regex($is_push, '/^(yes|no)$/');
        if (!$validate) {
            return $this->private_result('10002');
        }
        $customerLogic = new CustomerLogic();
        $is_token = $customerLogic->valiSession($token);
        if (!$is_token) {
            return $this->private_result('10003');
        }
        $where['c_id'] = $is_token;
        $update['is_push'] = $is_push;
        try {
            $save = Db::name("customer")->where($where)->update($update);
        } catch (\Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result('0001');
    }

    //获取我的医生（关注列表）
    public function getMyDoctor(){
        $token = get_token();
        $page = get_post_value('page',1);
        $num = get_post_value('num',20);
        if (empty($token)) {
            return $this->private_result('10001');
        }
        $customerLogic = new CustomerLogic();
        $c_id = $customerLogic->valiSession($token);
        if (!$c_id) {
            return $this->private_result('10003');
        }
        try {
            $where = "fo.c_id = ".$c_id." AND dp.is_default = 'yes'";
            $field = "d.d_id as d_id,d.real_name as real_name,d.avatar,dp.hospital as hospital,dp.department1 as department1,dp.department2 as department2,d.title as title";
            $list = Db::name("follow")->alias('fo')->join('yyb_doctor d', 'd.d_id =fo.d_id')->join('yyb_department dp', 'd.d_id =dp.d_id')->field($field)->where($where)->order('fo.create_time DESC')->page($page,$num)->select();
        } catch (\Exception $e) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result(RESPONSE_SUCCESS,$list);
    }
    public function reportList()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $page = get_post_value('page', 1);
        $num = get_post_value('num', 20);
        $validateResult = validate_number($page) && validate_number($num);
        if (!$validateResult) {
            if (empty($validateResult)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        $Consultation = new Consultation();
        $result = $Consultation->getList2("con.c_id = " . $cid, '', $page, $num);
        if ($result === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result(RESPONSE_SUCCESS, $result);
    }

    public function charge()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $money = get_post_value('money');
        $payType = get_post_value('pay_type');

        $validateResult = validate_number($money) &&
                          validate_words($payType, ['alipay', 'wechat']);
        if (!$validateResult) {
            if (empty($money)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        $order = new OrderLogic();
        $result = $order->addOrder($cid, '0', 'charge', $payType, $money, '');

        if ($result === false) {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
        return $this->private_result(RESPONSE_SUCCESS,$result);
    }

    public function gift()
    {
        // 验token并返回id
        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        // 获取参数, 并进行验证
        $payType = get_post_value('pay_type');
        $money = get_post_value('money');
        $did = get_post_value('did');
        $title = safe_str(get_post_value('title', '妙手仁心'));
        $content = safe_str(get_post_value('content'));

        $titles = ['一点心意', '白衣天使', '医德高尚', '德医双馨', '妙手仁心'];

        $validateResult = validate_number($money) &&
                          validate_number($did) &&
                          validate_words($title, $titles);
                          validate_words($payType, ['balance', 'alipay', 'wechat']);
        if (!$validateResult) {
            if (empty($money) || empty($did) || empty($payType)) {
                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
            } else {
                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
            }
        }

        trans_start();

        $extraData = [
            'title' => $title,
            'content' => $content,
            'username' => $this->getNickName($cid),
        ];
        $order = new OrderLogic();
        $orderData = $order->addOrder($cid, $did, 'gift', $payType, $money, $extraData);

        if ($orderData === false || empty($orderData['or_id'])) {
            trans_rollback();
            return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "新建订单失败");
        }

        if ($payType == 'balance') {
            // 余额支付 处理状态 返回数据
            $completeData = $order->completeOrder($orderData['or_id'], $cid);
            if ($completeData === false) {
                trans_rollback();
                return $this->private_result(RESPONSE_FAIL_INTERNAL_ERROR, "完成订单失败");
            }
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, array_merge($orderData, $completeData));

        } else {
            // 充值支付 返回数据
            trans_commit();
            return $this->private_result(RESPONSE_SUCCESS, $orderData);
        }
    }

    /**
     *  查 医生是否是 私人医生/院后指导
     */
    public function checkDoctorService(){
        $did = get_post_value('did', '');

        if (empty($cid) || empty($did)) {
            return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        }

        $validateResult = validate_number("d_id");
        if(!$validateResult){
            return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
        }

        $cid = $this->checkTokenAndGetCid();
        if (!$cid) {
            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
        }

        $consultation = new Consultation();
        $res = $consultation->checkServiceDoctor($cid,$did);

        if ($res !== RESPONSE_FAIL_SQL_ERROR) {
            return $this->private_result(RESPONSE_SUCCESS, $res);
        } else {
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }
    //自测工具 列表
    public function discover(){
        try{
            $list = Db::name('discover')->field('content',true)->order('weight DESC')->select();
            $domain = 'http://test.dankal.cn/yyhcms/data/upload/';
            foreach ($list as $key => $item) {
                if (isset($item['thumb']) && !empty($item['thumb'])) {
                    $list[$key]['thumb'] = $domain . $item['thumb'];
                }
            }
            return $this->private_result(RESPONSE_SUCCESS,$list);
        }catch (Exception $e){
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //自测工具 详情
    public function discoverInfo(){
        $dis_id = get_post_value("dis_id");
        if(!$dis_id){
            return $this->private_result('10001');
        }
        $validateResult = validate_number($dis_id);
        if(!$validateResult){
            return $this->private_result('10002');
        }
        try{
            $info = Db::name('discover')->where("dis_id = {$dis_id}")->find();
            $domain = 'http://test.dankal.cn/yyhcms/data/upload/';
            $info['thumb'] = $domain . $info['thumb'];
            return $this->private_result(RESPONSE_SUCCESS,$info);
        }catch (Exception $e){
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    //自测工具 添加参与次数
    public function addDiscoverCount(){
        $dis_id = get_post_value("dis_id");
        if(!$dis_id){
            return $this->private_result('10001');
        }
        $validateResult = validate_number($dis_id);
        if(!$validateResult){
            return $this->private_result('10002');
        }
        try{
            $add = Db::name('discover')->where("dis_id = {$dis_id}")->setInc('count',1);
            return $this->private_result(RESPONSE_SUCCESS);
        }catch (Exception $e){
            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
        }
    }

    private function getNickName($cid)
    {
        $username = '优医惠用户';
        try {
            $userInfo = \app\web\model\Customer::field(['c_id', 'nick_name', 'mobile_num'])->where('c_id', $cid)->find()->toArray();
            $username = $userInfo['nick_name'];
            if (empty_without_zero($username)) {
                if (strlen($userInfo['mobile_num']) == 11) {
                    $username = $userInfo['mobile_num'];
                    $username[3] = $username[4] = $username[5] = $username[6] = '*';
                }
            }
            if (empty($username)) {
                $username = '微信用户';
            }
        } catch (\Exception $e) {
            $username = '优医惠用户';
        }
        return $username;
    }

    private function checkTokenAndGetCid()
    {
        $token = get_token();
        $customer = new CustomerLogic();
        $cid = $customer->valiSession($token);
        return $cid;
    }

}
