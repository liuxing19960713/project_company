<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 11/25/16
 * Time: 10:29
 */

namespace app\web\logic;

use app\web\model\Appointment;
use app\web\model\Consultation;
use app\web\model\ConsultationProfile;
use app\web\model\Customer;
use app\web\model\CustomerCoupon;
use app\web\model\Doctor;
use app\web\model\ExConsultation;
use app\web\model\Refund;
use app\web\model\Service;
use app\web\model\Timeline;
use think\Db;

class ConsultationLogic
{
    public function getRecord($cid, $tab = 'current', $type = 'all', $page = '1', $num = '20')
    {
        $page = "{$page}, {$num}";
        $order = 'con.create_time DESC';
        $field = [
            'con.con_id', 'con.c_id', 'con.d_id', 'con.state', 'con.type', 'con.create_time', 'con.appoint_time',
            'doc.real_name', 'doc.avatar', 'doc.easemob_username', 'doc.gender as doc_gender',
            'cp.content', 'cp.department', 'cp.name', 'cp.gender'
        ];
        $whereStatement = [
            "con.c_id" => $cid,
        ];

        switch ($tab) {
            case "current":
                $whereStatement['con.state'] = ['exp', "REGEXP '未进行|进行中'"];
                break;
            case "history":
                $whereStatement['con.state'] = '已完成';
                break;
            default:
                return RESPONSE_FAIL_INTERNAL_ERROR;
                break;
        }


        switch ($type) {
            case "all":
                break;
            case "image":
                $whereStatement['con.type'] = '图文咨询';
                break;
            case "video":
                $whereStatement['con.type'] = '视频咨询';
                break;
            case "phone":
                $whereStatement['con.type'] = '电话咨询';
                break;
            default:
                return RESPONSE_FAIL_INTERNAL_ERROR;
                break;
        }

        $consultation = new Consultation();
        $list = $consultation->alias('con')
                ->field($field)
                ->join("__DOCTOR__ doc", "doc.d_id = con.d_id")
                ->join("__CONSULTATION_PROFILE__ cp", "cp.con_id = con.con_id", "LEFT")
                ->where($whereStatement)
                ->order($order)->page($page) ->select(); // $list 为 Query 对象的数组

        $result = [];
        foreach ($list as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    public function getSuperRecord($cid, $tab = 'current', $type = '全部服务', $page = '1', $num = '20')
    {
        try {
            $offset = ($page - 1) * $num;

            $extraField = ", `cp`.`content`, `cp`.`department`, `do`.`avatar`, `do`.`real_name`, `do`.`easemob_username`, `se`.`type` as `se_type` ";
            $extraField .= ", `de`.`hospital`, `do`.`title`";

            if ($tab == 'current') {
                $state = '未进行|进行中';
                $status = 'wait';
                $typeFilterA = " AND (`service_id` = '0' OR (`service_id` != '0' AND (`type` = '图文咨询'))) ";
                $typeFilterB = "";
            } else {
                $state = '已完成|已取消';
                $status = 'no';
                $typeFilterA = $typeFilterB = ($type == '全部服务') ? "" : " AND `type` = '{$type}' ";

                if ($type = "全部服务") {

                } else if ($type == "私人医生" || $type == '院后指导') {
                    $typeFilterA = " AND `type` = '图文咨询' AND `service_id` != '0'";
                } else {
                    $typeFilterA .= " AND `service_id` = '0'";
                }
            }

            $queryStatement = "
SELECT `T1`.* {$extraField} FROM (
    SELECT 'con' as `x`, `con_id` as `xid`, `c_id`, `d_id`, `cp_id`, `type`, `create_time`, `state` as `status`, `total_time` as `during_time`, `valid_time` as `valid_time`, `appoint_time` as `appoint_time`, `service_endtime` as `end_time`, `service_id` as `se_id`, `money`, '' as `reason`, `comment_time` FROM `yyb_consultation` WHERE `c_id` = '{$cid}' AND `state` REGEXP '{$state}' {$typeFilterA}
    UNION
    SELECT 'ap' as `x`, `ap_id` as `xid`,`c_id`, `d_id`, `cp_id`, `type`, `create_time`, `status`, `during` * 60 as `during_time`, `during` * 60 as `valid_time`, `appoint_time` as `appoint_time`, '0' as `end_time`, '0' as `se_id`, `price` as `money`, `reason`, '0' as `comment_time` FROM `yyb_appointment` WHERE `c_id` = '{$cid}' AND `status` = '{$status}' {$typeFilterB}
    UNION
    SELECT 'se' as `x`, `se_id` as `xid`,`c_id`, `d_id`, `cp_id`, `type`, `create_time`, `status`, `during` as `during_time`, `during` as `valid_time`, '0' as `appoint_time`, '0' as `end_time`, '0' as `se_id`, `money`, `reason`, '0' as `comment_time` FROM `yyb_service` WHERE `c_id` = '{$cid}' AND `status` = '{$status}' {$typeFilterB}
) AS `T1` 
LEFT JOIN `yyb_consultation_profile` AS `cp` ON T1.`cp_id` = `cp`.`cp_id` 
LEFT JOIN `yyb_doctor` AS `do` ON `T1`.`d_id` = `do`.`d_id`
LEFT JOIN `yyb_customer` AS `cu` ON `T1`.`c_id` = `cu`.`c_id`
LEFT JOIN `yyb_service` AS `se` ON `T1`.`se_id` = `se`.`se_id`
LEFT JOIN `yyb_department` AS `de` ON `T1`.`d_id` = `de`.`d_id` AND `de`.`is_default` = 'yes' AND `de`.`is_audited` = 'yes'
ORDER BY `create_time` DESC LIMIT {$offset}, {$num}";
            $res = Db::query($queryStatement);
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }

    }

    public function getServiceRecord($seId, $type, $page = '1', $num = '20')
    {
        try {
            $offset = ($page - 1) * $num;

            if ($type == "电话咨询" || $type == '视频咨询') {
                $filterA = " `type` = '{$type}' AND `service_id` = '{$seId}'";
                $filterB = " `type` = '{$type}' AND `se_id` = '{$seId}' AND (`status` = 'wait' OR `status` = 'no') ";
            } else {
                throw new \Exception("invalid type: " . var_export($type, true));
            }

            $extraField = ",`cp`.`name`, `cp`.`gender`, `cp`.`age`, `cp`.`content`, `cp`.`department`, `do`.`avatar` as `doctor_avatar`, `do`.`real_name`, `do`.`easemob_username` as `doctor_easemob`, `cu`.`avatar` as `customer_avatar`, `cu`.`easemob_username` as `customer_easemob` ";
            $extraField .= ", `apo`.`status` as `status2`";
            $queryStatement = "
SELECT `T1`.* {$extraField} FROM (
    SELECT 'con' as `x`, `con_id` as `xid`, `c_id`, `d_id`, `cp_id`, `type`, `create_time`, `state` as `status`, `total_time` as `during_time`, `valid_time` as `valid_time`, `appoint_time` as `appoint_time`, `service_endtime` as `end_time`, `service_id` as `se_id`, `money`, '' as `reason`, `ap_id` FROM `yyb_consultation` WHERE {$filterA}
    UNION
    SELECT 'ap' as `x`, `ap_id` as `xid`,`c_id`, `d_id`, `cp_id`, `type`, `create_time`, `status`, `during` * 60 as `during_time`, `during` * 60 as `valid_time`, `appoint_time` as `appoint_time`, '0' as `end_time`, `se_id`, `price` as `money`, `reason`, `ap_id` as `ap_id` FROM `yyb_appointment` WHERE {$filterB}
) AS `T1` 
LEFT JOIN `yyb_consultation_profile` AS `cp` ON T1.`cp_id` = `cp`.`cp_id` 
LEFT JOIN `yyb_doctor` AS `do` ON `T1`.`d_id` = `do`.`d_id`
LEFT JOIN `yyb_customer` AS `cu` ON `T1`.`c_id` = `cu`.`c_id`
LEFT JOIN `yyb_appointment` AS `apo` ON `T1`.`ap_id` = `apo`.`ap_id`
ORDER BY `create_time` DESC LIMIT {$offset}, {$num}";
            $res = Db::query($queryStatement);
            return $res;

        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function getAppointStatus($cid, $apId)
    {
        try {
            $field = [
                'ap.ap_id', 'ap.cp_id', 'ap.price as money', 'ap.status', 'ap.create_time', 'ap.appoint_time', 'ap.during', 'ap.reason',
                'con.con_id'
            ];
            $whereState = [
                'ap.c_id' => $cid,
                'ap.ap_id' => $apId,
            ];
            $appoint = new Appointment();
            $res = $appoint->alias('ap')->field($field)
                ->join("__CONSULTATION__ con", "ap.ap_id = con.ap_id")
                ->where($whereState)->find();
            if (empty($res)) {
                return [];
            }
            return $res->toArray();
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getServiceStatus($cid, $seId)
    {
        try {
            $field = [
                'se.se_id', 'se.cp_id', 'se.money', 'se.status', 'se.create_time', 'se.during', 'se.reason',
                'con.con_id'
            ];
            $whereState = [
                'se.c_id' => $cid,
                'se.se_id' => $seId,
            ];
            $sepoint = new Service();
            $res = $sepoint->alias('se')->field($field)
                           ->join("__CONSULTATION__ con", "se.se_id = con.service_id AND con.`type` = '图文咨询'", "LEFT")
                           ->where($whereState)->find();
            if (empty($res)) {
                return [];
            }
            return $res->toArray();
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }


    public function checkRecord($cid, $did, $type)
    {
        $field = [
            'con.con_id', 'con.c_id', 'con.d_id', 'con.state', 'con.type',
        ];
        $whereStatement = [
            "con.c_id" => $cid,
            "con.d_id" => $did,
            "con.type" => $type,
        ];
        $order = 'con.create_time DESC';

        $consultation = new Consultation();
        $result = $consultation->alias('con')
            ->field($field)
            ->where($whereStatement)
            ->order($order)
            ->find();
        return $result;
    }

    public function comment($cid, $conId, $data)
    {
        $consultation = new Consultation();
        $con = $consultation->where(['con_id' => $conId, 'c_id' => $cid])->find();

        if (empty($con)) {
            return RESPONSE_FAIL_CONSULTATION_NOT_FOUND;
        }

        if ($con->state != '已完成') {
            return RESPONSE_FAIL_CONSULTATION_UNFINISHED;
        }

        if (!empty($con->comment_time)) {
            return RESPONSE_FAIL_ALREADY_COMMENT;
        }

        $data['comment_time'] = time();
        $result = $consultation->allowField(true)->save($data, ['con_id' => $conId]);

        if (empty($result)) {
            return RESPONSE_FAIL_SQL_ERROR;
        }
        return $result;
    }

    public function getAppointmentList($timeFrom, $timeTo, $type)
    {
        $typeStr = ['phone' => '电话咨询', 'video' => '视频咨询'];
        $field = [
            'con.con_id', 'con.c_id', 'con.d_id', 'con.appoint_time', 'con.call_data', 'con.type', 'con.valid_time', 'con.total_time', 'con.ap_id',
            'do.real_name', 'do.mobile_num', 'do.easemob_username as do_ease', 'do.phone_price', 'do.video_price',
            'cu.qmy_client', 'cu.easemob_username as cu_ease',
            'cp.name',
        ];
        $consultation = new Consultation();
        $list = $consultation->alias('con')->field($field)
                             ->join("__DOCTOR__ do", 'con.d_id = do.d_id')
                             ->join("__CUSTOMER__ cu", 'con.c_id = cu.c_id')
                             ->join("__CONSULTATION_PROFILE__ cp", 'con.con_id = cp.con_id');
        if ($type != 'all') {
            $list = $list->where('con.type', $typeStr[$type]);
        }
        $list = $list->where('con.appoint_time', 'between', [$timeFrom, $timeTo])
                     ->where('con.state', 'neq', "已取消")
                     ->where('con.is_refunded', 'no')->select();
        $res = array();
        foreach ($list as $item) {
            $res[] = $item->toArray();
        }
        return $res;
    }

    public function updateEndedAppoint($apid)
    {
        try {
            $appoint = new Appointment();
            $res = $appoint->where('ap_id', $apid)->update(['status' => 'end']);
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function updateEndedServices($timeStamp)
    {
        try {
            $service = new Service();
            $res = $service->where('status', 'yes')->where('end_time', '<=', $timeStamp)->update(['status' => 'end']);
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function saveData($conId, $data)
    {
        try {
            $consultation = new Consultation();
            $result = $consultation->save($data, ['con_id' => $conId]);

            return $result;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }

    }

    public function updateVideoStatus($timeFrom, $timeTo)
    {
        try {
            $consultation = new Consultation();
            $res = $consultation
                ->where('type', '视频咨询')
                ->where('appoint_time', 'between', [$timeFrom, $timeTo])
                ->where('state', 'neq', "已取消")
                ->where('is_refunded', 'no')
                ->where('state', '未进行')
                ->update(['state' => '进行中']);
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    /**
     * @param      $cid
     * @param      $did
     * @param      $conType 'image','phone','video'
     * @param      $moneyForPay
     * @param      $couponId
     * @param bool $checkBalance
     * @param int  $during
     *
     * @return bool|int
     *
     * id 有效性
     * 是否已经结束上次问诊
     * 优惠券
     * 金额
     */
    public function prepareNewConsultation($cid, $did, $conType, $moneyForPay, $couponId = 0, $checkBalance = false, $during = 1, $isFree = false)
    {
        try {
            $is_open = 'is_open_' . $conType;
            $priceType = $conType . '_price';
            $conType = ['image' => '图文咨询', 'phone' => '电话咨询', 'video' => '视频咨询'][$conType];

            // 用户信息
            $customer = new Customer();
            $customerInfo = $customer->field(['c_id', 'money', 'mobile_num'])->where('c_id', $cid)->find();
            $customerInfo = $customerInfo->toArray();
            unset($customer);
            if (empty($customerInfo) || !isset($customerInfo['c_id']) || empty($customerInfo['c_id'])) {
                // 无效用户 id
                return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }
            if ($conType == '电话咨询' && empty($customerInfo['mobile_num'])) {
                return RESPONSE_FAIL_CUSTOMER_NO_MOBILE;
            }

            // 医生信息
            $doctor = new Doctor();
            $doctorField = ['d_id', 'is_open_image', 'is_open_phone', 'is_open_video', 'image_price', 'phone_price', 'video_price'];
            $doctorInfo = $doctor->audit()->field($doctorField)->where('d_id', $did)->find();
            $doctorInfo = $doctorInfo->toArray();
            unset($doctor);
            if (empty($doctorInfo) || !isset($doctorInfo['d_id']) || empty($doctorInfo['d_id'])) {
                // 无效医生 id 或医生未通过验证
                return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }
            $price = $doctorInfo[$priceType] * $during;
            if ($doctorInfo[$is_open] == 'no' || empty($price)) {
                // 医生未开通该服务 or 未设置价格
                    return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }

            if ($conType == '图文咨询') {
                $whereStatement = [
                    "c_id" => $cid,
                    "d_id" => $did,
                    "type" => $conType,
                    "state" => ['exp', "REGEXP '未进行|进行中'"]
                ];
                $consultation = new Consultation();
                $conResult = $consultation->field('con_id')->where($whereStatement)->find();
                if (!empty($conResult) || !empty($conResult->con_id)) {
                    // 存在未结束的咨询
                    return RESPONSE_FAIL_CONSULTATION_UNFINISHED;
                }
            }

            if (!empty($couponId)) {
                // 检查优惠券
                $customerCoupon = new CustomerCoupon();
                $couponField = [
                    "cc.cc_id", "cc.co_id", "cc.d_id", "cc.is_used", "cc.create_time",
                    "co.count", "co.valid_time"
                ];
                $couponInfo = $customerCoupon->alias('cc')->field($couponField)
                                             ->join("__COUPON__ co", "cc.co_id = co.co_id")
                                             ->where(['cc.c_id' => $cid, 'cc.cc_id' => $couponId])->find();
                if (empty($couponInfo)) {
                    // 该优惠券不存在
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->is_used == 'yes') {
                    // 该优惠券已经被使用
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->d_id != $did && $couponInfo->d_id != '0') {
                    // 对该医生不可用
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->create_time + $couponInfo->valid_time < time()) {
                    // 优惠券已过期
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->count == 0) {
                    $price = 0;
                } else {
                    $price = $price - $couponInfo->count;
                }
            }

            if ($isFree) {
                $price = 0;
                $checkBalance = false;
            }

            if ($moneyForPay != $price) {
                // 价格错误
                return RESPONSE_FAIL_WRONG_PRICE;
            }

            if ($checkBalance && $customerInfo['money'] < $moneyForPay) {
                // 余额不足
                return RESPONSE_FAIL_NO_ENOUGH_MONEY;
            }

            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function prepareNewService($cid, $did, $serviceType, $moneyForPay, $couponId = 0, $checkBalance = false, $during = '1', &$realPrice)
    {
        try {
            $is_open = 'is_open_' . $serviceType;
            $priceType = $serviceType . '_price';
            $serviceTypeStr = ['guidance' => '院后指导', 'private' => '私人医生'][$serviceType];

            // 用户信息
            $customer = new Customer();
            $customerInfo = $customer->field(['c_id', 'money', 'mobile_num'])->where('c_id', $cid)->find();
            $customerInfo = $customerInfo->toArray();
            unset($customer);
            if (empty($customerInfo) || !isset($customerInfo['c_id']) || empty($customerInfo['c_id'])) {
                // 无效用户 id
                return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }

            // 医生信息
            $doctor = new Doctor();
            $doctorField = ['d_id', 'is_open_guidance', 'is_open_private', 'guidance_price', 'private_price'];
            $doctorInfo = $doctor->audit()->field($doctorField)->where('d_id', $did)->find();
            $doctorInfo = $doctorInfo->toArray();
            unset($doctor);
            if (empty($doctorInfo) || !isset($doctorInfo['d_id']) || empty($doctorInfo['d_id'])) {
                // 无效医生 id 或医生未通过验证
                return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }
            $price = 0;
            if ($serviceType == 'guidance') {
                $price = $doctorInfo[$priceType] * $during;
            } else {
                $priceArray = $doctorInfo[$priceType];
                if (empty($priceArray) || empty(customUnserialize($priceArray)) || empty(customUnserialize($priceArray)[$during])) {
                    return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
                }
                $price = customUnserialize($priceArray)[$during];
            }
            if ($doctorInfo[$is_open] == 'no' || empty($price)) {
                // 医生未开通该服务 or 未设置价格
                return RESPONSE_FAIL_RESOURCE_NOT_FOUND;
            }
            $realPrice = $price;

            $hasUnclosedService = $this->hasUnclosedService($cid, $did);
            if ($hasUnclosedService !== true) {
                return $hasUnclosedService;
            }

            if (!empty($couponId)) {
                // 检查优惠券
                $customerCoupon = new CustomerCoupon();
                $couponField = [
                    "cc.cc_id", "cc.co_id", "cc.d_id", "cc.is_used", "cc.create_time",
                    "co.count", "co.valid_time"
                ];
                $couponInfo = $customerCoupon->alias('cc')->field($couponField)
                                             ->join("__COUPON__ co", "cc.co_id = co.co_id")
                                             ->where(['cc.c_id' => $cid, 'cc.cc_id' => $couponId])->find();
                if (empty($couponInfo)) {
                    // 该优惠券不存在
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->is_used == 'yes') {
                    // 该优惠券已经被使用
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->d_id != $did && $couponInfo->d_id != '0') {
                    // 对该医生不可用
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->create_time + $couponInfo->valid_time < time()) {
                    // 优惠券已过期
                    return RESPONSE_FAIL_INVALID_COUPON;
                }
                if ($couponInfo->count == 0) {
                    $price = 0;
                } else {
                    $price = $price - $couponInfo->count;
                }
            }

            if ($moneyForPay != $price) {
                // 价格错误
                return RESPONSE_FAIL_WRONG_PRICE;
            }

            if ($checkBalance && $customerInfo['money'] < $moneyForPay) {
                // 余额不足
                return RESPONSE_FAIL_NO_ENOUGH_MONEY;
            }

            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function prepareNewAppointment($did, $time)
    {
        try {
            $timeline = new Timeline();
            $timeInfo = $timeline->where('d_id', $did)->find();

            if (empty($timeInfo)) {
                return false;
            }

            $timeInfo = $timeInfo->toArray();

            $timeArray = get_time_point($time);
            if ($timeArray === false) {
                return false;
            }
            $fieldName = $timeArray['day'] . '_s';
            $setting = customUnserialize($timeInfo['schedule']); //医生设置
            $schedule = customUnserialize($timeInfo[$fieldName]); //该日预约

            if (!($setting[$timeArray['hour']] === 'yes' && $schedule[$timeArray['hour']] === 'yes')) {
                return false;
            }

            if ($time < time() + 10 * 60) {
                return false; // 十分钟前不可再预约
            }

            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }

    }

    public function getServicePrice($did, $serviceType, $during)
    {
        try {
            $doctor = new Doctor();
            $info = $doctor->field(['d_id', 'guidance_price', 'private_price'])->where('d_id', $did)->find();
            if (empty($info)) {
                return 0;
            }
            $info = $info->toArray();
            $price = 0;
            $priceType = $serviceType . '_price';

            if ($serviceType == 'guidance') {
                $price = $info[$priceType] * $during;
            } else {
                $priceArray = $info[$priceType];
                if (empty($priceArray) || empty(customUnserialize($priceArray)) || empty(customUnserialize($priceArray)[$during])) {
                    return 0;
                }
                $price = customUnserialize($priceArray)[$during];
            }
            return $price;
        } catch (\Exception $e) {
            ex_log($e);
            return 0;
        }
    }

    public function addAppointment($cid, $did, $cpId, $type, $price, $time, $during, $seId)
    {
        try {
            $appointmentData = [
                'cp_id' => $cpId,
                'd_id' => $did,
                'c_id' => $cid,
                'type' => $type,
                'price' => $price,
                'status' => 'wait',
                'appoint_date' => date('YmdHis', $time),
                'appoint_time' => $time,
                'during' => $during,
                'se_id' => $seId,
            ];

            $appointment = new Appointment();
            $appointment->data($appointmentData);
            $appointment->save();

            if (empty($appointment->ap_id)) {
                return false;
            }

            $result = [
                'ap_id' => $appointment->ap_id . "",
                'c_id' => $cid . "",
                'd_id' => $did . "",
                'type' => $type . "",
            ];
            return $result;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function addConsultationProfile($cid, $profileData)
    {
        try {
            $profile = new ConsultationProfile();
            $profileData['c_id'] = $cid;
            $profile->data($profileData);
            $profile->save();

            $result = [
                'cp_id' => $profile->cp_id,
            ];

            if (empty($profile->cp_id)) {
                return false;
            }

            return $result;

        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function addConsultation($cid, $did, $money, $type, $cpId, $totalTime, $apId, $apTime, $exConId)
    {
        $types = ['image' => '图文咨询', 'phone' => '电话咨询', 'video' => '视频咨询'];
        $isEx = empty($exConId) ? 'no' : 'yes';
        try {
            $consultation = new Consultation();
            $consultationData = [
                'c_id' => $cid,
                'd_id' => $did,
                'money' => $money,
                'state' => '进行中',
                'type' => $types[$type],
                'total_time' => $totalTime,
                'valid_time' => $totalTime,
                'ap_id' => $apId,
                'appoint_time' => $apTime,
                'is_extend' => $isEx,
                'cp_id' => $cpId,
            ];
            $consultation->data($consultationData);
            $consultation->save();

            $conId = $consultation->con_id;
            if (empty($conId)) {
                return false;
            }

            // 病历关联conId
            $relatedResult = $this->relatedProfile($cpId, $conId);
            if (!$relatedResult) {
                return false;
            }

            // 添加转诊记录
            if (!empty($exConId)) {
                $res = $this->addExConsultation($did, $conId, $exConId);
                if ($res === false) {
                    return false;
                }
            }

            $resultData = [
                'con_id' => $conId . "",
                'c_id' => $cid . "",
                'd_id' => $did . "",
                'state' => $consultationData['state'] . "",
                'type' => $consultationData['type'] . "",
            ];

            return $resultData;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function updateCoupon($ccid, $cid)
    {
        try {
            $updateData = [
                'is_used' => 'yes',
                'used_time' => time(),
            ];
            $whereStatement = [
                'c_id' => $cid,
                'cc_id' => $ccid,
                'is_used' => 'no',
            ];

            $customerCoupon = new CustomerCoupon();
            $result = $customerCoupon->where($whereStatement)->update($updateData);
            if (empty($result)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }

    }

    public static function relatedProfile($cpId, $conId)
    {
        // 病历关联咨询Id
        try {
            $profile = new ConsultationProfile();
            $res = $profile->where('cp_id', $cpId)->update(['con_id' => $conId]);
            if ($res === false) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }

    }

    public function getDetail($conId)
    {
        try {
            $fields = [
                'con.con_id', 'con.d_id', 'con.c_id', 'con.call_data', 'con.valid_time', 'con.type',
                'cp.name',
                'do.real_name', 'do.easemob_username as do_ease',
                'cu.easemob_username as cu_ease',
            ];
            $consultation = new Consultation();
            $info = $consultation->alias('con')->field($fields)
                ->join("__CONSULTATION_PROFILE__ cp", 'con.con_id = cp.con_id')
                ->join("__CUSTOMER__ cu", 'con.c_id = cu.c_id')
                ->join("__DOCTOR__ do", 'con.d_id = do.d_id')
                ->where('con.con_id', $conId)->find();
            if (empty($info)) {
                return false;
            }
            return $info->toArray();
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getCallData($conId, $userId, $userType)
    {
        $whereStatement = [
            'con_id' => $conId,
        ];
        if ($userType == 'customer') {
            $whereStatement['c_id'] = $userId;
        } else if ($userType == 'doctor') {
            $whereStatement['d_id'] = $userId;
        } else {
            return false;
        }
        $consultation = new Consultation();
        $info = $consultation->field(['con_id','call_data'])->where($whereStatement)->find();

        if (empty($info)) {
            return false;
        }

        $info = $info->toArray();

        if (empty($info) || empty($info['call_data'])) {
            return false;
        }
        return unserialize($info['call_data']);
    }

    public function consultationRefund($conId, $cid, $price, $leftSecond)
    {
        try {
            $minute = $leftSecond / 60 + 1;
            $refundMinute = (intval($minute / 5)) * 5;

            $refund = new Refund();
            $data = [
                'con_id' => $conId,
                'c_id' => $cid,
                'money' => $price * $refundMinute,
                'price' => $price,
                'left_time' => $leftSecond,
            ];
            $refund->data($data);
            $refund->save();

            if (empty($refund->rf_id)) {
                return false;
            }

            return $data['money'];
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function hasUnclosedService($cid, $did)
    {
        try {
            $field = [
                'c_id', 'd_id', 'type', 'create_time', 'status', 'money', 'cp_id', 'end_time',
            ];
            $whereState = [
                'c_id' => $cid,
                'd_id' => $did,
                'status' => ['neq', 'no'],
            ];
            $service = new Service();
            $info = $service->field($field)->where($whereState)->order("create_time DESC")->find();


            if (empty($info)) {
                return true;
            } else {
                if($info->status == 'wait'){
                    return "40024"; // 预约待确认
                }else{
                    // status == 'yes'
                    if($info->end_time > time()){
                        return "40025"; // 服务正在进行
                    }else{
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function haveCurrentService($cid, $did)
    {
        try {
            $field = [
                'c_id', 'd_id', 'type', 'create_time', 'status', 'money', 'cp_id', 'end_time',
            ];
            $whereState = [
                'c_id' => $cid,
                'd_id' => $did,
                'status' => ['neq', 'no'],
            ];
            $service = new Service();
            $info = $service->field($field)->where($whereState)->order("create_time DESC")->find();


            if (empty($info)) {
                return true;
            } else {
                if($info->status == 'wait'){
                    return "40024"; // 预约待确认
                }else{
                    // status == 'yes'
                    if($info->end_time > time()){
                        return "40025"; // 服务正在进行
                    }else{
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            ex_log($e);
            return RESPONSE_FAIL_SQL_ERROR;
        }
    }

    public function addService($cid, $did, $cpId, $type, $money, $during, $realMoney, $apId)
    {
        try {
            $currentTime = time();
            $data = [
                'c_id' => $cid,
                'd_id' => $did,
                'cp_id' => $cpId,
                'type' => $type,
                'money' => $money,
                'during' => $during,
                'real_money' => $realMoney,
                'create_time' => $currentTime,
                'end_time' => $currentTime + $during * 24 * 60 * 60,
                'ap_id' => $apId
            ];
            $service = new Service();
            $service->data($data);
            $service->save();

            if (empty($service->se_id)) {
                return false;
            }

            $result = [
                'se_id' => $service->se_id . "",
                'c_id' => $cid . "",
                'd_id' => $did . "",
                'type' => $type . "",
            ];
            return $result;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }

    }

    public function checkService($cid, $did, $time = '', &$serviceId)
    {
        try {
            $time = empty($time) ? time() : $time;
            $whereState = [
                'c_id' => $cid,
                'd_id' => $did,
                'status' => 'yes',
            ];
            $service = new Service();
            $info = $service->where($whereState)->order("end_time DESC")->find();
            if (empty($info)) {
                return false;
            }
            $info = $info->toArray();
            if ($time > $info['end_time']) {
                return false;
            } else {
                $serviceId = $info['se_id'];
                return true;
            }
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function getServices($did, $type)
    {
        try {
            $field = [
                'se.se_id', 'se.c_id', 'se.d_id', 'se.cp_id', 'se.money', 'se.status', 'se.during', 'se.create_time', 'se.end_time',
                'cu.avatar', 'cu.easemob_username', 'cp.name', 'cp.gender', 'cp.age',
                'con.con_id', 'con.state', 'cr.cr_id', 'cr.diagnose', 'cr.advise', 'cr.report_time'
            ];
            $whereState = [
                'se.d_id' => $did,
                'se.type' => $type,
                'se.status' => ['neq', 'no'],
            ];

            $res = [];
            $service = new Service();
            $list = $service->alias("se")->field($field)
                ->join("__CUSTOMER__ cu", "se.c_id = cu.c_id")
                ->join("__CONSULTATION_PROFILE__ cp", "cp.cp_id = se.cp_id")
                ->join("__CONSULTATION__ con", "se.se_id = con.service_id AND con.type = '图文咨询'", "LEFT")
                ->join("__CONSULTATION_REPORT__ cr", "cr.con_id = con.con_id", "LEFT")
                ->where($whereState)
                ->order('se.create_time DESC')->select();
            if ($list === false) {
                throw new \Exception("sql error");
            }
            foreach ($list as $item) {
                $res[] = $item->toArray();
            }
            return $res;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }


    private function addExConsultation($did, $conId, $exConId)
    {
        try {
            $consultation = new Consultation();
            $oldConInfo = $consultation->where('con_id', $exConId)->find();
            $oldConInfo = $oldConInfo->toArray();
            $extentData = [
                'd_id' => $did,
                'b_d_id' => $oldConInfo['d_id'],
                'con_id' => $conId,
                'b_con_id' => $oldConInfo['con_id'],
            ];
            $extent = new ExConsultation();
            $extent->data($extentData);
            $extent->save();
            if (empty($extent->exc_id)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

}
