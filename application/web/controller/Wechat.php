<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/18/2016
 * Time: 5:39 PM
 */
namespace app\web\controller;

use app\index\controller\Base;
use app\web\logic\CustomerLogic;
use think\View;
use app\common\model\Wechat as WechatSDK;
use think\Request;
use think\Exception;

class Wechat extends Base
{
    const HTTP_GET = 0;
    const  HTTP_POST = 1;

    public function __construct()
    {

        $request = \think\Request::instance();
        $action = $request->action();
        if ($action == 'wxcallback' || $action == 'getwxsignature')
            return;
        $wxlogin = Request::instance()->param('wxlogin');
        try {
            $token = $_COOKIE['token'];
            if (!$this->isTokenValid($token))
                $this->wxLogin($wxlogin);
        } catch (Exception $e) {
            $this->wxLogin($wxlogin);
        }
    }

    private function isTokenValid($token)
    {
        $customer = new CustomerLogic();
        $cid = $customer->valiSession($token);
        if (!$cid)
            return false;
        return true;
    }

    public function getWxSignature()
    {
        $url = get_post_value('url');
        $wxlogin = get_post_value('wxlogin');
        $from = get_post_value('from');
        $isappinstalled = get_post_value('isappinstalled');
        $state = get_post_value('state'); //第一次授权登录自动加的恶心参数
        if (!empty($url))
            $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
        if (!empty($wxlogin))
            $url = $url . '&wxlogin=' . $wxlogin;
        if (!empty($state))
            $url = $url . '&state=' . $state;
        if (!empty($from) || !empty($isappinstalled))
            $url = $url . '&from=' . $from . '&isappinstalled=' . $isappinstalled;
        $response = $this->sendRequest(self::HTTP_GET, 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx11cb625cd11729bd&secret=6517eb1ec0f437ac7fbff8daf93d392c'
            , array(), null);
        $response = json_decode($response);
        $access_token = $response->access_token;

        if ($access_token == null)
            $this->private_result('80001');
        $response2 = $this->sendRequest(self::HTTP_GET, 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $access_token . '&type=jsapi'
            , array(), null);
        $response2 = json_decode($response2);
        $ticket = $response2->ticket;
        if ($ticket == null)
            $this->render('80002');
        $nonceStr = $this->getRandChar(16);
        $timeStamp = time();
        $param = 'jsapi_ticket=' . $ticket . '&noncestr=' . $nonceStr . '&timestamp=' . $timeStamp . '&url=' . $url;

        $signature = sha1($param);
        return $this->private_result(RESPONSE_SUCCESS, array('signature' => $signature, 'json_ticket' => $ticket, 'nonceStr' => $nonceStr, 'param' => $param, 'timeStamp' => $timeStamp));
    }

