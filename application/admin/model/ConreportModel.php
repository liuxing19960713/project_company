<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class ConreportModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_consultation_report';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getConreportByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('cr_id desc')->select();
    }
	
	/**
     * @return Conreport
     */
    public static function build()
    {
        return new self();
    }
	
	
	public function userInfo()
    {
        //关联查询单条数据
        return $this->hasOne('UserModel', 'user_id', 'user_id')->field(['nick_name', 'mobile']);
    }

    public function doctorInfo()
    {
        //关联查询多条数据
        return $this->hasOne('DoctorModel', 'doctor_id', 'd_id')->field(['doctor_id','nick_name','title','create_time']);
    }

	
	
	public static function whereCount($dataMap = [])
    {
        return ConreportModel::where($dataMap)->count();
    }
	

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllConreport($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加文章
     * @param $param
     */
    public function add($param)
    {
        try{
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('Conreport/index'), '添加文章成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑文章信息
     * @param $param
     */
    public function edit($param)
    {
        try{

            $result = $this->save($param, ['cr_id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('Conreport/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneConreport($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delConreport($id)
    {
        try{

            $this->where('cr_id', $id)->delete();
            return msg(1, '', '删除文章成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}