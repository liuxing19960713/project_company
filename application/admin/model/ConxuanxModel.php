<?php

namespace app\admin\model;

use think\Model;
//APP搜索关键词管理Model
class ConxuanxModel extends Model {
    //连接的数据库名
    protected $table = 'yyb_option';

    //获取所有关键词数据结果集
    public function getAll() {
        //获取多条数据
        // return $this -> where([]) -> paginate($list);
        return $this -> where([]) -> select();
    }

    //获取指定条件的关键词数据结果集
    public function getSelect($where/*字典格式*/) {
        return $this -> where($where) -> select();
    }

    //新建关键词数据
    public function AddData($data/*使用array数组格式*/) {
        return $this -> insert($data);
    }
    
    //删除指定数据
    public function DeleteData($where) {
        //根据主键删除
        return $this -> where($where) -> delete();
    }

    //修改关键词字段
    public function AlterData($where,$updata) {
        return $this -> where($where) -> update($updata);
    }
}