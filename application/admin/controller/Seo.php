<?php
//首页SEO内容
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
//导入模型
use app\Admin\model\SeoModel;
class Seo extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //总数、数据、
        $count = count($seo = SeoModel::column("id,title,keywords,addtime"));
        $title = "seo列表";
        //分配数据
        return $this -> fetch("Seo/index",['title'=>$title,'count'=>$count,'seo'=>$seo]);
        
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
        return $this -> fetch("Seo/set",['title'=>'SEO添加']);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $res               = $request -> param();
        $res['addtime']    = date('Y-m-d H:i:s',time());
        //调用模型
        $seo               = new SeoModel($res);
        // 过滤post数组中的非数据表字段数据
        $result = $seo -> allowField(true) -> save();
        if ($result) {
            return 'ok';
        } else {
            return 'error';
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
        //
        $info   = SeoModel::where('id',$id)->find(); 
        $title  = "SEO编辑";
        return $this -> fetch("Seo/alert",['subject'=>$title,'info'=>$info]);
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
        $res    = $request->except(['id','/admin/Seo/update']);
        $seo    = new SeoModel;
        $result = $seo-> where('id', $id)
                      -> update($res);
        if ($result) {
            return "编辑ok";
        } else {
            return "编辑error";
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
        
        $result = SeoModel::destroy($id,true);
        if ($result) {
            return "操作ok";
        } else {
            return "操作error";
        }
    }
}
