<?php
namespace app\doctor\ controller;

use app\index\controller\Base;
use think\Db;
use think\Model;

class Version extends Base
{
    //版本更新

    //todo:: 时间表数据处理  待删除
    public function test(){
//        $arr = array();
//        for($i = 8;$i<=22;$i++){
//            $arr[$i*100] = "yes";
//            $arr[$i*100+30] = "yes";
//        }
//        customSerialize($arr);
//        var_dump(array_keys($arr));
//        var_dump(implode(",",array_values($arr)));
//        echo "<br />";
//        var_dump(array_combine(array_keys($arr),array_values($arr)));
        $array['a'] = '0';
        $array['b'] = '0';
        $array['c'] = '0';
        $array['d'] = '0';
        $array['e'] = '0';
        echo customSerialize($array);
        $time = date("Hi",time()-3600*10);
        echo $time;
        //var_dump(json_encode($arr));
    }

}
