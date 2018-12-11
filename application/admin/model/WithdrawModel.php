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
use app\admin\model\MessageDoctor;
use app\admin\model\MessageUser;

class WithdrawModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_withdrawal';
	
	
	/**
     * @return Withdraw
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
    public function getWithdrawByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('wd_id desc')->select();
    }
	
	
	public static function whereCount($dataMap = [])
    {
        return WithdrawModel::where($dataMap)->count();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllWithdraw($where)
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
            $result = $this->validate('WithdrawValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('withdraw/index'), '添加文章成功');
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

            $result = $this->save($param, ['wd_id' => $param['id']]);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('withdraw/index'), '编辑成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    
    public function userInfo()
    {
        return $this->hasOne('UserModel', 'user_id', 'user_id')->field(['nick_name', 'mobile']);
    }
	
	
	public function doctorInfo()
    {
        return $this->hasOne('DoctorModel', 'doctor_id', 'user_id')->field(['nick_name', 'mobile']);
    }
	
	
	/**
     * 根据文章的id 获取文章的信息
     * @param $id
     */

	public function getOneUser($id)
    {
        return $this->where('wd_id', $id)->find();
    }
	

    /**
     * 删除文章
     * @param $id
     */
    public function delWithdraw($id)
    {
        try{

            $this->where('wd_id', $id)->delete();
            return msg(1, '', '删除成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}