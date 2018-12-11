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

class ConsultationModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_consultation';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getConsultationByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('con_id desc')->select();
    }
	
	/**
     * @return Consultation
     */
    public static function build()
    {
        return new self();
    }

	
	
	public static function whereCount($dataMap = [])
    {
        return ConsultationModel::where($dataMap)->count();
    }
	

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllConsultation($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneConsultation($id)
    {
        return $this->where('con_id', $id)->find();
    }
	
	

    /**
     * 删除文章
     * @param $id
     */
    public function delConsultation($id)
    {
        try{

            $this->where('con_id', $id)->delete();
            return msg(1, '', '删除文章成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}