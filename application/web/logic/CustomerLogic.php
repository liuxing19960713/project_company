<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/15/16
 * Time: 10:41
 */

namespace app\web\logic;

use app\common\model\Easemob;
use app\common\model\QingMaYun;
use app\web\model\Consultation;
use app\web\model\Coupon;
use app\web\model\CustomerCoupon;
use app\web\model\Oauth;
use think\Db;
use app\web\model\Customer;
use app\web\model\CustomerSession;

class CustomerLogic
{
    public function login($mobile, $password)
    {
        $field = [
            'c_id', 'nick_name', 'mobile_num', 'avatar',
            'easemob_username', 'easemob_password'
        ];
        $customer = new Customer();
        $result = $customer->field($field)->where('mobile_num', $mobile)->where('password', $password)->find();

        if (empty($result)) {
            return false;
        }

        return $result->visible()->toArray();
    }

    public function addSession($cid, $device = 'web', $channel = '')
    {
        $session = new CustomerSession();
        $info = $session->where("c_id", $cid)->find();

        $data = [
            "c_id" => $cid,
            "token" => md5($cid . time() . rand(0, 10000)),
            "device" => $device,
            "channel_id" => $device,
            "create_time" => time(),
            "expiry" => strtotime('+14 days'),
            'is_logout' => 'no',
        ];

        if (empty($info)) {
            // 新增
            $session->data($data);
            $result = $session->allowField(true)->save();
        } else {
            // 修改
            $result = $session->where("c_id", $cid)->update($data);
        }

        if ($result) {
            return $data;
        } else {
            return false;
        }

    }

    public function valiSession($token)
    {
        $session = new CustomerSession();
        $result = $session->where(['token' => $token, 'is_logout' => 'no'])->find();
        if (empty($result)) {
            return false;
        }

        // 验证有效期, 过期的话ban
        if ($result['expiry'] < time()) {
            $result->save(['is_logout' => 'yes']);
            return false;
        }

        // 再验一下id
        $cid = Customer::where('c_id', $result['c_id'])->value('c_id');
        if (empty($cid)) {
            return false;
        }

        return $cid;
    }

    public function disableSession($token)
    {
        $session = new CustomerSession();
        $result = $session->where('token', $token)->find();
        $result->save(['is_logout' => 'yes']);
    }

