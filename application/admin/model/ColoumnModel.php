<?php

namespace app\Admin\model;

use think\Model;

class ColoumnModel extends Model
{
    //
     // 设置当前模型对应的完整数据表名称
    protected $table = 'yyb_newssort';


    public function getshown($value)
    {
        $status = [1=>'显示',0=>'隐藏'];
        return $status[$value];
    }
}
