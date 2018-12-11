<?php
/**
 * Created by PhpStorm.
 * User: fio
 */
namespace app\common\exception;

use Exception;
use think\App;
use think\exception\Handle;
use think\exception\HttpException;
use think\console\Output;

class DarkKnightHandle extends Handle
{
    public function report(Exception $exception)
    {
        parent::report($exception);
        $this->watcher($exception);
    }

    private function watcher(Exception $e)
    {
        try {
            $url = 'https://watcher.dankal.cn/api/Index/storeTrace';
            $appKey = '41e57a57699c5a7e322b2bb1c2a9c224';
            $appSecret = '07cf41279873f5657a425ad397834ed9';
            $postData = [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'is_push' => '1',

                'class_name'    => get_class($e),
                'file_name'    => $e->getFile(),
                'file_line'    => $e->getLine(),
                'error_message' => $this->getMessage($e),
                'error_code'    => $this->getCode($e),

                'source_from' => $this->getSourceCode($e)['first'],
                'source_array' => $this->getSourceCode($e)['source'],

                'call_stack_array'   => $e->getTrace(),
                'data_array'   => $this->getExtendData($e),
                'variables_array'  => [
                    'GET Data'              => $_GET,
                    'POST Data'             => $_POST,
                    'Files'                 => $_FILES,
                    'Cookies'               => $_COOKIE,
                    'Session'               => isset($_SESSION) ? $_SESSION : [],
                    'Server/Request Data'   => $_SERVER,
                    'Environment Variables' => $_ENV,
                ],
            ];

            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($postData));
            $result = curl_exec($curlHandle);
            curl_close($curlHandle);
            return $result;
        } catch (\Exception $e) {
            $e->getMessage();
            return false;
        }
    }
}
