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

class NewsModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_article_newest';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getNewsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('an_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return NewsModel::where($dataMap)->count();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllNews($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加文章
     * @param $param
     */
    public function addNews($param)
    {
        try{
            $result = $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('news/index'), '添加文章成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑文章信息
     * @param $param
     */
    public function editNews($param)
    {
        try{

            $result = $this->save($param, ['an_id' => $param['an_id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('news/index'), '编辑文章成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }
	
	
    public function AlterData($where,$updata) {
        return $this -> where($where) -> update($updata);
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneNews($id)
    {
        return $this->where('an_id', $id)->find();
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delNews($id)
    {
        try{

            $this->where('an_id', $id)->delete();
            return msg(1, '', '删除文章成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}