    public function updateInfo($cid, $data)
    {
        try {
            $customer = new Customer();
            $result = $customer->allowField(true)->save($data, ['c_id' => $cid]);
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMyCouponList($cid, $type, $did, $page, $num)
    {
        $field = 'cc.*, co.*, cc.create_time as create_time, do.real_name';
        $page = "{$page}, {$num}";
        $order = 'cc.create_time DESC';

        $coupon = new CustomerCoupon();
        $query = $coupon->alias('cc')->field($field)
            ->join('__COUPON__ co', 'co.co_id = cc.co_id')
            ->join('__DOCTOR__ do', 'cc.d_id = do.d_id', 'LEFT')
            ->where('cc.c_id', $cid);

        // 添加使用情况的筛选条件
        if ($type == 'no' || $type == 'yes') {
            $query = $query->where('cc.is_used', $type);
        }

        // 添加医生可用的筛选条件
        if (!empty($did)) {
            $query = $query->where('cc.d_id', $did);
        }

        $list = $query->order($order)->page($page)->select();

        $result = [];
        foreach ($list as $v) {
            $result[] = $v->toArray();
        }

        return $result;
    }

    public function oauthLogin($type, $oid, $name, $avatar)
    {
        $whereQuery = [
            'oauth_type' => $type,
            'oauth_id' => $oid,
        ];

        $cid = null;

        $oauth = new Oauth();
        $result = $oauth->where($whereQuery)->find();

        if (empty($result)) {
            // 创建新用户
            $cid = $this->addThirdPartyCustomer($name, $avatar);
            if (empty($cid)) {
                return false;
            }
            // 关联oauth表
            $oauthData = ['c_id' => $cid, 'oauth_id' => $oid, 'oauth_type' => $type];
            $oauth->data($oauthData);
            $res = $oauth->save();
        } else {
            // 获取用户id
            $cid = $result->c_id;
        }

        // create token
        $tokenData = $this->addSession($cid);
        return $tokenData;
    }

    public function getEaseMobAccount($cid, $withExtraInfo = false)
    {
        $customer = new Customer();
        $field ='easemob_username, easemob_password';
        if ($withExtraInfo) {
            $field .= ", nick_name, mobile_num, avatar";
        }
        $result = $customer->field($field)->where('c_id', $cid)->find();

        return $result->visible()->toArray();
    }

    public function getDetail($cid)
    {
        $field = [
            'c_id', 'nick_name', 'mobile_num', 'email', 'gender', 'birthday', 'age', 'blood_type', 'marriage', 'career','avatar',
            'money', 'easemob_username', 'easemob_password','is_push'
        ];
        $customer = new Customer();
        $whereQuery = [
            'c_id' => $cid
        ];

        $result = $customer->field($field)->where($whereQuery)->find();

        if (empty($result)) {
            return false;
        }
        $res = $result->visible()->toArray();

        $coupon = new CustomerCoupon();
        $couponNum = $coupon->where('c_id', $cid)->where('is_used', 'no')->count();
        $res['coupon_num'] = $couponNum . "";

        return $res;
    }

    public function addCustomerCoupon($cid, $did, $type)
    {
        if ($type == 'scan') {
            // 扫描优惠券
            $customerCoupon = new CustomerCoupon();
            $res = $customerCoupon->alias('cc')
                ->join("__COUPON__ co", "co.co_id = cc.co_id")
                ->where("co.type", "扫描")
                ->where("cc.c_id", $cid)
                ->where("cc.d_id", $did)
                ->find();
            if (empty($res)) {
                // 没有获取过, 直接添加
                $coupon = new Coupon();
                $couponInfo = $coupon->where('type', '扫描')->find();
                $data = [
                    "co_id" => $couponInfo->co_id,
                    "c_id" => $cid,
                    "d_id" => $did,
                ];
                $customerCoupon->data($data);
                $res = $customerCoupon->save();

                return $customerCoupon->toArray();

            } else {
                // 已经领取过, 不能再领取
                return "扫描优惠券已经领取过, 不能重复领取";
            }
        } else {
            // 分享优惠券 $type == 'share'
            $customerCoupon = new CustomerCoupon();
            $res = $customerCoupon->alias('cc')
                                  ->join("__COUPON__ co", "co.co_id = cc.co_id")
                                  ->where("co.type", "分享")
                                  ->where("cc.c_id", $cid)
                                  ->where("cc.d_id", $did)
                                  ->find();
            if (empty($res)) {
                // 没有获取过, 直接添加
                $coupon = new Coupon();
                $couponInfo = $coupon->where('type', '分享')->find();
                $data = [
                    "co_id" => $couponInfo->co_id,
                    "c_id" => $cid,
                    "d_id" => $did,
                ];
                $customerCoupon->data($data);
                $res = $customerCoupon->save();

                return $customerCoupon->toArray();

            } else {
                // 已经领取过, 不能再领取
                return "扫描优惠券已经领取过, 不能重复领取";
            }
        }
    }

    public function useCoupon($ccid, $cid, $did)
    {
        try {
            $updateData = [
                'is_used' => 'yes',
                'used_time' => time(),
            ];
            $whereStatement = [
                'c_id' => $cid,
                'cc_id' => $ccid,
            ];

            $customerCoupon = new CustomerCoupon();
            $target = $customerCoupon->where($whereStatement)->find();

            // todo: 判断已过期 (需要查coupon表)

            if (empty($target)) {
                // 该优惠券不存在
                return RESPONSE_FAIL_INVALID_COUPON;
            }

            if ($target->is_used == 'yes') {
                // 该优惠券已经被使用
                return RESPONSE_FAIL_INVALID_COUPON;
            }

            if ($target->d_id != $did && $target->d_id != '0') {
                // 对该医生不可用
                return RESPONSE_FAIL_INVALID_COUPON;
            }

            $customerCoupon->save($updateData, $whereStatement);
            return $customerCoupon->toArray();

        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }

    }

    public function addImageConsultation($cid, $did, $money ,$is_extend = 'no')
    {
        try {
            $consultation = new Consultation();
            $consultationData = [
                'c_id' => $cid,
                'd_id' => $did,
                'money' => $money,
                'state' => '进行中',
                'type' => '图文咨询',
                'is_extend' => $is_extend
            ];
            $consultation->data($consultationData);
            $consultation->save();

            return $consultation->toArray();

        } catch (\Exception $e) {
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function addOtherConsultation($cid, $did, $money ,$type, $during, $apId, $appointTime, $cpId, $seId)
    {
        try {
            $consulation = new Consultation();
            $consulationData = [
                'c_id' => $cid,
                'd_id' => $did,
                'money' => $money,
                'state' => '未进行',
                'type' => $type,
                'is_extend' => 'no',
                'total_time' => $during * 60,
                'valid_time' => $during * 60,
                'appoint_time' => $appointTime,
                'ap_id' => $apId,
                'cp_id' => $cpId,
                'service_id' => $seId,
            ];
            $consulation->data($consulationData);
            $consulation->save();

            return $consulation->toArray();

        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function addServiceConsultation($cid, $did, $money ,$type, $during, $se_id, $serviceEndTime, $cpId)
    {
        try {
            $consulation = new Consultation();
            $consulationData = [
                'c_id' => $cid,
                'd_id' => $did,
                'money' => $money,
                'state' => '进行中',
                'type' => $type,
                'is_extend' => 'no',
                'service_id' => $se_id,
                'total_time' => $during*24*60*60,
                'valid_time' => $during*24*60*60,
                'service_endtime' => $serviceEndTime,
                'cp_id' => $cpId,
            ];
            $consulation->data($consulationData);
            $consulation->save();

            return $consulation->toArray();

        } catch (\Exception $e) {
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function getInfoByMobile($mobile)
    {
        $field = [
            'c_id', 'nick_name', 'mobile_num', 'email', 'gender', 'birthday', 'age', 'blood_type', 'marriage', 'career','avatar',
            'easemob_username', 'easemob_password', 'reg_code', 'pass_code',
        ];
        try{
            $customer = new Customer();
            $whereQuery = [
                'mobile_num' => $mobile,
            ];

            $result = $customer->field($field)->where($whereQuery)->find();

            if (empty($result)) {
                return false;
            }
            return $result->visible()->toArray();

        } catch (\Exception $e) {
            return RESPONSE_FAIL_SQL_ERROR;
        }

    }

    public function saveCode($mobile, $code, $codeType, $isUpdate = false)
    {
        // 写得很烂
        $dataArray = [
            "mobile_num" => $mobile,
        ];
        switch ($codeType) {
            case "reg":
                $dataArray['reg_code'] = $code . "|" . time() . "|0";
                break;
            case "pass":
                $dataArray['pass_code'] = $code . "|" . time() . "|0";
                break;
        }

        try {
            $customer = new Customer();
            if ($isUpdate) {
                $result = $customer->where("mobile_num", $mobile)->update($dataArray);
            } else {
                $result = $customer->data($dataArray)->save();
            }

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function validateCode($mobile, $code, $codeType)
    {
        $whereStatement = [
            'mobile_num' => $mobile,
        ];
        $customer = new Customer();
        $info = $customer->where($whereStatement)->find();

        if (empty($info->c_id)) {
            return RESPONSE_FAIL_ACCOUNT_NOT_FOUND;
        }

        $codeArray = [];
        $codeField = '';
        switch ($codeType) {
            case "reg":
                $codeField = 'reg_code';
                $codeArray = explode("|", $info->reg_code);
                break;
            case "pass":
                $codeField = 'pass_code';
                $codeArray = explode("|", $info->pass_code);
                break;
        }

        if (!is_array($codeArray) || count($codeArray) <= 1) {
            return RESPONSE_FAIL_SMS_VALIDATE_FAIL;
        }

        // 验次数
        if (intval($codeArray[2]) >= 10) {
            return RESPONSE_FAIL_SMS_VALIDATE_TOO_MUCH;
        }

        if ((time() - $codeArray[1]) >= 600) {
            return RESPONSE_FAIL_SMS_CODE_EXPIRED;
        }

        if ($code != $codeArray[0]) {
            $codeArray[2] = intval($codeArray[2]) + 1;
            $codeData = implode("|", $codeArray);
            try {
                $customer->where($whereStatement)->update([$codeField => $codeData]);
            } catch (\Exception $e) {
                return RESPONSE_FAIL_SQL_ERROR;
            }
            return RESPONSE_FAIL_SMS_CODE_WRONG;
        }

        return true;
    }

    public function finishRegister($mobile, $password)
    {
        $whereStatement = [
            "mobile_num" => $mobile
        ];

        $updateData = [
            'password' => md5($password),
            'reg_code' => 1,
        ];

        try {
            $customer = new Customer();
            $info = $customer->field('c_id')->where("mobile_num", $mobile)->find();

            if (empty($info)) {
                return false;
            }

            $info = $info->toArray();

            // 环信注册
            $easeMobAccount = $this->addEaseMobAccount($info['c_id']);
            if (!empty($easeMobAccount)) {
                $updateData['easemob_username'] = $easeMobAccount['username'];
                $updateData['easemob_password'] = $easeMobAccount['password'];
            } else {
                // 注册失败
                return false;
            }

            // 轻码云注册
            $resultArray = $this->addQmyAccount($mobile);
            if ($resultArray === false) {
                return false;
            }
            $updateData['qmy_client'] = $resultArray['account'];
            $updateData['qmy_password'] = $resultArray['password'];

            $customer = new Customer();
            $result = $customer->where($whereStatement)->update($updateData);

            if (empty($result)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ex_log($e, 'e');
            return false;
        }
    }

    public function checkPassword($cid, $password)
    {
        $whereStatement = [
            "c_id" => $cid,
            "password" => md5($password)
        ];

        try {
            $customer = new Customer();
            $result = $customer->field(['c_id'])->where($whereStatement)->find();

            if (empty($result)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changePassword($password, $cid = null, $mobile = null)
    {
        $whereStatement = [];
        if (!empty($cid)) {
            $whereStatement['c_id'] = $cid;
        }
        if (!empty($mobile)) {
            $whereStatement['mobile_num'] = $mobile;
        }
        if (empty($whereStatement)) {
            return false;
        }

        $updateData = [
            'password' => md5($password),
            'pass_code' => '-1',
        ];

        try {
            $customer = new Customer();
            $result = $customer->where($whereStatement)->update($updateData);

            if (empty($result)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function addQmyAccount($mobile)
    {
        $configData = config('qingmayun');
        $accountSid = $configData['account_sid'];
        $authToken = $configData['auth_token'];
        $appId = $configData['app_id'];
        $handle = new QingMaYun($accountSid, $authToken, $appId);

        $name = 'yyb_' . $mobile ;
        $resultArray = $handle->createAccount($mobile, $name);

        if ($resultArray === false) {
            $errorCodes = $handle->getErrCode();
            $errorMessage = $handle->getErrMsg();
            $handle->clearError();
            every_log('reg', $errorMessage, 'qmy');
            return false;
        }
        return $resultArray;
    }

    public function updateMoney($cid, $money)
    {
        try {
            if (empty($money)) {
                throw new \Exception("money can not be empty", '9999');
            }
            $customer = new Customer();
            $updateMoneyResult = $customer->where('c_id', $cid)->setInc('money', $money);
            if ($updateMoneyResult === false) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    private function addEaseMobAccount($cid)
    {
        $easemob = new Easemob();
        $token = $easemob->getToken();
        $easemobAccount = $easemob->regChatUser($token, $cid, 'customer');
        return $easemobAccount;
    }

    private function addThirdPartyCustomer($name, $avatar)
    {
        $customerData = [
            'nick_name' => $name,
            'avatar' => $avatar
        ];

        Db::startTrans();
        $customer = new Customer();
        $customer->data($customerData);
        $customer->save();
        $cid = $customer->c_id;

        if (empty($cid)) {
            Db::rollback();
            return false;
        }

        $easemobAccount = $this->addEaseMobAccount($cid);
        if (empty($easemobAccount)) {
            Db::rollback();
            return false;
        }

        $updateData = [
            'easemob_username' => $easemobAccount['username'],
            'easemob_password' => $easemobAccount['password']
        ];
        $customer->save($updateData);

        Db::commit();

        return $cid;
    }

}
