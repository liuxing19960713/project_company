<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/8/16
 * Time: 20:22
 */

namespace app\common\tools;

class QingMaYun
{
    private $baseUrl     = 'https://api.qingmayun.com';
    private $softVersion = '20141029';
    private $header      = [];

    private $accountSid;
    private $authToken;
    private $appId;

    private $errCode = [];
    private $errMsg  = [];

    public function __construct($accountSid, $authToken, $appId, $isJson = true)
    {
        $this->accountSid = strtolower($accountSid);
        $this->authToken = $authToken;
        $this->appId = $appId;

        if ($isJson) {
            $contentType = 'application/json;charset=utf-8';
            $accept = 'application/json';
        } else {
            // todo: xml解析
            //$contentType = 'application/xml;charset=utf-8';
            //$accept = 'application/xml';
            $contentType = 'application/json;charset=utf-8';
            $accept = 'application/json';
        }

        $this->header = ['Content-type: ' . $contentType, 'Accept: ' . $accept,];
    }

    /**
     * Client账号创建
     * @link http://www.qingmayun.com/zhanghaochuangjian.html
     *
     * @param null $mobile
     * @param null $name
     * @param null $balance
     *
     * @return array|false
     *
     */
    public function createAccount($mobile = null, $name = null, $balance = null)
    {
        $clientData = array('appId' => $this->appId);
        if ($name !== null) {
            $clientData['friendlyName'] = $name;
        }
        if ($mobile !== null) {
            $clientData['mobile'] = $mobile;
        }
        if ($balance !== null) {
            $clientData['balance'] = $balance;
        }

        $url = $this->createUrl('clients');
        $header = $this->header;
        $requestData = json_encode(array('client' => $clientData), JSON_UNESCAPED_UNICODE);

        $result = $this->send_request($url, $header, $requestData);

        $res = json_decode($result, true);
        $code = $this->checkResponseCode($res);

        if ($code === true) {
            return ['account' => $res['result']['clientNumber'], 'password' => $res['result']['clientPwd']];
        }

        if ($code == '00026') {
            $findData = $this->findAccount($mobile);
            if ($findData !== false) {
                return $findData;
            }
        }

        if ($code === false) {
            $this->errCode[] = '99999';
            $this->errMsg[] = var_export($result, true);
        } else {
            $this->errCode[] = $code;
            $this->errMsg[] = var_export($result, true);
        }

        return false;
    }

    public function findAccount($mobile)
    {
        $clientData = array('appId' => $this->appId);
        if (empty($mobile)) {
            return false;
        }
        $clientData['start'] = "0";
        $clientData['mobile'] = $mobile;
        $clientData['limit'] = "1";
        $url = $this->createUrl('clientList');
        $header = $this->header;
        $requestData = json_encode(array('client' => $clientData), JSON_UNESCAPED_UNICODE);

        $result = $this->send_request($url, $header, $requestData);

        $res = json_decode($result, true);
        $code = $this->checkResponseCode($res);

        if ($code === true) {
            return [
                'account' => $res['result']['client'][0]['clientNumber'],
                'password' => $res['result']['client'][0]['clientPwd']
            ];
        }
        return false;
    }

    /**
     * 发起通话
     * @link http://www.qingmayun.com/huibo.html
     *
     * @param        $clientNumber
     * @param        $called
     * @param int    $allowedCallTime
     * @param string $userData
     *
     * @return array|bool
     */
    public function startCall($clientNumber, $called, $allowedCallTime = 1800, $userData = 'empty')
    {
        if (empty($clientNumber) || empty($called)) {
            $this->errCode[] = '99999';
            $this->errMsg[] = "empty number, client number = {$clientNumber}, called = {$called}";
            return false;
        }

        $data = array('appId' => $this->appId, 'clientNumber' => $clientNumber, 'called' => $called,
                      'allowedCallTime' => $allowedCallTime, 'userData' => $userData,);

        $url = $this->createUrl('call/callBack');
        $header = $this->header;
        $requestData = json_encode(array('callback' => $data), JSON_UNESCAPED_UNICODE);

        $result = $this->send_request($url, $header, $requestData);

        $res = json_decode($result, true);
        $code = $this->checkResponseCode($res);

        if ($code === true) {
            return ['callId' => $res['result']['callId'],];
        }

        if ($code === false) {
            $this->errCode[] = '99999';
            $this->errMsg[] = var_export($result, true);
        } else {
            $this->errCode[] = $code;
            $this->errMsg[] = var_export($result, true);
        }

        return false;
    }

    /**
     * 取消通话
     * @link http://www.qingmayun.com/cancel.html
     *
     * @param        $callId
     * @param string $cancelType
     *
     * @return array|bool
     */
    public function stopCall($callId, $cancelType = '0')
    {
        if (empty($callId)) {
            return false;
        }

        $data = array('appId' => $this->appId, 'callId' => $callId, 'cancelType' => $cancelType,);

        $url = $this->createUrl('v2calls/cancel');
        $header = $this->header;
        $requestData = json_encode(array('call' => $data), JSON_UNESCAPED_UNICODE);

        $result = $this->send_request($url, $header, $requestData);

        $res = json_decode($result, true);
        $code = $this->checkResponseCode($res);

        if ($code === true) {
            return [];
        }

        if ($code === false) {
            $this->errCode[] = '99999';
            $this->errMsg[] = var_export($result, true);
        } else {
            $this->errCode[] = $code;
            $this->errMsg[] = var_export($result, true);
        }

        return false;
    }

    public function getErrCode()
    {
        return $this->errCode;
    }

    public function getErrMsg()
    {
        return $this->errMsg;
    }

    public function clearError()
    {
        $this->errCode = [];
        $this->errMsg = [];
    }

    public function checkSig($rawData)
    {
        if (empty($rawData)) {
            return false;
        }
        if (!isset($rawData['sig']) || !isset($rawData['accountId']) || !isset($rawData['timestamp'])) {
            return false;
        }

        $hash = strtolower(md5($rawData['accountId'] . $this->authToken . $rawData['timestamp']));

        if ($hash == $rawData['sig']) {
            return true;
        }
        return false;
    }

    private function send_request($url, $header, $postFields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 不直接输出
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function createUrl($functionName)
    {
        date_default_timezone_set("Asia/Shanghai");
        $timeStamp = date("YmdHis");
        $sig = strtolower(md5($this->accountSid . $this->authToken . $timeStamp));

        $functionQuery = "{$functionName}?sig={$sig}&timestamp={$timeStamp}";
        $url = "{$this->baseUrl}/{$this->softVersion}/accounts/{$this->accountSid}/{$functionQuery}";

        return $url;
    }

    private function checkResponseCode($resultData)
    {
        if (isset($resultData['result']) && isset($resultData['result']['respCode'])) {
            if ($resultData['result']['respCode'] == '00000') {
                return true;
            } else {
                return $resultData['result']['respCode'];
            }
        } else {
            return false;
        }
    }
}
