<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use think\exception\HttpResponseException;
use app\common\model\BannerUser;
use app\common\model\BannerDoctor;
use app\common\model\ArticleIncrease;
use app\common\model\Oauth;
use app\common\model\Order;
use app\common\model\User as UserModel;
use app\common\model\UserToken;
use app\common\model\Sms as SmsModel;
use app\common\tools\XxPay;
use Qiniu\Auth;
use think\Db;

/**
 * Class Index
 * Index 控制器为无状态控制器，不验证身份，调用 $userModel 和 $tokenModel 时要注意
 * @package app\api\controller
 */
class Index extends Base
{
    /**
     * 发送验证码短信
     * @requestExample {"mobile": "13112341234", "sms_type": "reg"}
     * @responseExample {"code":200,"message":"短信发送成功","data":{}}
     * @return \think\response\Json
     */
    public function sendSms()
    {
        # 获取参数 验证参数
        $requestData = $this->selectParam(['mobile', 'sms_type' => 'reg']);
        $this->check($requestData, 'Base.sendSms');

        # 验证账户有效性
        $userModel = UserModel::build()
            ->field(['user_id', 'mobile'])->where(['mobile' => $requestData['mobile'],'is_deleted'=>'no'])->find();
        switch ($requestData['sms_type']) {
            case 'reg':
            case 'bind':
                if (!empty($userModel)) {
                    $this->setRenderCode(400);
                    $this->setRenderMessage('该手机号已被注册');
                    return $this->getRenderJson();
                }
                break;
            case 'pass':
                if (empty($userModel)) {
                    $this->setRenderCode(400);
                    $this->setRenderMessage('该手机号还没注册');
                    return $this->getRenderJson();
                }
                break;
        }

        # 发送短信 记录发送
        $sendResult = SmsModel::sendSms($requestData['mobile'], get_random_num_str(), $requestData['sms_type']);
        if (!$sendResult) {
            $this->setRenderCode(500);
            $this->setRenderMessage('短信发送失败，请稍后再试');
            return $this->getRenderJson();
        }

        $this->setRenderMessage('短信发送成功');
        return $this->getRenderJson();
    }

    /**
     * 验证短信验证码
     * @requestExample {"mobile": "13112341234", "sms_type": "reg", "code": "1386"}
     * @responseExample {"code":200,"message":"验证成功","data":{}}
     * @return \think\response\Json
     */
    public function validateCode()
    {
        # 获取参数 验证参数
        $requestData = $this->selectParam(['mobile', 'sms_type', 'code']);
        $this->check($requestData, 'Base.validateCode');

        # 验证验证码
        $validateResult = SmsModel::validateCode(
            $requestData['mobile'],
            $requestData['code'],
            $requestData['sms_type']
        );
        if (!$validateResult) {
            $this->setRenderCode(400);
            $this->setRenderMessage('验证码错误');
            return $this->getRenderJson();
        }

        $this->setRenderMessage('验证成功');
        return $this->getRenderJson();
    }

    /**
     * 用户注册, 需要短信验证码
     * @requestExample {"mobile": "13164720321", "password": "123456", "code": "1386", "province":"广东省", "city":"深圳市"}
     * @responseExample {"code":200,"message":"注册成功","data":{}}
     * @return \think\response\Json
     */
    public function register()
    {
        # 获取参数 验证参数
        $requestData = $this->selectParam(['mobile', 'code', 'password', 'province', 'city']);
        $this->check($requestData, 'Base.register');

        # 验证验证码
        $validateResult = SmsModel::validateCode($requestData['mobile'], $requestData['code'], 'reg');
        if (!$validateResult) {
            $this->setRenderCode(400);
            $this->setRenderMessage('验证码错误');
            return $this->getRenderJson();
        }

        # 再次验证手机号码有效性
        $checkExist = UserModel::build()
            ->field(['user_id', 'mobile'])->where(['mobile' => $requestData['mobile']])->find();
        if (!empty($checkExist)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('该手机号已被注册');
            return $this->getRenderJson();
        }

        # 注册用户
        $registerResult = UserModel::register(
            $requestData['mobile'],
            $requestData['password'],
            $requestData['province'],
            $requestData['city']
        );
        if (!$registerResult) {
            $this->setRenderCode(500);
            $this->setRenderMessage('注册失败了');
            return $this->getRenderJson();
        }

        $this->setRenderMessage('注册成功');
        return $this->getRenderJson();
    }

