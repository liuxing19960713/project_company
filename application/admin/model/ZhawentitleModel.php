<?php

namespace app\admin\model;

use think\Model;
//APP搜索关键词管理Model
class ZhawentitleModel extends Model {
    //连接的数据库名
    protected $table = 'yyb_app_zhawen_title';

    //获取全部数据
    public function AllData($where) {
        return $this -> where($where) -> select();
    }
    //left join双表对照查询
    public function Join($join,$where) {
        return $this -> alias('a') -> join($join) -> where($where) -> select();
    }
    //获取指定条件的关键词数据结果集
    //模糊搜索：$where['name'] = array('like','%'.$name.'%');
    public function getSelect($where) {
        return $this -> where($where) -> select();
    }
    //分页获取数据
    public function pagintate($where,$list,$parameter) {
        return $this -> where($where) -> paginate($list,false,['query' => $parameter/*分页的url额外参数*/]);
    }
    //倒序分页获取数据
    public function pagintate2($where,$list,$parameter,$orderwhere) {
        return $this -> where($where) ->  order($orderwhere.' desc') -> paginate($list,false,['query' => $parameter/*分页的url额外参数*/]);
    }
    //删除指定数据
    public function DeleteData($where) {
        //根据主键删除
        return $this -> where($where) ->delete();
    }
    //新建数据
    public function AddData($where,$data) {
        return $this -> where($where) -> insert($data);
    }
    //数据更新
    public function UpData($where,$data) {
        return $this -> where($where) -> update($data);
    }
    //获取执行的最后一条sql
    public function SQL() {
        return $this -> getLastSql();
    } 
}