<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
define('G_HTTP', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
define('G_HTTP_HOST', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
defined('G_HOST') or define('G_HOST', G_HTTP . G_HTTP_HOST);

// example: http(s)://example.com/project_name/public
defined('PUBLIC_PATH') or define('PUBLIC_PATH', dirname(G_HTTP . G_HTTP_HOST . $_SERVER['SCRIPT_NAME']));

// example: http(s)://example.com/project_name/public/index.php/admin/
defined('ADMIN_PATH') or define('ADMIN_PATH', PUBLIC_PATH . '/index.php/admin/');

define('SQL_SEPARATOR', '|');
define('ADMIN_TOKEN', md5("YYH_ADMIN_TOKEN"));


/**
 *  检查字段是否存在
 * @param $name
 * @param string $defaultValue
 * @return string
 */
function get_post_value($name, $defaultValue = '')
{
    $data = array_merge($_GET, $_POST);
    return ((isset($data[$name])) && ($data[$name] !== '')) ? $data[$name] : $defaultValue;
}

function get_json_input($key = null)
{
    $inputs = \think\Request::instance()->param();
    if ($key) {
        return isset($inputs[$key]) ? $inputs[$key] : null;
    } else {
        return \think\Request::instance()->param();
    }
}

function get_json_inputs($keys = [])
{
    if ($keys) {
        $result = [];
        $request = \think\Request::instance()->param();
        foreach ($keys as $key) {
            $result[$key] = isset($request[$key]) ? $request[$key] : null;
        }
        return $result;
    } else {
        return \think\Request::instance()->param();
    }
}

function get_rank($content, $rankArray, $textArray)
{
    $target = null;
    foreach ($rankArray as $key => $value) {
        if ($content <= $value) {
            $target = $key;
            break;
        }
    }
    if ($target === null) {
        $target = count($rankArray);
    }
    $result = $textArray[$target];
    unset($target);
    return $result;
}

function format_mac_address($macAddress)
{
    if (is_string($macAddress) && strlen($macAddress) == 12) {
        return strtolower('0' . implode('0', str_split($macAddress)));
    }
    return $macAddress;
}

function get_random_string($length = 6)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g",
        "h", "i", "j", "k", "l", "m", "n",
        "o", "p", "q", "r", "s", "t",
        "u", "v", "w", "x", "y", "z",
        "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N",
        "O", "P", "Q", "R", "S", "T",
        "U", "V", "W", "X", "Y", "Z",
        "0", "1", "2", "3", "4",
        "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);
    $output = "";
    for ($i = 0; $i < $length; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

function get_random_num_str($length = 4, $withZero = false, $canBeRepeated = true)
{
    $baseChars = "123456789";
    if ($withZero) {
        $baseChars .= "0";
    }
    if ($canBeRepeated || $length > 10) {
        $baseChars = str_repeat($baseChars, $length);
    }
    $chars = str_shuffle($baseChars);
    $output = substr($chars, 0, $length);
    return $output;
}

function getCurl($url)
{
    try {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $result;
    } catch (\Exception $e) {
        return null;
    }
}

function postCurl($url, $postData)
{
    try {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $result;
    } catch (\Exception $e) {
        return null;
    }
}

function log_file($content, $title = 'LOG', $filename = 'log_file')
{
    try {
        $titleShow = (strlen($title) > 30) ? substr($title, 0, 27) . '...' : $title;
        $spaceNum = (66 - strlen($titleShow)) / 2;
        $titleShow = '=' . str_repeat(' ', intval($spaceNum)) . $titleShow . str_repeat(' ', ceil($spaceNum)) . '=';

        $time = date('Y-m-d H:i:s');
        $content = var_export($content, true);


        $logContent = <<<EOT
====================================================================
{$titleShow}
====================================================================
time:     {$time}
title:    {$title}
--------------------------------------------------------------------
content:  \n{$content}\n\n\n
EOT;

        $logPath = LOG_PATH;
        $logName = $filename . date('Ymd') . '.log';
        if (!is_dir($logPath)) {
            mkdir($logPath);
        }
        $logFile = fopen($logPath . $logName, "a");
        fwrite($logFile, $logContent);
        fclose($logFile);
    } catch (\Exception $e) {
        // do nothing
    }
}

/**
 * 获取随机位数数字
 *
 * @param integer $len
 *            长度
 * @return string
 */
function rand_string($len = 4)
{
    $chars = str_repeat('0123456789', $len);
    $chars = str_shuffle($chars);
    $str = substr($chars, 0, $len);
    return $str;
}

function get_rand_char($length)
{
    $str = null;
    $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

function get_token()
{
    return \think\Request::instance()->header('token');
}

function get_device()
{
    return \think\Request::instance()->header('device');
}

function is_starts_with($str, $pattern)
{
    return strpos($str, $pattern) === 0;
}

function is_ends_with($str, $pattern)
{
    $length = strlen($pattern);
    if ($length == 0) {
        return true;
    }

    return (substr($str, -$length) === $pattern);
}

function is_empty_str($str)
{
    return (preg_replace('/\s/', '', $str)) === '';
}

function get_root_with_domain()
{
    $request = \think\Request::instance();
    return $request->root(true);
}

function trans_start()
{
    \think\Db::startTrans();
}

function trans_commit()
{
    \think\Db::commit();
}

function trans_rollback()
{
    \think\Db::rollback();
}

function validate_is($value, $rule)
{
    return \think\Validate::is($value, $rule);
}

/**
 * 验证正数
 * @param $value
 * @param $rule
 *
 * @return bool
 */
function validate_number($value)
{
    return \think\Validate::is($value, 'number') && $value >= 0;
}

function validate_regex($value, $rule)
{
    return \think\Validate::regex($value, $rule);
}

function validate_words($value, array $words)
{
    $reg = implode('|', $words);
    $rule = "/^({$reg})$/";
    return \think\Validate::regex($value, $rule);
}

function empty_without_zero($value)
{
    return empty($value) && ($value !== 0 && $value !== '0' && $value !== 0.0);
}

function ex_log(\Exception $e, $prefixName = 'e')
{
    try {
        $eTime = date('Y-m-d H:i:s');
        $eFile = $e->getFile();
        $eLine = $e->getLine();
        $eMsg = $e->getMessage();
        $eTrace = $e->getTraceAsString();

        $logPath = ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR;
        $logName = $prefixName . date('Ymd') . '.log';
        $logContent = <<<EOT
----------\n
time  : {$eTime}
file  : {$eFile}
line  : {$eLine}
msg   : {$eMsg}
trace : \n{$eTrace}\n\n
EOT;

        if (!is_dir($logPath)) {
            mkdir($logPath);
        }
        $logFile = fopen($logPath . $logName, "a");
        fwrite($logFile, $logContent);
        fclose($logFile);
    } catch (\Exception $e) {
        // do nothing
    }

}

function every_log($filename, $content, $title = 'Log')
{
    try {
        $titleShow = (strlen($title) > 30) ? substr($title, 0, 27) . '...' : $title;
        $spaceNum = (66 - strlen($titleShow)) / 2;
        $titleShow = '=' . str_repeat(' ', intval($spaceNum)) . $titleShow . str_repeat(' ', ceil($spaceNum)) . '=';

        $trace = debug_backtrace();
        $time = date('Y-m-d H:i:s');
        $file = $trace[0]['file'];
        $line = $trace[0]['line'];
        $function = $trace[1]['function'];
        $content = var_export($content, true);

        $logContent = <<<EOT
====================================================================
{$titleShow}
====================================================================
time:     {$time}
file:     {$file}
line:     {$line}
function: {$function}
title:    {$title}
--------------------------------------------------------------------
content:  \n{$content}\n\n\n
EOT;

        $logPath = ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR;
        $logName = $filename . date('Ymd') . '.log';
        if (!is_dir($logPath)) {
            mkdir($logPath);
        }
        $logFile = fopen($logPath . $logName, "a");
        fwrite($logFile, $logContent);
        fclose($logFile);
    } catch (\Exception $e) {
        // do nothing
    }
}

function safe_str($str)
{
    $unsafeChars = [
        "`", "<", ">", "\\", "/",
    ];
    foreach ($unsafeChars as $item) {
        $str = str_replace($item, "", $str);
    }
    return $str;
}

function replace_separator($str, $search = ',', $replace = SQL_SEPARATOR)
{
    return str_replace($search, $replace, $str);
}

/**
 * 自定义结构序列化, 把字典转换为 key:value|key:value 形式
 * @param array $data
 *
 * @return string
 */
function customSerialize(Array $data)
{
    if (empty($data)) {
        return "";
    }
    $temp = [];
    foreach ($data as $key => $value) {
        $temp[] = $key . ":" . $value;
    }
    $result = implode(SQL_SEPARATOR, $temp);
    return $result;
}

/**
 * 自定义结构反序列化, 把 key:value|key:value 形式的字符串转换为字典
 * @param $string
 *
 * @return array
 */
function customUnserialize($string)
{
    if (empty($string)) {
        return [];
    }
    $dataArr = explode('|', $string);
    $result = [];
    foreach ($dataArr as $item) {
        $temp = explode(':', $item);
        $result[$temp[0]] = $temp[1];
    }

    return $result;
}

function get_post_raw_data()
{
    return file_get_contents("php://input");
}

function json_unicode_encode($value)
{
    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

function get_time_point($time)
{
    $now_day = date("Ymd", time());
    $day = date("Ymd", $time);
    $date['hour'] = intval(date("Hi", $time));

    if ($day == $now_day) {
        $date['day'] = "today";
    } else {
        if ($day == $now_day + 1) {
            $date['day'] = "tomorrow";
        } else {
            return false;
        }
    }

    return $date;
}

function total_time($total_time){
    $dtime= $total_time;
    $timedata = '';
    $h = floor($dtime%(3600*24)/3600);
    if ($h) {
        $timedata .= $h . "小时 ";
    }
    $m = floor($dtime%(3600*24)%3600/60);
    if ($m) {
        $timedata .= $m . "分钟 ";
    }
    return $timedata;
}