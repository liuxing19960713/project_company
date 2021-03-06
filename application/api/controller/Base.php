<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\controller;

use app\common\model\User as UserModel;
use app\common\model\UserToken as TokenModel;
use app\common\model\User;
use think\Controller;
use think\exception\HttpResponseException;
use think\Request;

class Base extends Controller
{
    public $renderCode = 200;
    public $renderMessage = '';
    public $renderData = [];
    public $renderJson;

    protected $param = [];
    protected $userModel;
    protected $tokenModel;
    protected $userId;

    protected $pageIndex;
    protected $pageSize;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->param = $this->request->param();
        $this->getPageIndexAndSize();

        // 获取 token
        $token = $request->header('token');

        // todo: 测试用，要删除
//        if (empty($token) && config('app_debug')) {
//            $token = 'cf85d51aaab54c59f7dced5e4878cfe3';
//        }

        // Index 控制器不验证身份
        if ($request->controller() == 'Index') {
            return;
        }

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
    }

    /**
     * 获取返回状态码
     * @return int
     */
    protected function getRenderCode()
    {
        return $this->renderCode;
    }

    /**
     * 获取返回提示信息
     * @return string
     */
    protected function getRenderMessage()
    {
        return $this->renderMessage;
    }

    /**
     * 获取返回内容
     * @return array
     */
    protected function getRenderData()
    {
        return $this->renderData;
    }

    /**
     * 设置返回状态码
     * @param int $renderCode
     */
    protected function setRenderCode($renderCode)
    {
        $this->renderCode = intval($renderCode);
    }

    /**
     * 设置返回提示信息
     * @param string $renderMessage
     */
    protected function setRenderMessage($renderMessage)
    {
        $this->renderMessage = $renderMessage;
    }

    /**
     * 添加返回内容
     * @param $dataKey
     * @param $renderData
     * @param bool $asObject
     */
    protected function addRenderData($dataKey, $renderData, $asObject = true)
    {
        if ($asObject && (is_null($renderData) || is_array($renderData))) {
            $this->renderData[$dataKey] = (object)$renderData;
        } elseif ($renderData === false) {
            $this->renderData[$dataKey] = (object)[];
        } else {
            $this->renderData[$dataKey] = $renderData;
        }
    }

    /**
     * 获取返回的jsonResp
     * @return \think\response\Json
     */
    protected function getRenderJson()
    {
        $data = [
            'code' => $this->renderCode,
            'message' => $this->renderMessage,
            'data' => (object)$this->renderData,
        ];
        $optionalHeaders = [
            "Access-Control-Allow-Origin" => "*",
        ];
        header('Access-Control-Allow-Headers:token', false);
        header('Access-Control-Allow-Headers:content-type', false);
        $this->renderJson = json($data, 200, $optionalHeaders);
        return $this->renderJson;
    }

    /**
     * 验证参数
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @throws HttpResponseException
     */
    protected function check($data, $validate)
    {
        unset($validateResult);
        $validateResult = $this->validate($data, $validate);
        if ($validateResult !== true) {
            $this->setRenderCode(402);
            $this->setRenderMessage($validateResult);
            throw new HttpResponseException($this->getRenderJson());
        }
    }

    /**
     * 单个参数验证
     *
     * @param $value
     * @param $key
     * @param $validate
     */
    protected function checkSingle($value, $key, $validate)
    {
        $this->check([$key => $value], $validate);
    }

    /**
     * 获取用户模型
     * @return \app\common\model\User
     */
    protected function getUserModel()
    {
        if (empty($this->userModel)) {
            $this->userModel = User::get($this->tokenModel['user_id']);
        }
        if (empty($this->userModel)) {
            $this->userModel = new UserModel();
        }
        return $this->userModel;
    }

    protected function getUserId()
    {
        return $this->userId;
    }

    /**
     * 获取 token 模型
     * @return \app\common\model\UserToken
     */
    protected function getTokenModel()
    {
        if (empty($this->tokenModel)) {
            $this->tokenModel = new TokenModel();
        }
        return $this->tokenModel;
    }

    /**
     * 获取指定参数
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    protected function getParam($key, $defaultValue = null)
    {
        return isset($this->param[$key]) ? trim($this->param[$key]) : $defaultValue;
    }

    /**
     * 获取多个参数，keysArray为空时获得全部参数
     * 注意键名不能为数字
     *
     * @param array|null $keysArray
     * @return array
     */
    protected function selectParam($keysArray = null)
    {
        if ($keysArray) {
            $paramResult = [];
            foreach ($keysArray as $key => $value) {
                if (is_int($key)) {
                    // 没有 key , 用数字作为key
                    $paramResult[$value] = $this->getParam($value);
                } else {
                    // 有 key 和 value , value为参数默认值
                    $paramResult[$key] = $this->getParam($key, $value);
                }
            }
            return $paramResult;
        } else {
            return $this->param;
        }
    }

    /**
     * 获取页码和每页数量
     * @param int $defaultIndex
     * @param int $defaultSize
     * @param int $sizeLimit
     */
    protected function getPageIndexAndSize($defaultIndex = 1, $defaultSize = 20, $sizeLimit = 1000)
    {
        $this->pageIndex = isset($this->param['page_index']) ? $this->param['page_index'] : null;
        $this->pageSize = isset($this->param['page_size']) ? $this->param['page_size'] : null;
        $this->pageIndex = $this->pageIndex ?: $defaultIndex;
        $this->pageSize = $this->pageSize ?: $defaultSize;

        $this->checkSingle($this->pageIndex, 'page_index', 'Base.page_index');
        $this->checkSingle($this->pageSize, 'page_size', 'Base.page_size');

        if ($this->pageSize > $sizeLimit) {
            $this->setRenderCode(402);
            $this->setRenderMessage("请求的内容过多");
            throw new HttpResponseException($this->getRenderJson());
        }
    }
}
