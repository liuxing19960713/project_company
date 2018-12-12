<?php
//轮播图表
namespace app\Admin\model;

use think\Model;

class CarouselModel extends Model
{
	 // 设置当前模型对应的完整数据表名称
    protected $table = 'yyb_carousel';

    //自动修改时间
    // protected $autoWriteTimestamp = true;
    // // 定义时间戳字段名
    // protected $createTime = 'create_at';
    // protected $updateTime = 'update_at';
    // //自动写入时间
    // protected $autoWriteTimestamp = 'datetime';
	// // 设置返回数据集的对象名
	// protected $resultSetType = 'collection';
    public function getStatusAttr($value)
    {
        $status = [1=>'显示',0=>'隐藏'];
        return $status[$value];
    }


}
