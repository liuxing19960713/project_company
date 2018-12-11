#!/usr/bin/env php
<?php
//加载开启websocket监听服务
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','appapi/Ws');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';