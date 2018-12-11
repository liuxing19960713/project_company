<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Controller;
// 七牛云sdk
vendor('qiniu.autoload');
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
header('Access-Control-Allow-Origin: *');
class Base extends Controller
{
	public $renderSuccess = false;
    public $renderMessage = '';
    public $renderResult = [];
    public $renderJson;
	
	/**
     * @param bool $renderSuccess
     */
    public function setRenderSuccess($renderSuccess = true)
    {
        $this->renderSuccess = $renderSuccess;
    }

    /**
     * @param string $renderMessage
     */
    public function setRenderMessage($renderMessage)
    {
        $this->renderMessage = $renderMessage;
    }

    /**
     * @param array | object $renderResult
     */
    public function setRenderResult($renderResult)
    {
        $this->renderResult = $renderResult;
    }
	
	
	public function getRenderJson()
    {
        $data = [
            'success' => $this->renderSuccess,
            'message' => $this->renderMessage,
            'result' => $this->renderResult,
        ];
        $code = $this->renderSuccess ? 200 : 500;
        $this->renderJson = json($data, $code);
        return $this->renderJson;
    }
	
    public function _initialize()
    {
        if(empty(session('username'))){

            $loginUrl = url('login/index');
            if(request()->isAjax()){
                return msg(111, $loginUrl, '登录超时');
            }

            $this->redirect($loginUrl);
        }

        $this->assign([
            'username' => session('username'),
            'rolename' => session('role'),
            'actionx' => session('action')
        ]);

    }
    /**
     * 七牛云服务
    */
    //七牛云鉴权，生成Token
    public function QnToken($accessKey,$secretKey,$bucket) {
        $upManager = new UploadManager();
        // $accessKey = "HbqYHSMA_unefuplEetlRjS3Acwje2fe3wLfuKjn";
        // $secretKey = "2f2ZeK8kvQAzd_LHGymmDtZsJgniXbL0zgGTOiw4";
        //创建auth对象
        $auth = new Auth($accessKey,$secretKey);
        // $bucket = 'video1'; //上传的空间名
        //使用auth对象中的方法传入空间名参数来新建一个上传Token
        $upToken = $auth -> uploadToken($bucket);
        return $upToken;
    }
    //七牛云上传文件
    public function QnUpFile($token,$key,$filePath) {
        //初始化UploadManger()对象并进行文件上传
        $upManager = new UploadManager();
        //调用 UploadManager 的 putFile 方法进行文件的上传
        list($ret, $err) = $upManager -> putFile($token,$key,$filePath);
        if ($err !== null) {
            return $err;
        } else {
            return $ret;
        }
    }
}


