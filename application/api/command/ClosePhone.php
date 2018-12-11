<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\command;

use app\common\model\Appointment;
use app\common\model\Consultation;
use app\common\model\Refund;
use app\common\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class ClosePhone extends Command
{
    protected function configure()
    {
        # 注册命令
        $this->setName('close:phone')->setDescription("结束电话咨询");
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $currentTime = time();
            $timeFrom = $currentTime - 60 * 400;
            $timeTo = $currentTime - 60 * 20;

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

            foreach ($waitQueue as $item) {
                $validTime = $item['valid_time'];
                if (!empty($validTime)) {
                    trans_start();

                    // 添加退款记录
                    $result1 = $this->consultationRefund(
                        $item['con_id'],
                        $item['c_id'],
                        $item['phone_price'],
                        $validTime
                    );

                    // 添加余额
                    $result2 = User::build()->where(['user_id' => $item['c_id'] ])->setInc('money', $result1);

                    // 修改咨询记录已退款
                    $result3 = Consultation::update([ 'is_refunded' => 'yes' ], [ 'con_id' => $item['con_id'] ]);
                    $result4 = Appointment::update(['status' => 'end'], [ 'ap_id' => $item['ap_id'] ]);

                    if ($result1 === false || $result2 === false || $result3 === false || $result4 === false) {
                        trans_rollback();
                        every_log("timer_call_finish", $item, "refund error");
                    } else {
                        trans_commit();
                    }
                }
            }

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            ex_log($e, 'ex_timer');
            exit();
        }
    }

    private function consultationRefund($conId, $cid, $price, $leftSecond)
    {
        try {
            $minute = $leftSecond / 60 + 1;
            $refundMinute = (intval($minute / 5)) * 5;

            $refund = new Refund();
            $data = [
                'con_id' => $conId,
                'c_id' => $cid,
                'money' => $price * $refundMinute,
                'price' => $price,
                'left_time' => $leftSecond,
            ];
            $refund->data($data);
            $refund->save();

            if (empty($refund->rf_id)) {
                return false;
            }

            return $data['money'];
        } catch (\Exception $e) {
            ex_log($e);
            return false;
        }
    }

}
