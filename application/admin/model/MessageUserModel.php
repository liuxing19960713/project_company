<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\admin\model;

use app\common\tools\BaiduPush;
use app\common\tools\Easemob;
use think\Model;

class MessageUserModel extends Model
{
	protected $table = 'yyb_message_user';

    /**
     * @return MessageUser
     */
    public static function build()
    {
        return new self();
    }

    public static function pushMessage(
        $userId,
        $content,
        $messageType,
        $extraInfo = [],
        $fromEaseMob = '',
        $toEaseMob = ''
    ) {
        $messageData = [
            'user_id' => $userId,
            'content' => $content,
            'message_type' => $messageType,
            'from_user_id' => isset($extraInfo['from_user_id']) ? $extraInfo['from_user_id']: '',
            'extra' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
            'sub_type' => isset($extraInfo['sub_type']) ? $extraInfo['sub_type'] : '',
        ];
        self::create($messageData);
        // 单推改用环信，不用百度推送
//        $userTokenModel = UserToken::build()->where(['user_id' => $userId])->find();
//        if (empty($userTokenModel) || empty($userTokenModel['channel_id'])) {
//            $channelId = $userTokenModel['channel_id'];
//            $deviceType = $userTokenModel['device'];
//            BaiduPush::pushToUser($channelId, $content, $deviceType, config('baidupush'));
//        }
        $userInfo = UserModel::get($userId);
        if ($userInfo['is_push'] == 'no') {
            return;
        }
        if (!empty($fromEaseMob) && !empty($toEaseMob)) {
            $easeMob = new Easemob();
            $easeMob->sendMessage(config('easemob'), $fromEaseMob, $toEaseMob, $content, 'txt', $extraInfo);
        }
    }

    public static function pushSystemMessage($userId, $content, $extraInfo = [], $eventId = 0)
    {
        $userModel = UserModel::get($userId);
        $toEaseMob = $userModel['easemob_username'];
        $messageData = [
            'user_id' => $userId,
            'content' => $content,
            'message_type' => 'system',
            'from_user_id' => isset($extraInfo['from_user_id']) ? $extraInfo['from_user_id']: '',
            'extra' => json_encode($extraInfo, JSON_UNESCAPED_UNICODE),
            'sub_type' => isset($extraInfo['sub_type']) ? $extraInfo['sub_type'] : '',
            'event_id' => $eventId,
        ];
        self::create($messageData);

        $userInfo = UserModel::get($userId);
        if ($userInfo['is_push'] == 'no') {
            return;
        }

        $easeMob = new Easemob();
        $easeMob->pushSystemMessage(config('easemob'), $toEaseMob, $content, 'txt', $extraInfo);
    }

    public static function pushNotifyMessage($userId, $content, $extraInfo)
    {
        $userModel = UserModel::get($userId);
        $toEaseMob = $userModel['easemob_username'];

        $userInfo = UserModel::get($userId);
        if ($userInfo['is_push'] == 'no') {
            return;
        }

        $easeMob = new Easemob();
        $test = $easeMob->pushSystemMessage(config('easemob'), $toEaseMob, $content, 'txt', $extraInfo);
        every_log($test, 'test', 'test');
    }

    public static function pushSystemMessageToAllUser($content, $extra = '', $subType = '')
    {
//        $userModelList = User::build()->field(['user_id','easemob_username'])->select();
//        $messageDataList = [];
//        $ease_mob_list = [];
//        $current = time();
//        foreach ($userModelList as $item) {
//            $tempMessageDataMap = [
//                'user_id' => $item['user_id'],
//                'content' => $content,
//                'message_type' => 'system',
//                'extra' => $extra,
//                'sub_type' => $subType,
//            ];
//            $ease_mob_list[] = $item['easemob_username'];
//            $messageDataList[] = $tempMessageDataMap;
//        }
//        empty($messageDataList)?true:self::build()->insertAll($messageDataList);
//        $easeMob = new Easemob();
//        $easeMob->pushSystemAllMessage(config('easemob'), $ease_mob_list, $content, 'txt',$extra);
//
        $userModelList = UserModel::build()->field(['user_id'])->select();

        $messageDataList = [];
        foreach ($userModelList as $item) {
            $tempMessageDataMap = [
                'user_id' => $item['user_id'],
                'content' => $content,
                'message_type' => 'system',
                'extra' => $extra,
                'sub_type' => $subType,
            ];
            $messageDataList[] = $tempMessageDataMap;
        }
        self::build()->saveAll($messageDataList);

        BaiduPush::pushToAllUser($content, config('baidupush'));
    }
}
