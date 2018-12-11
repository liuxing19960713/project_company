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

class AdminModel extends Model
{
    // 确定链接表名
    protected $table = 'yyb_admin';

    /**
     * 根据搜索条件获取用户列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getAdminsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('admin_id asc')->select();
    }
	
	public static function whereCount($dataMap = [])
    {
        return AdminModel::where($dataMap)->count();
    }

    /**
     * 根据搜索条件获取所有的用户数量
     * @param $where
     */
    public function getAllAdmins($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 插入管理员信息
     * @param $param
     */
    public function insertAdmin($param)
    {
        try{

            $result =  $this->validate('AdminValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('admin/index'), '添加用户成功');
            }
        }catch(PDOException $e){

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑管理员信息
     * @param $param
     */
    public function editAdmin($param)
    {
        try{

            $result =  $this->validate('AdminValidate')->save($param, ['admin_id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('admin/index'), '编辑用户成功');
            }
        }catch(PDOException $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据管理员id获取角色信息
     * @param $id
     */
    public function getOneAdmin($id)
    {
        return $this->where('admin_id', $id)->find();
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function delAdmin($id)
    {
        try{

            $this->where('admin_id', $id)->delete();
            return msg(1, '', '删除管理员成功');

        }catch( PDOException $e){
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 根据用户名获取管理员信息
     * @param $name
     */
    public function findAdminByName($name)
    {
        return $this->where('account', $name)->find();
    }

    /**
     * 更新管理员状态
     * @param array $param
     */
    public function updateStatus($param = [], $uid)
    {
        try{

            $this->where('admin_id', $uid)->update($param);
            return msg(1, '', 'ok');
        }catch (\Exception $e){

            return msg(-1, '', $e->getMessage());
        }
    }
}