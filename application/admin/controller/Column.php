<?php
/**
 * 栏目模块
 */
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\Admin\model\ColoumnModel;
class Column extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        $count  = count($info = DB::table("yyb_newssort")->paginate(8));

        // var_dump($info);
        return $this->fetch("Column/index",['info'=>$info,'count'=>$count,'title'=>"栏目模块"]);

    }



     /**
     * 调整类别顺序 添加分隔符
     * @author 刘兴
     *  
     * @return   [type]                   [description]
     */
    public function getcates()
    {
        $cate = DB::table("yyb_newssort")->select(DB::raw('*,concat(sortpath,",",id) as paths'))->orderBy('sortpath')->get();
        // 遍历数据
        foreach ($cate as $key => $value) {
            //转为数组
            $arr = explode(',', $value->path);
            // 获取逗号个数
            $len = count($arr)-1;
            // 重复字符串函数
            $cate[$key]->name=str_repeat("--|", $len).$value->name;
        }
        return $cate;
    }



    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $cate = db("newssort") -> select();
        $title= "栏目添加";
        // var_dump($cate);die;
        return $this->fetch("Column/create",['cate'=>$cate,'title'=>$title]);


    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
         
        $data = $request->param();
        $cate = $data['cate'];
        // var_dump($data);

        $data['addtime'] = date("Y-m-d H:i:s",time());
        $id = db('newssort') -> insertGetId($data);
        // var_dump($id);
        $info = db('newssort') -> where("id",$id)->find();
        // var_dump($info);
        $path = '0'.','.$id.','.$cate;
        // var_dump($path);
        $result = db("newssort")->where("id",$id)->update(['sortpath' => $path]);
        if ($result) {
            return "ok";
        } else{
            return "error";
        }

        // $data['']
        // var_dump($data);
        // if (DB::table("yyb_newssort")->insert($data)) {
        //     return $this->success("ok","column/index");
        // }else{
        //     return $this->error("error","column/create");
        // }

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
        $info = DB::table("yyb_newssort") -> where("id",$id) -> find();
        $cate = db("newssort") -> select();
        return $this->fetch("Column/edit",['info'=>$info,'cate'=>$cate]);
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
        // var_dump($id);
        $data   = $request->except(['id']);
        $info   = db("newssort") -> where("id",$id)->find();
        if ($data['cate']  != $info['cate']) {
            $path = '0'.','.$id.','.$data['cate'];
            $data['sortpath'] = $path;
        }
        // var_dump($data);die;
        $result = DB::table("yyb_newssort")->where("id",$id)->update($data);
        // var_dump($id);
        if ($result) {
            return $this->success("ok","Column/index");
        }else{
            return $this->error("error","Column/edit/$id");
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
        // return $id;
        $data    = db('newssort')->delete($id);
        if($data) {
            return ['code'=>1,'message'=>'操作完成'];
        }else{
            return ['code'=>-1,'message'=>'操作完成'];
        }

    }
}
