<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\admin\model;

use app\common\tools\BaiduPush;
use app\common\tools\Easemob;
use think\Model;

class MessageDoctorModel extends Model
{
	protected $table = 'yyb_message_doctor';

    /**
     * @return MessageDoctor
     */
    public static function build()
    {
        return new self();
    }

    public static function pushMessage(
        $userId,
        $content,
        $extraInfo = [],
        $fromUserId = '0',
        $conId = '0',
        $messageType = 'con',
        $fromEaseMob = '',
        $toEaseMob = ''
    ) {
        $messageData = [
            'user_id' => $userId,
            'from_user_id' => '0', //
            'con_id' => '0', //
            'message_type' => 'con', //
            'content' => $content,
            'extra' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
            'sub_type' => isset($extraInfo['sub_type']) ? $extraInfo['sub_type'] : '',
        ];
        self::create($messageData);

        $doctorInfo = DoctorModel::get($userId);
        if ($doctorInfo['is_push'] == 'no') {
            return;
        }

        if (!empty($fromEaseMob) && !empty($toEaseMob)) {
            $easeMob = new Easemob();
            $easeMob->pushSystemMessage(config('easemob'), $toEaseMob, $content, 'txt', $extraInfo);
        }
    }

    public static function pushSystemMessage(
        $userId,
        $content,
        $extraInfo = [],
        $fromUserId = '0',
        $conId = '0',
        $messageType = 'con',
        $toEaseMob = ''
    ) {
        $messageData = [
            'user_id' => $userId,
            'from_user_id' => '0', //
            'con_id' => '0', //
            'message_type' => 'system', //
            'content' => $content,
            'extra' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
            'sub_type' => isset($extraInfo['sub_type']) ? $extraInfo['sub_type'] : '',
        ];
        self::create($messageData);

        $doctorInfo = DoctorModel::get($userId);
        if ($doctorInfo['is_push'] == 'no') {
            return;
        }

        $easeMob = new Easemob();
        $easeMob->pushSystemMessage(config('easemob'), $toEaseMob, $content, 'txt', $extraInfo);
    }

    public static function pushNotifyMessage($doctorId, $content, $extraInfo)
    {
        $userModel = DoctorModel::get($doctorId);
        $toEaseMob = $userModel['easemob_username'];

        $doctorInfo = DoctorModel::get($doctorId);
        if ($doctorInfo['is_push'] == 'no') {
            return;
        }

        $easeMob = new Easemob();
        $easeMob->pushSystemMessage(config('easemob'), $toEaseMob, $content, 'txt', $extraInfo);
    }

    public static function pushSystemMessageToAllUser($content, $extra = '', $subType = '')
    {
        $userModelList = DoctorModel::build()->field(['doctor_id'])->select();

        $messageDataList = [];
        foreach ($userModelList as $item) {
            $tempMessageDataMap = [
                'user_id' => $item['doctor_id'],
                'content' => $content,
                'message_type' => 'system',
                'extra' => $extra,
                'sub_type' => $subType,
            ];
            $messageDataList[] = $tempMessageDataMap;
        }
        self::build()->saveAll($messageDataList);

        BaiduPush::pushToAllDoctor($content, config('baidupush'));
    }
}