    public function bind()
    {
        $token = $this->request->header('token');
        // 验证token
        if (empty($token)) {
            $this->setRenderCode(401);
            $this->setRenderMessage('无效会话，请重新登录');
            throw new HttpResponseException($this->getRenderJson());
        }
        $this->tokenModel = $this->getTokenModel()->getTokenInfo($token);
        if (empty($this->tokenModel)) {
            $this->setRenderCode(401);
            $this->setRenderMessage('会话已过期，请重新登录');
            throw new HttpResponseException($this->getRenderJson());
        }
        $this->userId = $this->tokenModel['user_id'];

        # 获取参数 验证参数
        $requestData = $this->selectParam(['mobile', 'code', 'password']);
        $this->check($requestData, 'Base.bind');


        # 验证验证码
        $validateResult = SmsModel::validateCode($requestData['mobile'], $requestData['code'], 'bind');
        if (!$validateResult) {
            $this->setRenderCode(400);
            $this->setRenderMessage('验证码错误');
            return $this->getRenderJson();
        }

        # 再次验证手机号码有效性
        $checkExist = UserModel::build()
            ->field(['user_id', 'mobile'])->where(['mobile' => $requestData['mobile']])->find();
        if (!empty($checkExist)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('该手机号已被注册');
            return $this->getRenderJson();
        }

        # 注册用户
        $registerResult = UserModel::bindUser(
            $this->userId,
            $requestData['mobile'],
            $requestData['password']
        );
        if (!$registerResult) {
            $this->setRenderCode(500);
            $this->setRenderMessage('绑定失败了');
            return $this->getRenderJson();
        }

        $this->setRenderMessage('绑定成功');
        return $this->getRenderJson();
    }

