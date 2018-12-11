<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\command;

use app\common\model\Consultation;
use app\common\tools\QingMaYun;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Startcalling extends Command
{
    protected function configure()
    {
        # 注册命令
        $this->setName('startcalling')->setDescription("开始拨号");
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $currentTime = time();
            $timeFrom = $currentTime - 60 * 10;
            $timeTo = $currentTime + 60 * 10;

            $field = [
                'con.con_id', 'con.c_id', 'con.d_id', 'con.appoint_time', 'con.call_data', 'con.type',
                'con.valid_time', 'con.total_time', 'con.ap_id',
                'do.nick_name', 'do.mobile', 'do.easemob_username as do_ease', 'do.phone_price', 'do.video_price',
                'cu.qmy_client', 'cu.easemob_username as cu_ease',
                'cp.name',
            ];
            $waitQueue = Consultation::build()->alias('con')->field($field)
                ->join("__DOCTOR__ do", 'con.d_id = do.doctor_id')
                ->join("__USER__ cu", 'con.c_id = cu.user_id')
                ->join("__CONSULTATION_PROFILE__ cp", 'con.con_id = cp.con_id')
                ->where('con.type', '电话咨询')
                ->where('con.appoint_time', 'between', [$timeFrom, $timeTo])
                ->where('con.state', 'neq', "已取消")
                ->where('con.is_refunded', 'no')
                ->select();

            $configData = config('qingmayun');
            $accountSid = $configData['account_sid'];
            $authToken = $configData['auth_token'];
            $appId = $configData['app_id'];
            $handle = new QingMaYun($accountSid, $authToken, $appId);

            foreach ($waitQueue as $item) {
                $clientNumber = $item['qmy_client'];
                $mobileTo = $item['mobile'];
                $resultArray = $handle->startCall($clientNumber, $mobileTo, $item['valid_time'], $item['con_id']);
                every_log('qmy_log', json_encode($item->toArray(), JSON_UNESCAPED_UNICODE), 'log');
                if ($resultArray === false) {
                    $errorCodes = var_export($handle->getErrCode(), true);
                    $errorMessage = var_export($handle->getErrMsg(), true);
                    $handle->clearError();
                    every_log(
                        'timer_call_fail',
                        "error code: {$errorCodes}\nerror msg: {$errorMessage}",
                        "start call fail"
                    );
                    continue;
                }
                $callId = $resultArray['callId'];
                $conId = $item['con_id'];
                $data = [
                    'callId' => $callId,
                    'status' => 'calling',
                    'count' => '1',
                ];
                $callData = [
                    'call_data' => serialize([$data]),
                    'state' => '进行中',
                ];
                // save
                $saveResult = Consultation::update($callData, ['con_id' => $conId]);
                if ($saveResult === false) {
                    every_log('timer_call_fail', "conId: {$conId}\ncall data: {$callData}", "save call data fail");
                }
                unset($resultArray);
            }
            Consultation::build()
                ->where('type', '视频咨询')
                ->where('appoint_time', 'between', [$timeFrom, $timeTo])
                ->where('state', 'neq', "已取消")
                ->where('is_refunded', 'no')
                ->where('state', '未进行')
                ->update(['state' => '进行中']);
        } catch (\Exception $e) {
            ex_log($e, 'ex_timer');
            exit();
        }
    }
}
