<?php
/**
 * 链接模块
 */
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
class Link extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //总数、数据、分页
        $count = count($info = DB::table("yyb_links")->paginate(10));
        $title = "友情链接";
        return $this->fetch("Link/index",['info'=>$info,'title'=>$title,'count'=>$count]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        // echo '11';
        return $this->fetch("Link/create");
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
        $data = $request->param();
        $data['sort'] = 0;
        if (DB::table("yyb_links")->insert($data)) {
            return $this->success("ok","Link/index");
        }else{
            return $this->error("error","Link/create");
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
        
        $info = DB::table("yyb_links")->where("id",$id)->find();
        return $this->fetch("Link/edit",['info'=>$info]);
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
        $data         = $request->except(['id']);
        $data['sort'] = 0;
        $succ  = DB::table("yyb_links")->where("id",$id)->update($data);
        if ($succ) {
            return $this->success("ok","about/index");
        }else{
            return $this->success("error","about/edit/$id");
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
        if (DB::table("yyb_links")->where('id',$id)->delete()){
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作失败'];
        }         
    }
}