    private function sendRequest($method, $url, $requestHeader, $requestBody)
    {
        $ch = curl_init($url);
        if ($method == self::HTTP_GET) {
            curl_setopt_array($ch, array(
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $requestHeader,
            ));
        } elseif ($method == self::HTTP_POST) {
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $requestHeader,
                CURLOPT_POSTFIELDS => $requestBody
            ));
        }
        // Send the request
        $response = curl_exec($ch);
        // Check for errors
        if ($response === FALSE) {
            die(curl_error($ch));
        }

        return $response;
    }

    private function getRandChar($length)
    {
        $str = null;
        $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    public function test()
    {
        $view = new  View();
        return $view->fetch('wechat/test');
    }

    public function wxLogin($state = 'a')
    {
        $configData = config('oauth')['wechat'];

        $wx = new WechatSDK();
        if ($wx->config($configData) === false) {
            $errorMessage = $wx->getErrorMessage();
            // todo: 错误处理
            echo "something wrong with: " . $errorMessage;
            exit();
        }

        $url = $wx->getUrl($state);
        if ($url === false) {
            $errorMessage = $wx->getErrorMessage();
            // todo: 错误处理
            echo "something wrong with: " . $errorMessage;
            exit();
        }

        $this->redirect($url);
    }

    public function wxCallback()
    {
        $code = get_post_value('code');
        $state = get_post_value('state');

        $configData = config('oauth')['wechat'];
        $wx = new WechatSDK();
        if ($wx->config($configData) === false) {
            $errorMessage = $wx->getErrorMessage();
            // todo: 错误处理
            echo "something wrong with: " . $errorMessage;
            exit();
        }

        $userInfo = $wx->getUserInfoByCode($code);
        if ($userInfo === false) {
            $errorMessage = $wx->getErrorMessage();
            // todo: 错误处理
            echo "something wrong with: " . $errorMessage;
            exit();
        }
        log_file(var_export($userInfo, true), "wx_user_info", "wc_user_info");

        $unionid = isset($userInfo['unionid']) ? $userInfo['unionid'] : null;
        $openid = isset($userInfo['openid']) ? $userInfo['openid'] : null;
        $oauthId = empty($unionid) ? $openid : $unionid;
        $oauthType = 'wechat';
        $name = $userInfo['nickname'];
        $avatar = $userInfo['headimgurl'];

        $customer = new CustomerLogic();
        $result = $customer->oauthLogin($oauthType, $oauthId, $name, $avatar);

        if (empty($result)) {
            // todo: 错误处理 $errMsgMap['30010'] = '注册失败';
            echo "注册失败";
        }
        $token = $result['token'];
        $cid = $result['c_id'];
        $customerInfo = $customer->getDetail($cid);
        $easeMobUsername = $customerInfo['easemob_username'];
        $easeMobPassword = $customerInfo['easemob_password'];
        $avatar = $customerInfo['avatar'];
        cookie('token', $token, 3 * 24 * 3600);
        cookie('easeMobUsername', $easeMobUsername, 3 * 24 * 3600);
        cookie('easeMobPassword', $easeMobPassword, 3 * 24 * 3600);
        cookie('avatar', $avatar, 3 * 24 * 3600);
        $view = new View();
        $arr = explode('fenge', $state);
        if (sizeof($arr) > 1) {
            $view->assign('did', $arr[1]);
            $view->assign('from', 'offline');
            cookie('currentDid', $arr[1], 3 * 24 * 3600);
            return $view->fetch('wechat/doctor-detail');
        } else
            return $view->fetch('wechat/index');


    }

    public function index()
    {
        $view = new View();
        return $view->fetch('wechat/index');
    }

    public function indexHistory()
    {
        $view = new View();
        return $view->fetch('wechat/index-history');
    }

    public function searchWithValue()
    {
        $view = new View();
        return $view->fetch('wechat/search-value');
    }

    public function searchWihNull()
    {
        $view = new View();
        return $view->fetch('wechat/search-null');
    }

    public function docList()
    {
        $view = new View();
        return $view->fetch('wechat/doctor-list');
    }

    public function docDetail()
    {
        $view = new View();
        $did = Request::instance()->param('did');
        $from = Request::instance()->param('wxlogin');
        if (empty($from))
            $view->assign('from', 'online');
        else $view->assign('from', 'offline');
        $view->assign("did", $did);
        cookie('currentDid', $did, 3 * 24 * 3600);
        return $view->fetch('wechat/doctor-detail');
    }

    public function pickCoupon()
    {
        $view = new View();
        return $view->fetch('wechat/consulting-pick');
    }

    public function editMedicalRecord()
    {
        $view = new View();
        return $view->fetch('wechat/infertility-sex');
    }

    public function editMedicalRecordForNormal()
    {
        $view = new View();
        return $view->fetch('wechat/case-edit');
    }

    public function pay()
    {
        $view = new View();
        return $view->fetch('wechat/consulting-pay');
    }

    public function chat()
    {
        $view = new View();
        return $view->fetch('wechat/chat');
    }

    /**
     * 当前问诊服务
     * @return string
     */
    public function consultationRecord()
    {
        $view = new View();
        return $view->fetch('wechat/user-record');
    }

    /**
     * 历史问诊服务
     * @return string
     */
    public function consultationHistRecord()
    {
        $view = new View();
        return $view->fetch('wechat/user-record-history');
    }

    /**
     * 评价医生
     * @return string
     */
    public function evaluation()
    {
        $view = new View();
        return $view->fetch('wechat/user-evaluate');
    }

    public function chatRecord()
    {
        $view = new View();
        return $view->fetch('wechat/user-chatRecord');
    }


    public function userCoupon()
    {
        $view = new View();
        return $view->fetch('wechat/user-coupon');
    }

    public function getFollowedDocList()
    {
        $view = new View();
        return $view->fetch('wechat/user-attentio');
    }


    public function userIndex()
    {
        $view = new View();
        return $view->fetch('wechat/user-index');
    }

    public function userName()
    {
        $view = new View();
        return $view->fetch('wechat/edit-name');
    }

    public function userMobile()
    {
        $view = new View();
        return $view->fetch('wechat/edit-phone');
    }

    public function userEmail()
    {
        $view = new View();
        return $view->fetch('wechat/edit-email');
    }

    public function userVocation()
    {
        $view = new View();
        return $view->fetch('wechat/edit-vocation');
    }
}