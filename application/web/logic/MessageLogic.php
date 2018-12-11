<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/17/16
 * Time: 15:37
 */

namespace app\web\logic;

use app\common\model\Easemob;
use app\web\model\Customer;
use app\web\model\Doctor;
use app\web\model\Message;

class MessageLogic
{
    public function addMessage($userId, $userType, $content, $subType, $title = '')
    {
        try {
            $messageData = [
                'user_id' => $userId,
                'user_type' => $userType,
                'content' => $content,
                'sub_type' => $subType,
                'title' => empty($title) ? '优医惠' : $title,
                'status' => 'yes',
            ];

            $message = new Message();
            $message->data($messageData);
            $message->save();

            $meId = $message->me_id;
            if (empty($meId)) {
                return false;
            }
            return $messageData;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    public function pushMessage($messageData, $easeMobAccount = '', $userId = '', $userType = '', $isShow = true)
    {
        try {
            if (empty($easeMobAccount)) {
                $easeMobAccount = $this->getEaseMobAccount($userId, $userType);
            }
            if (empty($easeMobAccount)) {
                throw new \Exception('empty easeMob account', '9999');
            }
            $easeMob = new Easemob();
            $token = $easeMob->getToken();
            if ($isShow) {
                $messageData['show'] = 'yes';
            } else {
                $messageData['show'] = 'no';
            }
            $result = $easeMob->pushSendChat($easeMobAccount, json_unicode_encode($messageData), "", "txt", $token);
            return $result;
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

    private function getEaseMobAccount($userId, $userType)
    {
        if (empty($userId)) {
            return false;
        }

        if ($userType != 'doctor' && $userType != 'customer') {
            return false;
        }

        if ($userType == 'doctor') {
            $doctor = new Doctor();
            $userInfo = $doctor->field('easemob_username')->where('d_id', $userId)->find();
        } else {
            $customer = new Customer();
            $userInfo = $customer->field('easemob_username')->where('c_id', $userId)->find();
        }

        if (!empty($userInfo->easemob_username)) {
            $easeMobAccount = $userInfo->easemob_username;
            return $easeMobAccount;
        } else {
            return false;
        }
    }
}