    /**
     * 用户登录
     * @requestExample {"mobile": "13112341234", "password": "123456"}
     * @return \think\response\Json
     */
    public function login()
    {
        $userData = $this->selectParam(['mobile', 'password']);

        $this->check($userData, 'User.login');
        $userData['password'] = md5($userData['password']);
        $userData['is_deleted'] = 'no';
        $userModel = UserModel::get($userData);
        if (empty($userModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('帐号或密码错误');
            return $this->getRenderJson();
        }

        $token = UserToken::build()->storeToken($userModel['user_id']);
        if (empty($token) || empty($token['token'])) {
            $this->setRenderCode(500);
            $this->setRenderMessage('服务器异常');
            $this->addRenderData('info', 'store token error');
            return $this->getRenderJson();
        }
        $this->addRenderData('token', $token);

        $userInfoField = [
            'user_id', 'nick_name', 'avatar', 'mobile', 'email',
            'gender', 'birthday', 'age', 'blood_type', 'real_name',
            'province', 'city',
            'money', 'fans_count', 'follow_count', 'tube_stage',
            'is_push',
            'easemob_username', 'easemob_password'
        ];
        $userInfoArray = $userModel->visible($userInfoField)->toArray();
        $this->addRenderData('user_info', $userInfoArray);

        return $this->getRenderJson();
    }

    /**
     * 修改密码
     * @return \think\response\Json
     */
    public function changePassword()
    {
        // 获取 token
        $token = $this->request->header('token');
        $oldPassword = $this->getParam('old_password');
        $newPassword = $this->getParam('new_password');
        $this->checkSingle($oldPassword, 'password', 'Base.password');
        $this->checkSingle($newPassword, 'password', 'Base.password');

        // 验证token
        if (empty($token)) {
            $this->setRenderCode(401);
            $this->setRenderMessage('无效会话，缺少验证信息');
            return $this->getRenderJson();
        }
        $this->tokenModel = $this->getTokenModel()->getTokenInfo($token);
        if (empty($this->tokenModel)) {
            $this->setRenderCode(401);
            $this->setRenderMessage('无效会话，请重新登录');
            return $this->getRenderJson();
        }
        $this->userId = $this->tokenModel['user_id'];

        $userModel = $this->getUserModel();
        if (md5($oldPassword) != $userModel['password']) {
            $this->setRenderCode(400);
            $this->setRenderMessage('修改失败，原密码错误');
            return $this->getRenderJson();
        }

        $userModel->isUpdate(true)->save(['password' => md5($newPassword)]);

        $this->setRenderMessage('修改成功');
        return $this->getRenderJson();
    }

    public function resetPassword()
    {
        # 获取参数 验证参数
        $requestData = $this->selectParam(['mobile', 'code', 'password']);
        $this->check($requestData, 'Base.reset_password');

        # 验证验证码
        $validateResult = SmsModel::validateCode($requestData['mobile'], $requestData['code'], 'pass');
        if (!$validateResult) {
            $this->setRenderCode(400);
            $this->setRenderMessage('验证码错误');
            return $this->getRenderJson();
        }

        $userModel = UserModel::build()
            ->field(['user_id', 'mobile', 'password'])->where(['mobile' => $requestData['mobile'],'is_deleted'=>'no'])->find();
        if (empty($userModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('该帐号不存在');
            return $this->getRenderJson();
        }
        $userModel->isUpdate(true)->save(['password' => md5($requestData['password'])]);

        $this->setRenderMessage('修改成功');
        return $this->getRenderJson();
    }

    public function oauthLogin()
    {
        $requestData = $this->selectParam(
            ['oauth_type', 'oauth_id', 'nick_name', 'avatar', 'province', 'city', 'access_token']
        );
        $this->check($requestData, 'User.oauth');

        if ($requestData['oauth_type'] == 'qq') {
            $accessToken = $requestData['access_token'];
            if (empty($accessToken)) {
                $this->setRenderCode(401);
                $this->setRenderMessage('认证失败');
                return $this->getRenderJson();
            }
            $url = "https://graph.qq.com/oauth2.0/me?access_token={$accessToken}&unionid=1";
            $res = getCurl($url);
            if (empty($res)) {
                $this->setRenderCode(500);
                $this->setRenderMessage('第三方登录失败');
                return $this->getRenderJson();
            }

            $pattern = '/(?:\{)(.*)(?:\})/i';
            preg_match($pattern, $res, $result);
            $resArray = json_decode($result[0], true);
            if (empty($resArray['unionid'])) {
                $this->setRenderCode(500);
                $this->setRenderMessage('第三方登录失败');
                return $this->getRenderJson();
            }
            $requestData['oauth_id'] = $resArray['unionid'];
        }

        $userId = Oauth::oauthLogin(
            $requestData['oauth_type'],
            $requestData['oauth_id'],
            $requestData['nick_name'],
            $requestData['avatar'],
            $requestData['province'],
            $requestData['city']
        );

        $token = UserToken::build()->storeToken($userId);
        if (empty($token) || empty($token['token'])) {
            $this->setRenderCode(500);
            $this->setRenderMessage('服务器异常');
            $this->addRenderData('info', 'store token error');
            return $this->getRenderJson();
        }
        $this->addRenderData('token', $token);

        $userInfoField = [
            'user_id', 'nick_name', 'avatar', 'mobile', 'email',
            'gender', 'birthday', 'age', 'blood_type', 'real_name',
            'province', 'city',
            'money', 'fans_count', 'follow_count', 'tube_stage',
            'is_push',
            'easemob_username', 'easemob_password'
        ];
        $userInfoArray = UserModel::get($userId)->visible($userInfoField)->toArray();
        $this->addRenderData('user_info', $userInfoArray);

        return $this->getRenderJson();
    }

    /**
     * 注销token
     * @return \think\response\Json
     */
    public function logout()
    {
        // 获取 token
        $token = $this->request->header('token');

        if (!empty($token)) {
            UserToken::disableToken($token);
        }
        $this->setRenderMessage('成功登出');

        return $this->getRenderJson();
    }

    /**
     * 姐妹怀孕数量
     * @return \think\response\Json
     */
    public function successCount()
    {
        $count = Db::table('yyb_user')->where(['tube_stage' => '验孕'])->count('user_id');
        $count = intval($count) + 156;
        $this->addRenderData('count', $count);
        return $this->getRenderJson();
    }

    /**
     * 获取用户端首页banner
     * @return \think\response\Json
     */
    public function banners()
    {
        $fieldList = [
            'bau_id', 'img_url','href_url'
        ];
        $whereMap = [
            'is_hidden' => 'no',
            'is_deleted' => 'no',
        ];
        $orderMap = [
            'order_num' => 'desc'
        ];

        $bannerModelList = BannerUser::build()
            ->field($fieldList)
            ->where($whereMap)
            ->order($orderMap)
            ->select();

        $this->addRenderData('banners', $bannerModelList, false);
        return $this->getRenderJson();
    }


    /**
     * 获取医生端首页banner
     * @return \think\response\Json
     */
    public function doctorBanners()
    {
        $fieldList = [
                'bad_id', 'img_url','href_url'
        ];
        $whereMap = [
                'is_hidden' => 'no',
                'is_deleted' => 'no',
        ];
        $orderMap = [
                'order_num' => 'desc'
        ];

        $bannerModelList = BannerDoctor::build()
                ->field($fieldList)
                ->where($whereMap)
                ->order($orderMap)
                ->select();

        $this->addRenderData('banners', $bannerModelList, false);
        return $this->getRenderJson();
    }
    
    /**
     * 获取服务器时间
     * @return \think\response\Json
     */
    public function getServerTime()
    {
        $this->addRenderData('server_time', time());
        return $this->getRenderJson();
    }

    /**
     * 获取七牛上传token和domain，给移动端用
     * @return \think\response\Json
     */
    public function qiniu()
    {
        $qnConfig = config('qiniu');
        $auth = new Auth($qnConfig['accessKey'], $qnConfig['secretKey']);
        $upToken = $auth->uploadToken($qnConfig['bucket']);

        $this->addRenderData('domain', $qnConfig['bucketDomain']);
        $this->addRenderData('uptoken', $upToken);
        return $this->getRenderJson();
    }

    /**
     * 获取七牛上传token，给网页用
     * @return \think\response\Json
     */
    public function uptoken()
    {
        $qnConfig = config('qiniu');
        $auth = new Auth($qnConfig['accessKey'], $qnConfig['secretKey']);
        $upToken = $auth->uploadToken($qnConfig['bucket']);

        return json(['uptoken' => $upToken], 200, ["Access-Control-Allow-Origin" => "*"]);
    }


    public function checkOrder()
    {
        $orderId = $this->getParam('order_id', '');
        $this->checkSingle($orderId, 'id', 'base.id');

        $orderModel = Order::get($orderId);
        if (empty($orderModel)) {
            $this->setRenderCode(500);
            $this->setRenderMessage('服务器异常');
            return $this->getRenderJson();
        }

        $this->addRenderData('order_status', $orderModel['status']);
        return $this->getRenderJson();
    }

    public function isDebug()
    {
        $this->addRenderData('is_debug', '0');
        return $this->getRenderJson();
    }

    public function payTest()
    {
        $xxPayConfig = config('xxpay');
        $paySdk = new XxPay(
            $xxPayConfig['app_id'],
            $xxPayConfig['app_pri_key'],
            $xxPayConfig['pub_key'],
            $xxPayConfig['md5_pri_key']
        );
        $url = $paySdk->pay('test', md5(time()).rand(10000,99999), '0.01');
        var_dump($url);
    }

    public function fixTube()
    {
        return "";
        \app\common\model\TubeRecord::fixInitStage();
    }


    /**
     * 提高成功案例
     * @return \think\response\Json
     */
    public function increaseIndex()
    {
        $field = [
                'in_id', 'title', 'keyword', 'img_url', 'views_count', 'create_time'
        ];
        $whereMap = [
                'is_deleted' => 'no',
        ];
        $orderMap = [
                'create_time' => 'desc'
        ];
        $articleModelList = ArticleIncrease::build()
                ->field($field)
                ->where($whereMap)
                ->order($orderMap)
                ->page($this->pageIndex, $this->pageSize)
                ->select();

        $this->addRenderData('article_list', $articleModelList, false);
        return $this->getRenderJson();
    }

    /**
     * 提高成功率详情
     * @return \think\response\Json
     */
    public function increaseShow()
    {
        $id = $this->getParam('in_id');
        $this->checkSingle($id, 'id', 'Base.id');

        $field = [
                'in_id', 'title', 'keyword', 'img_url', 'content', 'views_count', 'create_time'
        ];
        $whereMap = [
                'in_id' => $id,
                'is_deleted' => 'no',
        ];
        $articleModel = ArticleIncrease::build()->field($field)->where($whereMap)->find();
        if (empty($articleModel)) {
            $this->setRenderCode(400);
            $this->setRenderMessage('找不到了');
            $this->addRenderData('info', "article not found, article_id: $id");
            return $this->getRenderJson();
        }

        # 阅读数量加1
        ArticleIncrease::build()->where($whereMap)->inc('views_count')->update();

        $this->addRenderData('article', $articleModel);

        return $this->getRenderJson();
    }
}
