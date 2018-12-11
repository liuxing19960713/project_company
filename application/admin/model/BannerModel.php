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

class BannerModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_banner_user';

    /**
     * 根据搜索条件获取角色列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getBannerByWhere($where, $offset, $limit)
    {

        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的角色数量
     * @param $where
     */
    public function getAllBanner($where)
    {
        return $this->where($where)->count();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return BannerModel::where($dataMap)->count();
    }
	
	public function userInfo()
    {
        return $this->hasOne('UserModel', 'user_id', 'user_id')->field(['nick_name']);
    }
	
	public function postInfo()
    {
        return $this->hasOne('PostModel', 'post_id', 'banner_type_id')->field(['title', 'content']);
    }
	
	public function commentInfo()
    {
        return $this->hasOne('CommentModel', 'comment_id', 'banner_type_id')->field(['content']);
    }

	/**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneBanner($id)
    {
        return $this->where('bau_id', $id)->find();
    }
	
	
	public function edit($param)
    {
        try{

            $result = $this->save($param, ['bau_id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('banner/index'), '修改成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
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
                return msg(1, url('banner/index'), '添加成功');
            }
        }catch (\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }


    /**
     * 删除角色
     * @param $id
     */
    public function delBanner($id,$filter)
    {
        try{
			if($filter==2){
				db('banner_doctor')->where('bad_id',$id)->delete();
            	//$this->where('bau_id', $id)->delete();
			}else if($filter==3){
				db('banner_other')->where('id',$id)->delete();
			}else{
            	$this->where('bau_id', $id)->delete();
			}
            return msg(1, '', '删除成功');

        }catch(PDOException $e){
            return msg(-1, '', $e->getMessage());
        }
    }


    
}