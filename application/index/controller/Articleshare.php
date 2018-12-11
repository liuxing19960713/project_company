<?php
//APP文章分享页
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\admin\model\DoctorModel;
use app\admin\model\AppIntModel;

class Articleshare extends Controller {
    public function index() {
        echo "请选择Tips、ArticleNewest、ArticleIncrease访问方法进行访问";
    }

    //文章分类1
    public function Tips() {
        $model = new AppIntModel('yyb_tips');
        $ArticleId = $_GET['id'];
        // 基础文章信息
        $Data = $model -> getSelect(['tip_id' => $ArticleId]);
        $Title = $Data[0]['title'];
        $ArticleData = [];
        $ArticleData['Title'] = $Data[0]['title'];
        $ArticleData['UpdateTime'] = date('Y-m-d',$Data[0]['create_time']);
        $ArticleData['ViewsCount'] = $Data[0]['views_count'];
        $ArticleData['content'] = $Data[0]['content'];
        $ArticleData['praise'] = $Data[0]['praise'];
        $Classify = "优孕攻略";
        // 评论信息

        //获取一级评论数据
        $where = [];
        // $where['type'] = 'tips';
        // $where['article_id'] = $ArticleId;
        //$where = "'type'='tips' and 'article_id'=".$ArticleId." and hf<>0"
        //$Book1 = $model2 -> getSelect($where);
        //获取二级级评论数据
        $Book1 = [];
        $this -> assign('Title',$Title);
        $this -> assign('Book1',$Book1);
        $this -> assign('ArticleData',$ArticleData);
        $this -> assign('Classify',$Classify);
        return $this -> fetch('article/index');
    }

    //文章分类2
    public function ArticleNewest() {
        $model = new AppIntModel('yyb_article_newest');
        $ArticleId = $_GET['id'];
        // 基础文章信息
        $Data = $model -> getSelect(['an_id' => $ArticleId]);
        $Title = $Data[0]['title'];

        $ArticleData = [];
        $ArticleData['Title'] = $Data[0]['title'];
        $ArticleData['UpdateTime'] = date('Y-m-d',$Data[0]['create_time']);
        $ArticleData['ViewsCount'] = $Data[0]['views_count'];
        $ArticleData['content'] = $Data[0]['content'];
        $ArticleData['praise'] = $Data[0]['praise'];
        $Classify = "最新资讯";
        // 评论信息

        //获取一级评论数据
        $Book1 = [];
        $this -> assign('Title',$Title);
        $this -> assign('Book1',$Book1);
        $this -> assign('ArticleData',$ArticleData);
        $this -> assign('Classify',$Classify);
        return $this -> fetch('article/index');
    }

    //医生文章分类
    public function ArticleIncrease() {
        $model = new AppIntModel('yyb_article_increase');
        $ArticleId = $_GET['id'];
        // 基础文章信息
        $Data = $model -> getSelect(['in_id' => $ArticleId]);
        $Title = $Data[0]['title'];

        $ArticleData = [];
        $ArticleData['Title'] = $Data[0]['title'];
        $ArticleData['UpdateTime'] = date('Y-m-d',$Data[0]['create_time']);
        $ArticleData['ViewsCount'] = $Data[0]['views_count'];
        $ArticleData['content'] = $Data[0]['content'];
        $ArticleData['praise'] = $Data[0]['praise'];
        $ArticleData['doctor_id'] = $Data[0]['doctor_id'];
        $Classify = "Newes";
        //医生作者信息
        $DoctorM = new DoctorModel();
        $DoctorData = $DoctorM -> getSelect(['doctor_id' => $ArticleData['doctor_id']]);
        // 评论信息

        //获取一级评论数据
        $where = [];
        $Book1 = [];
        
        $this -> assign('Title',$Title);
        $this -> assign('Book1',$Book1);
        $this -> assign('ArticleData',$ArticleData);
        $this -> assign('DoctorData',$DoctorData);
        $this -> assign('Classify',$Classify);
        return $this -> fetch('article/DoctorArticles');
    }

}
?>