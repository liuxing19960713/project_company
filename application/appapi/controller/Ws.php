<?php

namespace app\appapi\controller;
use think\worker\Server;
use Workerman\Lib\Timer;
//允许跨域请求该资源
header("Access-Control-Allow-Origin:*");

class Ws extends Server {
    protected $socket = 'websocket://0.0.0.0:2020';
    //protected $socket = 'websocket://localhost:2018';
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data) {
        $connection->id = $data;
        $connection->send($data.'成功连接请求分发器');
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection) {
        echo $connection->id . " 01 " . "\r\n";
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection) {
        echo $connection->id . " 02 " . "\r\n";
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg) {
        echo " error $code $msg\n";
    }

   /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker) {
        // 进程启动后,设置一个每秒运行一次的定时器
        Timer::add(1, function ()use($worker){
            $time_now = time();
            foreach ($worker->connections as $connection) {
                // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $time_now;
                    continue;
                }
                // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                if ($time_now - $connection->lastMessageTime > 86400) {
                    $connection->send('太久没动作，退出登录');
                    $connection->close();
                }
            }
        });
        
        echo $worker->id . "\r\n";
    }
}
