<?php
/**
 * 专题管理
 */
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Subject extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $title = "专题管理";
        $count = count($info = DB::table("yyb_subject")->paginate(5));
        return $this->fetch("Subject/index",["count"=>$count,"title"=>$title,"info"=>$info]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        $title = "添加专题内容";
        return $this->fetch("Subject/add",['title'=>$title]);
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
        $data = $request -> param();
        $data['addtime']    =   date('Y-m-d H:i:s',time());

        $res  = Db::table("yyb_subject") ->insert($data);

        if ($res) {
            return '添加ok';
        }else{
            return '添加false';
        }

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
        // var_dump($id);
        $info   = DB::table("yyb_subject")->where("id",$id)->find();
        $title  = "专题编辑";
        // var_dump($info);
        return $this->fetch("Subject/alter",['info'=>$info,'title'=>$title]);
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
        //
       $data = $request-> except(['id']);
       // var_dump($data);
       $res  = Db::table("yyb_subject") ->where("id",$id) ->update($data);
       if ($res) {
           return 'ok';
       }else{
           return 'error';
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
        $data    = db('subject')->delete($id);
        
        if($data) {
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作完成'];
        }

    }
}
