<?php
/**
 * Created by PhpStorm.
 * User: fio
 */

namespace app\api\command;

use app\common\model\Finance;
use app\common\model\MessageUser;
use app\common\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\Appointment;
use think\Db;

class CloseAppoint extends Command
{
    protected function configure()
    {
        # 注册命令
        $this->setName('close:appoint')->setDescription("取消预约");
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            // 初始化数据
            $defaultReason = '由于医生繁忙，您的预约没有成功，请预约下一个时段';

            // 获取时间
            $current = time();
            $time = $current - 60 * 5; // 5分钟前的时间

            // 获取过期预约列表
            $whereMap = [
                'create_time' => [ '<', $time ],
                'status' => [ '=', 'wait' ],
            ];
            $appointArray = Appointment::build()
                ->where($whereMap)
                ->select();

            print('count: ' . count($appointArray) . "\n");

            $userIdList = [];
            foreach ($appointArray as $item) {
                $userIdList[] = $item['c_id'];
            }

            $userModelMap = User::usersInList($userIdList, [ 'user_id', 'easemob_username']);
            foreach ($appointArray as $item) {
                $item['easemob_username'] = $userModelMap[$item['c_id']]['easemob_username'];
            }

            // 处理过期预约
            foreach ($appointArray as $item) {
                // 预约状态改为no, 用户余额加回去, 订单加上退款
                $updateId = $item['ap_id'];
                $goodsTypeTextArray = [
                    '电话咨询' => 'phone',
                    '视频咨询' => 'video',
                    '私人医生' => 'private',
                ];
                $goodsType = $goodsTypeTextArray[$item['type']];
                trans_start();
                if ($item['type'] == '私人医生') {
                    $sqlStatement = "update yyb_appointment as ap, yyb_user as cu, yyb_order as ord, yyb_service as se
set ap.status = 'no', ap.reason = '{$defaultReason}' ,cu.money = ap.price + cu.money, ord.is_refund = 'yes', ord.refund_time = {$time}, se.status = 'no', se.reason = '{$defaultReason}'
where ap.ap_id = '{$updateId}' AND se.ap_id = ap.ap_id AND ap.c_id = cu.user_id AND ord.goods_type = '{$goodsType}' AND ord.goods_id = se.se_id AND ord.is_refund = 'no'";
                } else {
                    $sqlStatement = "update yyb_appointment as ap, yyb_user as cu, yyb_order as ord
set ap.status = 'no', ap.reason = '{$defaultReason}' , cu.money = ap.price + cu.money, ord.is_refund = 'yes', ord.refund_time = {$time}
where ap.ap_id = '{$updateId}' AND ap.c_id = cu.user_id AND ord.goods_type = '{$goodsType}' AND ord.goods_id = '{$updateId}' AND ord.is_refund = 'no'";
                }
                $is_update = Db::execute($sqlStatement);

                //插入交易记录
                $extra = serialize(array("id"=>$updateId,'type'=> $goodsType));
                $is_finance = Finance::create([
                    'user_id' => $item['c_id'],
                    'user_type' => "customer",
                    'money' => $item['price'],
                    'type' => 'refund',
                    'status' => 'in',
                    'extra' => $extra
                ]);
                $bool = $is_finance && ($is_update !== false);
                if (!$bool) {
                    trans_rollback();
                    print('id:' . $item['ap_id'] . ' fail' . "\n");
                    if (!$is_finance) {
                        print("finance table update fail.\n");
                    }
                    if (!$is_update) {
                        print("{$sqlStatement}\n");
                        print("table update fail.\n");
                    }
                } else {
                    trans_commit();
                    print('id:' . $item['ap_id'] . ' success' . "\n");
                    // 消息数组添加一条内容，用于添加进message表
                    if ($bool !== false) {
                        $extra = [
                            'is_hidden' => false,
                        ];
                        MessageUser::pushMessage(
                            $item['c_id'],
                            $defaultReason,
                            'con',
                            $extra,
                            config('easemob')['admin_username'],
                            $item['easemob_username']
                        );
                    } else {
                        every_log('timer_fail', "update or insert finance fail: \n{$sqlStatement}", "close appoint");
                    }
                }
            }
            return '';
        } catch (\Exception $e) {
            ex_log($e, 'ex_timer');
            print('error:' . $e->getMessage(). "\n");
            exit();
        }
    }
}
