<?php
//文章管理模块
namespace app\Admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\Admin\model\ArticleModel;
class Article extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        if (!input('?get.id')) {
            //当无切换栏目数据
            $article = new ArticleModel;
            $info    = $article->getAlldate();
            $count   = count($info);       
        } else {
             //获取的字段
            //切换栏目数据列表

            $id = $request->param('id');
            $field  = "n.istop,n.id,n.cate,n.iscom,n.ptitle,n.title,n.keywords,n.pic,n.shown,ns.title as ntitle,n.addtime";
            $count  = count($info =  db("news") 
                                  -> alias("n") 
                                  -> join('yyb_newssort ns', 'n.cate = ns.id')
                                  -> field($field)

                                  -> where("ns.id",$id)
                                  // -> where('n.keywords', 'like', $keywords)
                                  -> order('shown desc')
                                  -> order('iscom desc') 
                                  -> order('istop desc')
                                  -> order('id desc') 
                                  -> paginate(10));
       
        }
        //栏目数据
        $cate   = db("newssort") -> field("title,id") -> select();
        $title  = "文章列表";
        return $this -> fetch("Article/index",['info'=>$info,'title'=>$title,'count'=>$count,'cate'=>$cate]);
       
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */

    public function create()
    {
        //栏目数据
        $cate   = db("newssort") -> select();
        //添加标签数据
        $label  = db("label") -> select();
        $title  = "文章添加";
        return $this -> fetch("Article/create",['cate'=>$cate,'title'=>$title,'label'=>$label]);
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
        $res    = $request -> param();
        // var_dump($res);die;
        $result = db("news") -> insert($res);
        if ($result) {
             return "ok";
         } else {
             return "error"; 
         }
        // var_dump($res);

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        

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
        //栏目数据
        $cate   = db("newssort") -> select();
        $title  = "文章编辑"; 
        //数据
        $field  = "n.id,n.cate,n.iscom,n.ptitle,n.title,n.keywords,n.pic,n.shown,ns.title as ntitle,n.addtime,n.label_id,n.content";
        $label  = db("label") -> select();
        $list   = db("news") -> alias("n") 
                             -> join("yyb_newssort ns","n.cate = ns.id") 
                             -> field($field)
                             -> where("n.id",$id)
                             -> find();
        // var_dump($list['label_id']);die;
        $arr = explode(",", $list['label_id']);
        // $pop = array_pop($arr);
        // var_dump($arr);die;
        $ll  = array_filter($arr);
        // var_dump($ll);die;
        
        return $this->fetch("Article/alert",['cate'=>$cate,'list'=>$list,'title'=>$title,'label'=>$label,'ll'=>$ll ]);

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
        $info = db("news") -> where("id",$id) -> find();
        $res  = $request -> except(['id']);
        
        if ($res['pic'] == "") {
            $res['pic'] = $info['pic'];
        }
        if ($res['label_id'] =="") {
            $res['label_id'] = $info['label_id'];
        }
        $result = db("news") -> where('id', $id)
                             -> update($res);
        if ($result) {
            return "更新ok";
        } else {
            return "更新false";
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
       $res = ArticleModel::where('id',$id)->delete();

       if ($res) {
           return "操作ok";
       } else {
           return "操作error";
       }

    }

    /**
     * 批量删除
     */
    public function batchdel()
    {
        $request = Request();
        // 只获取id参数
        $res     = Request::instance()->only(['id'],'get');
        $id      = $res['id'];
        $info    = db("news") -> where("id in($id)")->delete();
        if ($info) {
            return '1';
        } 
    }

    public function changShown(Request $request,$id)
    {
      $res =  ArticleModel::get($id);
      if ($res['shown'] == 1) {
        ArticleModel::update(['id' => $id, 'shown' => '0']);
        return "更改为隐藏";
      } else {
        ArticleModel::update(['id' => $id, 'shown' => '1']);
        return "已设置显示";
      }
    }


    //设置文章置顶
    public function isTop(Request $request,$id)
    {
        $info = ArticleModel::get($id);
        if ($info['istop'] == 0) {
            ArticleModel::update(['id' => $id, 'istop' => '1']);
            return "下架";
        } else {
            ArticleModel::update(['id' => $id, 'istop' => '0']);
            return "上架";
        }
    }
}
