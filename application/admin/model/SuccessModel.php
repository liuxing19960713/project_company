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

class SuccessModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_article_success';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getSuccessByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('as_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return SuccessModel::where($dataMap)->count();
    }
	

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllSuccess($where)
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
                return msg(1, url('success/index'), '添加文章成功');
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

            $result = $this->save($param, ['as_id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('success/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneSuccess($id)
    {
        return $this->where('as_id', $id)->find();
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delSuccess($id)
    {
        try{

            $this->where('as_id', $id)->delete();
            return msg(1, '', '删除文章成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}