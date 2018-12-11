<?php
namespace app\test\model;
use think\Model;
class TestModel extends Model {
    // 确定链接表名
    protected $table = 'yyb_doctor';
    //获取数据条数
    public function count($where) {
        return $this -> where($where) -> select();
    }
    //获取全部数据
    public function getAll() {
        return $this -> select();
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
    //删除指定数据
    public function DeleteData($where) {
        //根据主键删除
        return $this -> where($where) ->delete();
    }
    //新建医生数据
    public function AddData($where,$data) {
        return $this -> where($where) -> insert($data);
    }
    //获取执行的最后一条sql
    public function SQL() {
        return $this -> getLastSql();
    } 
}