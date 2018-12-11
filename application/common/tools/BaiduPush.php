<?php

namespace app\common\tools;

require __DIR__ . '/baidupushsdk/sdk.php';

class BaiduPush
{
    public static function pushToDoctor($channelId, $content, $deviceType, $config, $title = '优孕宝')
    {
        return self::pushMessageToSingleDevice($channelId, $content, 'doctor', $deviceType, $config, $title);
    }

    public static function pushToUser($channelId, $content, $deviceType, $config, $title = '优孕宝')
    {
        return self::pushMessageToSingleDevice($channelId, $content, 'user', $deviceType, $config, $title);
    }

    public static function pushToAllDoctor($content, $config, $title = '优孕宝')
    {
        return self::pushToAll($content, 'doctor', $config, $title);
    }

    public static function pushToAllUser($content, $config, $title = '优孕宝')
    {
        return self::pushToAll($content, 'user', $config, $title);
    }

    public function pushMessageToSingleDevice($channelId, $content, $userType, $deviceType, $config, $title = '优孕宝')
    {
        if ($deviceType != 'ios' && $deviceType != 'android') {
            return false;
        }

        $apiKey = $config[$userType][$deviceType]['api_key'];
        $secretKey = $config[$userType][$deviceType]['secret_key'];
        $sdk = new \PushSDK($apiKey, $secretKey);

        $message = [];
        $options = [
            'msg_type' => 1
        ];

        if ($deviceType == 'ios') {
            $message['aps'] = array('alert' => $content);
            $options['deploy_status'] = 2; // iOS应用的部署状态:  1：开发状态；2：生产状态； 若不指定，则默认设置为生产状态。

        }
        if ($deviceType == 'android') {
            $message['title'] = $title;
            $message['description'] = $content;
        }

        // 向目标设备发送一条消息
        $pushResult = $sdk->pushMsgToSingleDevice($channelId, $message, $options);

        // 判断返回值,当发送失败时, $rs的结果为false, 可以通过getError来获得错误信息.
        if ($pushResult === false) {
            // $errorCode = $sdk->getLastErrorCode();
            return false;
        }

        return true;
    }

    public static function pushToAll($content, $userType, $config, $title = '优孕宝')
    {
        $options = [
            'msg_type' => 1,
            'deploy_status' => 2,
        ];

        $iosSdk = new \PushSDK($config[$userType]['ios']['api_key'], $config[$userType]['ios']['secret_key']);
        $iosMessage = [
            'aps' => array('alert' => $content),
            'deploy_status' => 2,
        ];
        $iosPushResult = $iosSdk->pushMsgToAll($iosMessage, $options);


        $androidSdk = new \PushSDK($config['doctor']['android']['api_key'], $config['doctor']['android']['secret_key']);
        $androidMessage = array(
            'title' => $title,
            'description' => $content
        );
        $androidPushResult = $androidSdk->pushMsgToAll($androidMessage, $options);

        if ($iosPushResult === false || $androidPushResult === false) {
            return false;
        }

        return true;
    }
}
