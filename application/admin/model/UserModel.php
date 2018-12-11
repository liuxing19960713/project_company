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
use app\common\tools\Easemob;

use think\Model;
 
class UserModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_user';
	
	/**
     * @return User
     */
    public static function build()
    {
        return new self();
    }
	

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getUsersByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('user_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return UserModel::where($dataMap)->count();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllUsers($where)
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
				//$usid = 1;
				//return $usid;
                return msg(1, url('user/index'), '添加成功');
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

            $result = $this->save($param, ['user_id' => $param['user_id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('user/index'), '编辑成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneUser($id)
    {
        return $this->where('user_id', $id)->find();
    }
	
	
	
	/**
     * 环信注册
     * @param $userId
     * @return bool
     */
    public static function addEaseMobAccount($userId)
    {
        $easeMob = new Easemob();
        $token = $easeMob->getToken();
        $easemobAccount = $easeMob->regChatUser($token, $userId, 'customer');
        return $easemobAccount;
    }
	
	
	public function AlterData($where,$updata) {
        return $this -> where($where) -> update($updata);
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delUser($id)
    {
        try{

            $this->where('user_id', $id)->delete();
            return msg(1, '', '删除成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}