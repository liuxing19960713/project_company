<?php
//文章管理表
namespace app\Admin\model;

use think\Model;
use think\Db;
class ArticleModel extends Model
{
    //设置当前模型对应的完整数据表名称
    protected $table = "yyb_news";

    public function getIscom($value)
    {
        $status = [0=>'默认',1=>'推荐'];
        return $status[$value];
    }

    public function getShown($value)
    {
        $shown = [0=>'隐藏',1=>'显示'];
        return $shown[$value];
    }


    //获取全部数据
    public function getAlldate()
    {
      	$field   = "n.istop,n.id,n.cate,n.iscom,n.ptitle,n.title,n.keywords,n.pic,n.shown,ns.title as ntitle,n.addtime";
        $k = input('get.title');
        // $t = input('get.tt');

    
  
        $info = db("news") -> alias("n") 
                                  -> join('yyb_newssort ns', 'n.cate = ns.id')
                                  -> field($field)
                                  -> where('keywords','like','%'.$k.'%')
                                  // -> whereOR('n.title','like','%'.$t.'%')
                                  -> order('shown desc')
                                  -> order('iscom desc') 
                                  -> order('istop desc')
                                  -> order('id desc') 
                                  -> paginate(10);
       
    	
        return $info;
    }
}
