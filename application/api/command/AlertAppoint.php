<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\command;

use app\common\model\Consultation;
use app\common\model\MessageDoctor;
use app\common\model\MessageUser;
use app\common\tools\QingMaYun;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class AlertAppoint extends Command
{
    protected function configure()
    {
        # 注册命令
        $this->setName('alertappoint')->setDescription("开始拨号");
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $defaultReason = "您预约的咨询，将在5分钟后开始，请保持电话或网络畅通";
            $currentTime = time();
            $timeFrom = $currentTime;
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
                ->where('con.type', 'neq', '图文咨询')
                ->where('con.appoint_time', 'between', [$timeFrom, $timeTo])
                ->where('con.state', 'neq', "已取消")
                ->where('con.is_refunded', 'no')
                ->select();

            foreach ($waitQueue as $item) {
                $userExtra = [
                    'type' => 'appointment',
                    'event_id' => $item['ap_id'],
                ];
                MessageUser::pushMessage(
                    $item['c_id'],
                    $defaultReason,
                    'system',
                    $userExtra,
                    config('easemob')['admin_username'],
                    $item['cu_ease']
                );
                MessageDoctor::pushMessage(
                    $item['d_id'],
                    $defaultReason,
                    'system',
                    $userExtra,
                    config('easemob')['admin_username'],
                    $item['do_ease']
                );
            }

        } catch (\Exception $e) {
            ex_log($e, 'ex_timer');
            exit();
        }
    }
}
