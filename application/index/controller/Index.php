<?php
namespace app\index\controller;

use app\common\model\MessageUser;
use app\doctor\model\User;
use app\index\model\Feedback;
use app\web\logic\CustomerLogic;
use app\web\model\Customer;
use think\View;

class Index extends Base
{
    public function index()
    {
    }

    public function pay()
    {
        $title = @$_GET['type'];
        $total = @$_GET['total'];
        $money = @$_GET['money'];
        $titleArray = [
            'image' => '图文咨询',
            'phone' => '电话咨询',
            'video' => '视频咨询',
            'private' => '私人医生'
        ];
        if (in_array($title, array_keys($titleArray))) {
            $title = $titleArray[$title];
        }
        $this->assign('title', $title);
        $this->assign('money', $money);
        $this->assign('total', $total);
        $this->assign('PUBLIC_PATH', PUBLIC_PATH);
        return $this->fetch('index/pay');
    }

    public function next()
    {
        $this->assign('resultstr', '支付成功');
        $this->assign('redirecturl', 'YouYunBao://dk_pay');
        $this->assign('PUBLIC_PATH', PUBLIC_PATH);
        return $this->fetch('index/next');
    }

    public function test()
    {
        MessageUser::pushSystemMessageToAllUser('测试广推');
    }

    /**
     * 意见反馈
     *
     * @return \think\response\Json
     */
//    public function feedback()
//    {
//        $token = get_token();
//        $memberType = get_post_value('member_type', 'doctor');
//        $content = safe_str(get_post_value('content', ''));
//        $mobile = get_post_value('mobile', '');
//
//        // 验参
//        $validateResult = (validate_regex($memberType, '/^(doctor|customer)$/')) &&
//                          (strlen($content) <= 200) &&
//                          (validate_number($mobile) || empty($mobile));
//        if (!$validateResult) {
//            if (empty($memberType) || empty($content)) {
//                return $this->private_result(RESPONSE_FAIL_MISSING_ARGUMENT);
//            } else {
//                return $this->private_result(RESPONSE_FAIL_ILLEGAL_ARGUMENT);
//            }
//        }
//
//        // 验token
//        $memberId = $this->getMemberId($token, $memberType);
//        if (!$memberId) {
//            return $this->private_result(RESPONSE_FAIL_INVALID_TOKEN);
//        }
//
//        $data = [
//            'member_id' => $memberId,
//            'member_type' => $memberType,
//            'content' => $content,
//            'mobile' => $mobile
//        ];
//
//        try {
//            $feedback = new Feedback();
//            $feedback->save($data);
//        } catch (\Exception $e) {
//            ex_log($e);
//            return $this->private_result(RESPONSE_FAIL_SQL_ERROR);
//        }
//
//        return $this->private_result(RESPONSE_SUCCESS);
//    }

//    private function getMemberId($token, $memberType)
//    {
//        if ($memberType == 'doctor') {
//            $user = new User();
//            $memberId = $user->valiToken($token);
//        } else {
//            $user = new CustomerLogic();
//            $memberId = $user->valiSession($token);
//        }
//        return $memberId;
//    }
}
