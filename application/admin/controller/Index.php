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
use app\admin\model\NodeModel;
use think\Db;

class Index extends Base
{
    public function index()
    {

        return $this->fetch('/index');
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function indexPage()
    {
        $mysql = Db::query('select VERSION() as version');
        $mysql = isset($mysql[0]['version']) && !empty($mysql[0]['version']) ? $mysql[0]['version'] : 'unknown';
        $programVersion = config('sys')['app_version'] . " [<a href='https://fiochen.me' target='_blank'>fio</a>]";
        $sysInfo = [
            'operating_system' => PHP_OS,
            'operating_environment' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'php_run_mode' => php_sapi_name(),
            'mysql_version' => $mysql,
            'program_version' => $programVersion,
            'update_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time'),
            'disk_free_space' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        ];
        $this->view->assign('sys_info', $sysInfo);
        return $this->fetch('index');
    }
}
