<?php
/**
 * Created by PhpStorm.
 * User: fioChen
 */

namespace app\common\tools;

class XxPay
{
    private $appId = null;
    private $appSecret = null;
    private $platKey = null;
    private $md5Key = null;
    private $rawData = null;
    private $dataArray = [];
    private $orderUrl = 'http://api2.xiaoxiaopay.com:7500/order/';

    public function __construct($appId, $appSecret, $platKey, $md5Key)
    {
        $this->appId = $appId;
        $this->appSecret = $this->clean($appSecret);
        $this->platKey = $this->clean($platKey);
        $this->md5Key = $this->clean($md5Key);

        $this->rawData = file_get_contents("php://input");
        $this->dataArray = json_decode($this->rawData, true);
    }

    public function pay($title, $orderId, $price)
    {
        $ip = $this->getRealIp(); //获取ip

        // 请求参数
        $SignArr = array(
            'merchantID' => $this->appId,  //商户号
            'waresname'  => $title,  //商品名称
            'cporderid'  => $orderId,  //商家订单号，请确保订单号唯一
            'price'      => $price,  //订单金额，单位：元，需要格式化为两位小数
            'returnurl'  => PUBLIC_PATH,  //同步通知地址，完整http链接，支付成功之后跳转的地址
            'notifyurl'  => PUBLIC_PATH . '/index/Pay/xxpay',  //异步通知地址，完整http链接，通知地址后不带任何参数，用于接收支付成功后的异步推送
            'paytype'    => 10006,  //支付方式，详见文档
            'ip'         => $ip  //用户ip
        );

        // 组装请求报文  对数据签名	两种签名方式二选一
//        $reqData = $this->composeRsa($SignArr, $this->appSecret); // 生成含rsa签名的请求数据
        $reqData = $this->composeMd5($SignArr, $this->md5Key); //生成含md5签名的请求数据

        //发送到小小贝服务后台请求调起支付
        $paymentData = $this->httpPost($this->orderUrl, $reqData);

        //根据签名方式验签数据并且解析返回报文
        if (!$this->parseRespRsa($paymentData, $this->platKey)) { //MD5的验签方法为 parseRespMd5
            return false;
        } else {
            //解析返回报文
            $callback = json_decode($paymentData);
            $url = $callback->info->payurl;
            return $url;
        }
    }

    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
        $this->dataArray = json_decode($this->rawData, true);
    }

    public function checkSign()
    {
        return $this->parseRespRsa($this->rawData, $this->platKey);
    }

    public function getDataArray()
    {
        if (!empty($this->dataArray)) {
            return $this->dataArray;
        }
        return false;
    }

    public function getResultCode()
    {
        return isset($this->dataArray['resultCode']) ? $this->dataArray['resultCode'] : false;
    }

    public function getSign()
    {
        return isset($this->dataArray['sign']) ? $this->dataArray['sign'] : false;
    }

    public function getSigntype()
    {
        return isset($this->dataArray['signtype']) ? $this->dataArray['signtype'] : false;
    }

    public function getInfo()
    {
        return isset($this->dataArray['info']) ? $this->dataArray['info'] : false;
    }

    public function getPayStatus()
    {
        return isset($this->dataArray['info']['pay_status']) ? $this->dataArray['info']['pay_status'] : false;
    }

    public function getPayOrder()
    {
        return isset($this->dataArray['info']['pay_order']) ? $this->dataArray['info']['pay_order'] : false;
    }

    public function getTransid()
    {
        return isset($this->dataArray['info']['transid']) ? $this->dataArray['info']['transid'] : false;
    }

    public function getPayFee()
    {
        return isset($this->dataArray['info']['pay_fee']) ? $this->dataArray['info']['pay_fee'] : false;
    }

    public function isWithdraw()
    {
        return ($this->getResultCode() == 20000) && ($this->getPayStatus() == 'TRADE_SUCCESS');
    }

    /**格式化公钥
     * $pubKey PKCS8格式的公钥串
     * return pem格式公钥， 可以保存为.pem文件
     */
    public function formatPubKey($pubKey)
    {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }

    /**格式化公钥
     * $priKey PKCS8格式的私钥串
     * return pem格式私钥， 可以保存为.pem文件
     */
    public function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END RSA PRIVATE KEY-----";
        return $fKey;
    }

    /**RSA签名
     * $data待签名数据
     * $priKey商户私钥
     * 签名用商户私钥
     * 使用MD5摘要算法
     * 最后的签名，需要用base64编码
     * return Sign签名
     */
    public function sign($data, $priKey)
    {
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $priKey, OPENSSL_ALGO_MD5);
        //base64编码
        $sign = base64_encode($sign);
        $sign = urlencode($sign);
        return $sign;
    }

    /**RSA验签
     * $data待签名数据
     * $sign需要验签的签名
     * $pubKey小小贝公钥
     * 验签用小小贝公钥，摘要算法为MD5
     * return 验签是否通过 bool值
     */
    public function verify($data, $sign, $pubKey)
    {
        //调用openssl内置方法验签，返回bool值
        $result  = (bool)openssl_verify($data, base64_decode($sign), $pubKey, OPENSSL_ALGO_MD5);
        //返回资源是否成功
        return $result;
    }

    /**
     * RSA验签
     */
    public function parseRespRsa($content, $pkey)
    {
        $response = json_decode($content);
        $sign = @$response->sign;
        if (empty($sign)) {
            return false;
        }
        //取出验证签名正文，空格转为加号
        $sign = str_replace(' ', '+', $sign);
        $transdata = array();
        foreach ($response->info as $k => $v) {
            if ($k != 'money') { //金额保留两位小数
                $transdata[$k] = trim($v);
            } else {
                $transdata[$k] = sprintf("%.2f", $v);
            }
        }
        //转换为校验签名格式
        $content = $this->createLinkstringUrlencode($transdata);
        //校验签名
        $pkey = $this->formatPubKey($pkey);
        return $this->verify($content, $sign, $pkey);
    }

    /**
     * MD5验签
     */
    public function parseRespMd5($content, $md5key)
    {
        $response = json_decode($content);
        $sign = $response->sign;
        $transdata = array();
        foreach ($response->info as $k => $v) {
            if ($k != 'money') { //金额保留两位小数
                $transdata[$k] = trim($v);
            } else {
                $transdata[$k] = sprintf("%.2f", $v);
            }
        }
        //转换为校验签名格式
        $content = $this->createLinkstringUrlencode($transdata);
        $content .= '&key='.$md5key;
        //生成MD5签名
        $check = md5($content);
        return $sign == $check;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param array $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public function createLinkstringUrlencode($para)
    {
        //使用ASCII码正序
        ksort($para);
        $arg = "";
        while (list($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    /**
     * curl方式发送post报文
     * $remoteServer 请求地址
     * $postData post报文内容
     * $userAgent用户属性
     * return 返回报文
     */
    public function request_by_curl($remoteServer, $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteServer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = urldecode(curl_exec($ch));
        curl_close($ch);
        return $data;
    }

    /**
     * 组装request报文
     * $reqJson 需要组装的json报文
     * $md5key  md5密钥
     * return 返回组装后的报文`
     */
    public function composeMd5($reqJson, $md5key)
    {
        //获取待签名字符串
        $content = $this->createLinkstringUrlencode($reqJson);
        $content .= '&key='.$md5key;
        //生成MD5签名
        $sign = md5($content);
        if (isset($reqJson['psw']) && $reqJson['psw']) {
            unset($reqJson['psw']);
        }
        $content = json_encode($reqJson);
        $reqData = "transdata=".urlencode($content)."&sign=".urlencode($sign)."&signtype=MD5";

        return $reqData;
    }

    /**
     * 组装request报文
     * $reqJson 需要组装的json报文
     * $vkey  cp私钥，格式化之前的私钥
     * return 返回组装后的报文`
     */
    public function composeRsa($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = $this->createLinkstringUrlencode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);
        //生成RSA签名
        $sign = $this->sign($content, $vkey);
        $content = json_encode($reqJson);
        $reqData = "transdata=".urlencode(trim($content))."&sign=".urlencode($sign)."&signtype=RSA";
        return $reqData;
    }

    /**
     * 发送post请求
     * $Url 请求地址
     * $reqData  请求的内容
     * return 返回服务端响应数据
     */
    public function httpPost($Url,$reqData)
    {
        $respData = $this->request_by_curl($Url, $reqData);
        return $respData;
    }

    public function clean($str)
    {
        $qian = array(" ","　","\t","\n","\r","-","BEGIN","PRIVATE","KEY","END","PUBLIC");
        return  str_replace($qian, '', $str);
    }

    public function getRealIp()
    {
        $ip = false;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i=0; $i < count($ips); $i++) {
                if (!eregi('^(10│172.16│192.168).', $ips[$i])) {
                    $ip=$ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}
