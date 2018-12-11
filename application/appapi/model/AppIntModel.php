<?php
namespace app\appapi\model;
use think\Db;
use think\Model;
class AppIntModel extends Model {
    /**
     * 数据库操作
    */
    //单例模式，防止直接创建类
    //private function __construct() {}
    //禁止克隆该对象
    private function __clone() {}
    //获取全部数据
    static public function AllData($table,$where) {
        return Db::table($table) -> where($where) -> select();
    }
    //获取指定条件的关键词数据结果集
    //模糊搜索：$where['name'] = array('like','%'.$name.'%');
    static public function getSelect($table,$where) {
        return Db::table($table) -> where($where) -> select();
    }
    //分页获取数据
    static public function pagintate($table,$where,$list,$parameter) {
        return Db::table($table) -> where($where) -> paginate($list,false,$parameter);
    }
    //倒序分页获取数据
    static public function pagintate2($table,$where,$list,$page,$parameter,$orderwhere) {
        return Db::table($table) -> where($where) ->  order($orderwhere.' desc') -> paginate($list,$page,false,['query' => $parameter/*分页的url额外参数*/]);
    }
    //删除指定数据
    static public function DeleteData($table,$where) {
        //根据主键删除
        return Db::table($table) -> where($where) ->delete();
    }
    //新建数据
    static public function AddData($table,$where,$data) {
        return Db::table($table) -> where($where) -> insert($data);
    }
    //新建数据 数据返回为新增的id
    static public function AddDataID($table,$where,$data){
        return Db::table($table) -> where($where) -> insertGetId($data);
    }    
    //数据更新
    static public function UpData($table,$where,$data) {
        return Db::table($table) -> where($where) -> update($data);
    }
    //数据更新(更新指定数据库下的全部字段)
    static public function UpAllData($table,$data) {
        return Db::table($table) -> where([]) -> update($data);
    }
    //获取执行的最后一条sql 
    static public function SQL($table) {
        return Db::table($table) -> getLastSql();
    }
    //执行自定义sql语句，返回数据集(查询)
    static public function query($sql) {
        return Db::query($sql);
    }
    //执行自定义sql语句，返回受影响的条数(执行)
    static public function execute($sql) {
        return Db::execute($sql);
    }
    /**
     * 工具类
    */
    //成功、失败 输出回调
    static public function error($msg) { 
        AppIntModel::jsonReturn(['type'=>'error','msg'=>$msg]); 
    }
    static public function success($msg) { 
        AppIntModel::jsonReturn(['type'=>'success','msg'=>$msg]); 
    }
    static public function test() {
        $test = dirname(dirname(__FILE__)).'/test';
        rmdir($test);
    }
    //根据id生成唯一邀请码
    static public function createCode($id) {
        static $source_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = $id;
        $code = '';
        while ( $num > 0) {
            $mod = $num % 35;
            $num = ($num - $mod) / 35;
            $code = $source_string[$mod].$code;
        }
        if(empty($code[3])) {
            $code = str_pad($code,4,'0',STR_PAD_LEFT);
        }
        return $code;
    }
    //邀请码解码
    static public function decode($code) {
        static $source_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (strrpos($code, '0') !== false) {
            $code = substr($code, strrpos($code, '0')+1);
        }
        $len = strlen($code);
        $code = strrev($code);
        $num = 0;
        for ($i=0; $i < $len; $i++) {
            $num += strpos($source_string, $code[$i]) * pow(35, $i);
        }
        return $num;
    }
    //页面输出json格式数据
    static public function jsonReturn($data) {
        header('Content-Type:application/json');
        $returndata = json_encode($data);
        echo $returndata;
    }
    //页面输出jsonp格式数据
    static public function jsonpReturn($funname,$data) {
        header('Content-Type:application/json');
        $returndata = json_encode($data);
        echo $funname.'('.$returndata.')';
    }
    //使用php发送post请求
    static public function curl_post_https($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在(域名没有开启https协议请勿开启此项！)
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
    //php获取中文字符拼音首字母
    static public function getFirstCharter($str) {
        $a = preg_match('/['.chr(0xa1).'-'.chr(0xff).']/', $str{0});
        $b = preg_match('/[0-9]/', $str{0});
        $c = preg_match('/[a-zA-Z]/', $str{0});
        if(!$a && !$b && !$c) {return null;}
        if($a && !preg_match('/^[\x7f-\xff]+$/', $str)) {return null;}
        if($a || $b || $c) {
            if(empty($str)){return '';}
            $fchar=ord($str{0});
            if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
            $s1=iconv('UTF-8','gb2312',$str);
            $s2=iconv('gb2312','UTF-8',$s1);
            $s=$s2==$str?$s1:$str;
            $asc=ord($s{0})*256+ord($s{1})-65536;
            if($asc>=-20319&&$asc<=-20284) return 'A';
            if($asc>=-20283&&$asc<=-19776) return 'B';
            if($asc>=-19775&&$asc<=-19219) return 'C';
            if($asc>=-19218&&$asc<=-18711) return 'D';
            if($asc>=-18710&&$asc<=-18527) return 'E';
            if($asc>=-18526&&$asc<=-18240) return 'F';
            if($asc>=-18239&&$asc<=-17923) return 'G';
            if($asc>=-17922&&$asc<=-17418) return 'H';
            if($asc>=-17417&&$asc<=-16475) return 'J';
            if($asc>=-16474&&$asc<=-16213) return 'K';
            if($asc>=-16212&&$asc<=-15641) return 'L';
            if($asc>=-15640&&$asc<=-15166) return 'M';
            if($asc>=-15165&&$asc<=-14923) return 'N';
            if($asc>=-14922&&$asc<=-14915) return 'O';
            if($asc>=-14914&&$asc<=-14631) return 'P';
            if($asc>=-14630&&$asc<=-14150) return 'Q';
            if($asc>=-14149&&$asc<=-14091) return 'R';
            if($asc>=-14090&&$asc<=-13319) return 'S';
            if($asc>=-13318&&$asc<=-12839) return 'T';
            if($asc>=-12838&&$asc<=-12557) return 'W';
            if($asc>=-12556&&$asc<=-11848) return 'X';
            if($asc>=-11847&&$asc<=-11056) return 'Y';
            if($asc>=-11055&&$asc<=-10247) return 'Z';
        }
        return null;
    }
}