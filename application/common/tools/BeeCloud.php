<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 * Date: 12/13/16
 * Time: 16:52
 */

namespace app\common\tools;

class BeeCloud
{
    private $appId = null;
    private $appSecret = null;
    private $dataArray = [];

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;

        $rawData = file_get_contents("php://input");
        $dataArray = json_decode($rawData, true);
        $this->dataArray = $dataArray;
    }

    public function checkSign()
    {
        $timestamp = $this->dataArray['timestamp'];
        $signInput = strtolower($this->dataArray['sign']);
        $signCorrect = strtolower(md5($this->appId . $this->appSecret . $timestamp));
        if ($signInput === $signCorrect) {
            return true;
        }
        return false;
    }

    public function getDataArray()
    {
        if (!empty($this->dataArray)) {
            return $this->dataArray;
        }
        return false;
    }

    public function getTimestamp()
    {
        $data = isset($this->dataArray['timestamp']) ? $this->dataArray['timestamp'] : false;
        return $data;
    }

    public function getChannelType()
    {
        $data = isset($this->dataArray['channel_type']) ? $this->dataArray['channel_type'] : false;
        return $data;
    }

    public function isTradeWithdraw()
    {
        $data = isset($this->dataArray['trade_success']) ? $this->dataArray['trade_success'] : false;
        return $data;
    }

    public function getTransactionId()
    {
        $data = isset($this->dataArray['transaction_id']) ? $this->dataArray['transaction_id'] : false;
        return $data;
    }

    public function getFee()
    {
        $data = isset($this->dataArray['transaction_fee']) ? $this->dataArray['transaction_fee'] : false;
        return $data;
    }

    public function getOptional()
    {
        $data = isset($this->dataArray['optional']) ? $this->dataArray['optional'] : false;
        return $data;
    }


}
