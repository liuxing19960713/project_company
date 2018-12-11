<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/16/16
 * Time: 10:43
 */

namespace app\web\logic;

use app\index\controller\Base;
use app\index\model\Finance;
use app\web\model\Customer;
use app\web\model\Doctor;
use app\web\model\Gift;
use app\web\model\Order;

class OrderLogic
{
    public function addOrder($cid, $did, $goodsType, $payType, $money, $extra)
    {
        if (empty($cid)|| !$this->checkGoodsType($goodsType) || !$this->checkPayType($payType)) {
            return false;
        }

        $orderData = [
            'c_id' => $cid,
            'd_id' => empty($did) ? '0' : $did,
            'use_type' => ($payType == 'balance') ? 'expense' : 'charge',
            'goods_type' => $goodsType,
            'pay_type' => $payType,
            'money' => $money,
            'extra' => serialize($extra),
            'status' => 'wait'
        ];

        try {
            $order = new Order();
            $order->data($orderData);
            $order->save();
            if (empty($order->or_id)) {
                return false;
            }
            $resultData = [
                'or_id' => $order->or_id . "",
                'c_id' => $order->c_id . "",
                'money' => $order->money . "",
                'status' => $order->status . "",
            ];
            return $resultData;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function completeOrder($orId, $cid, $isCharge = false, $billNo = '')
    {
        $order = new Order();
        $orderInfo = $order->where('or_id', $orId)->find();
        $orderInfo = $orderInfo->toArray();

        if ($orderInfo['status'] != 'wait') {
            return false;
        }

        $returnResult = array();
        $returnResult['or_id'] = $orId;

        $money = $orderInfo['money'];

        $goodsType = $orderInfo['goods_type'];

        $extraInfo = unserialize($orderInfo['extra']);

        $middleInfo = array(); // 中间储存容器

        $consultation = new ConsultationLogic();

        // 生成用户病历
        switch ($goodsType) {
            case 'image':
            case 'phone':
            case 'video':
            case 'guidance':
            case 'private':
                $profileResult = $consultation->addConsultationProfile($cid, $extraInfo['profile']);
                if (!is_array($profileResult)) {
                    return false;
                }
                $middleInfo['cpId'] = $profileResult['cp_id'];
                $middleInfo['username'] = $extraInfo['profile']['name'];
                break;
        }

        // 完成订单任务
        switch ($goodsType) {
            case 'image':
                // 创建图文咨询
                $consultationResult = $consultation->addConsultation($cid, $orderInfo['d_id'], $orderInfo['money'], 'image', $middleInfo['cpId'], 0, '', 0, $extraInfo['exConId']);
                if (!is_array($consultationResult)) {
                    return false;
                }
                $returnResult = $consultationResult;
                $middleInfo['goods_id'] = $consultationResult['con_id'];
                break;
            case 'phone':
            case 'video':
                // 创建预约
                $typeStr = ($goodsType == 'phone') ? '电话咨询' : '视频咨询';
                $appointment = $consultation->addAppointment($cid, $orderInfo['d_id'], $middleInfo['cpId'], $typeStr, $orderInfo['money'], $extraInfo['time'], $extraInfo['during'], $extraInfo['seId']);
                if (!is_array($appointment)) {
                    return false;
                }
                $returnResult = $appointment;
                $middleInfo['goods_id'] = $appointment['ap_id'];
                break;
            case 'guidance':
            case 'private':
                // 创建院后私人预约
                $daysMap = ["a" => "7", "b" => "30", "c" => "90", "d" => "180", "e" => "360"];
                $typeStr = ($goodsType == 'guidance') ? '院后指导' : '私人医生';
                $during = ($goodsType == 'guidance') ? $extraInfo['during'] : $daysMap[$extraInfo['during']];
                $service = $consultation->addService($cid, $orderInfo['d_id'], $middleInfo['cpId'], $typeStr, $orderInfo['money'], $during, $extraInfo['real_money']);
                if (!is_array($service)) {
                    return false;
                }
                $returnResult = $service;
                $middleInfo['goods_id'] = $service['se_id'];
                break;
            case 'gift':
                // 创建心意
                $giftData = [
                    "c_id" => $cid,
                    "d_id" => $orderInfo['d_id'],
                    "gift" => $orderInfo['money'],
                    "title" => $extraInfo['title'],
                    "content" => $extraInfo['content'],
                ];
                $gift = new Gift();
                $gift->data($giftData);
                $gift->save();

                $giftId = $gift->g_id;
                if (empty($giftId)) {
                    return false;
                }
                $middleInfo['goods_id'] = $giftId;
                $returnResult['g_id'] = $giftId;

                // 充值, 给医生加钱
                $doctor = new Doctor();
                $updateMoneyResult = $doctor->where('d_id', $orderInfo['d_id'])->setInc('money', $money);
                if ($updateMoneyResult === false) {
                    return false;
                }
                // 添加医生账户记录
                $doctorExtra = serialize(["id" => $middleInfo['goods_id'], 'type'=> $goodsType]);
                $finance = new Finance();
                $is_finance = $finance->insert($orderInfo['d_id'], "doctor", $money, $goodsType, 'in', $doctorExtra, time());
                if ($is_finance === false) {
                    return false;
                }
                break;
            case 'charge':
                // 充值, 给用户加钱
                $customer = new Customer();
                $updateMoneyResult = $customer->where('c_id', $cid)->setInc('money', $money);
                if ($updateMoneyResult === false) {
                    return false;
                }
                $middleInfo['goods_id'] = '0';
                break;
        }

        // 发送推送给医生
        $did = $orderInfo['d_id'];
        $message = new MessageLogic();
        switch ($goodsType) {
            case 'image':
                $username = $middleInfo['username'];
                $messageData = $message->addMessage($did, 'doctor', "患者 {$username} 向你发起了图文咨询", '图文咨询');
                if (!empty($messageData)) {
                    $message->pushMessage($messageData, null, $did, 'doctor');
                }
                break;
            case 'phone':
            case 'video':
                // 电话视频预约
                $username = $middleInfo['username'];
                $typeStr = ($goodsType == 'phone') ? '电话咨询' : '视频咨询';
                $messageData = $message->addMessage($did, 'doctor', "患者 {$username} 向您发起了{$typeStr}预约", $typeStr);
                if (!empty($messageData)) {
                    $message->pushMessage($messageData, null, $did, 'doctor');
                }
                break;
            case 'guidance':
            case 'private':
                // 院后私人预约
                $username = $middleInfo['username'];
                $typeStr = ($goodsType == 'guidance') ? '院后指导' : '私人医生';
                $messageData = $message->addMessage($did, 'doctor', "患者 {$username} 向您发起了{$typeStr}预约", $typeStr);
                if (!empty($messageData)) {
                    $message->pushMessage($messageData, null, $did, 'doctor');
                }
                break;
            case 'gift':
                // 心意
                $username = $extraInfo['username'];
                $messageData = $message->addMessage($did, 'doctor', "用户 {$username} 送给你{$money}元心意，请财务管理中查看", '心意');
                if (!empty($messageData)) {
                    $message->pushMessage($messageData, null, $did, 'doctor');
                }
                break;
        }

        if (!$isCharge) {
            // 非充值, 即使用余额, 修改用户余额
            $customer = new Customer();
            $money = $orderInfo['money'];
            $updateMoneyResult = $customer->where('c_id', $cid)->setDec('money', $money);
            if ($updateMoneyResult === false) {
                return false;
            }
        }

        // 修改订单状态
        $order = new Order();
        $updateData = [
            'status' => 'yes',
            'goods_id' => $middleInfo['goods_id']
        ];
        if ($isCharge && !empty($billNo)) {
            $updateData['bill_no'] = $billNo;
        }
        $res = $order->where('or_id', $orId)->update($updateData);
        if ($res === false) {
            return false;
        }

        // 修改个人财务记录
        if (!empty($money)) {
            $inOrOut = 'out';
            if ($goodsType == 'charge') {
                $inOrOut = 'in';
            }
            $extra = serialize(["id" => $middleInfo['goods_id'], 'type'=> $goodsType]);
            $finance = new Finance();
            $is_finance = $finance->insert($cid, "customer", $money, $goodsType, $inOrOut, $extra, time());
            if ($is_finance === false) {
                return false;
            }
        }

        $returnResult['status'] = 'yes';
        return $returnResult;
    }

    public function checkOrder($orderId, $cid, $money)
    {
        try {
            $order = new Order();
            $orderInfo = $order->where('or_id', $orderId)->find();
            $orderInfo = $orderInfo->toArray();
            if (empty($orderInfo) || !is_array($orderInfo)) {
                return false;
            }
            if ($orderInfo['c_id'] != $cid) {
                return false;
            }
            if ($orderInfo['money'] != $money || $orderInfo['status'] != 'wait') {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    private function checkPayType($value)
    {
        $allowedWords = ['balance', 'alipay', 'wechat'];
        return $this->isInWords($value, $allowedWords);
    }

    private function checkUseType($value)
    {
        $allowedWords = ['charge', 'expense'];
        return $this->isInWords($value, $allowedWords);
    }

    private function checkGoodsType($value)
    {
        $allowedWords = ['image', 'phone', 'video', 'guidance', 'private', 'gift', 'charge'];
        return $this->isInWords($value, $allowedWords);
    }

    private function isInWords($value, $words)
    {
        if (empty($value)) {
            return false;
        }

        $res = false;
        foreach ($words as $word) {
            if ($value == $word) {
                $res = true;
                break;
            }
        }
        return $res;
    }
}
