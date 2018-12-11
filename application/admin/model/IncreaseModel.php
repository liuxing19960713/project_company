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

class IncreaseModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_article_increase';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getIncreaseByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('in_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return IncreaseModel::where($dataMap)->count();
    }
	
	
	public function doctorInfo()
    {
        //关联查询多条数据
        return $this->hasOne('DoctorModel', 'doctor_id', 'doctor_id')->field(['nick_name']);
    }
	

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllIncrease($where)
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
                return msg(1, url('increase/index'), '添加文章成功');
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

            $result = $this->save($param, ['in_id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('increase/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneIncrease($id)
    {
        return $this->where('in_id', $id)->find();
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delIncrease($id)
    {
        try{

            $this->where('in_id', $id)->delete();
            return msg(1, '', '删除文章成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}