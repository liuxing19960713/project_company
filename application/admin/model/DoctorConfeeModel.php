<?php
namespace app\admin\model;
use think\Model;
class DoctorConfeeModel extends Model{
    protected $table = 'yyb_doctor_confee';
    // 数据获取
    public function getSelect($where) {
        return $this -> where($where) -> select();
    }
    //倒序获取数据
    public function getSelect2($where) {
        return $this -> where($where) ->  order('doctor_id desc') -> select();
    }
    // 新建数据
    public function AddData($where,$data) {
        return $this -> where($where) -> insert($data);
    }
    // 数据更新
    public function UpData($where,$data) {
        return $this -> where($where) -> update($data);
    }
}
?>