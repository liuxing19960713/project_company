<?php
//标签管理
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Label extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //总数、数据、分页
        $count = count($info = db("label")->paginate(5));
        $title = "标签列表";
        return $this->fetch("Label/index",['title'=>$title,"count"=>$count,'info'=>$info]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $title = "标签添加";
        return $this->fetch("Label/create",['title'=>$title]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
        $data               =  $request->param();
        $data['addtime']    =  date("Y-m-d H:i:s",time());
        $res                =  db("label")->insert($data);
        if ($res) {
            return 'ok';
        }else{
            return "error";
        }
        // var_dump($data);

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        $info = db("label")->where("id",$id)->find();
        return $this->fetch("Label/alert",['title'=>'标签编辑','info'=>$info]);
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        
       $res     = $request->except(['id','/admin/label/update']);
       $resd    = db("label")->where("id",$id)->update($res);
       if ($resd) {
           return "编辑oK";
       }else{
           return "编辑false";
       }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        $data    = db('label')->delete($id);
        
        if($data) {
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作完成'];
        }
    }
}
