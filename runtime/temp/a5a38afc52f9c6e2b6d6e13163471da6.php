<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"/var/www/html/Unkonwn/public/../application/index/view/article/DoctorArticles.html";i:1535167539;s:80:"/var/www/html/Unkonwn/public/../application/index/view/./common/AppDownLoad.html";i:1535167540;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- 文章分享页 -->
    <title><?php echo $ArticleData['Title']; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS2__/common.css" rel="stylesheet">
    <link href="__CSS2__/Articleshare.css" rel="stylesheet">
    <link href="__COMPONENT__/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
</head>

<body>
    <div id="app">
        <div class="ArticleBox clearfix">
            <div class="h3"><?php echo $ArticleData['Title']; ?></div>
            <!-- 医生作者信息 -->
            <div class="DictorBox clearfix">
                <img class="TX" src="<?php echo $DoctorData[0]['avatar']; ?>" alt="">
                <div class="Box2 clearfix">
                    <span class="name"><?php echo $DoctorData[0]['nick_name']; ?></span>
                    <span class="hospital"><?php echo $DoctorData[0]['hospital']; ?></span>
                </div>
                <div class="Box2 clearfix">
                    <span class="department"><?php echo $DoctorData[0]['department_parent']; ?>&nbsp;<?php echo $DoctorData[0]['title']; ?></span>
                </div>
                <a class="bt AppDownLoad">未关注</a>
            </div>
            <!-- 文章信息 -->
            <p class="SpBox2 clearfix">
                <span class="sp1 update_time" style="margin-right:0.7rem;"><?php echo $ArticleData['UpdateTime']; ?></span>
                <span class="sp1 views_count" style="float:right; margin:0 0.7rem;">
                <i class="fa fa-eye" style="margin:0 0.2rem;"></i><?php echo $ArticleData['ViewsCount']; ?></span>
            </p>
            <p>
                <!-- 文章内容 -->
                <?php echo $ArticleData['content']; ?>
            </p>
            <div class="Dz_Box clearfix">
                <a class="AppDownLoad Dz1">
                    <i class="fa fa-thumbs-up"></i><?php echo $ArticleData['praise']; ?>
                </a>
                <a class="AppDownLoad Dz2">
                    <i class="fa fa-comment"></i>立即咨询
                </a>
            </div>
            <!-- 文章评论区 -->
            <!-- $Book1一级评论数据 -->
            <?php if(empty($Book1) != true): ?>
            <div class="Title2Box clearfix">
                <span class="Bag"></span>
                <span class="text">最新评论</span>
            </div>
            <?php if(is_array($Book1) || $Book1 instanceof \think\Collection || $Book1 instanceof \think\Paginator): $i = 0; $__LIST__ = $Book1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <div class="BookBox clearfix">
                <img class="TX" src="__IMG__/a1.jpg" alt="">
                <div class="Box2 clearfix">
                    <span class="user_name">张小萌</span>
                    <span class="Book_thumbs">
                        <i class="fa fa-thumbs-up"></i>20</span>
                    <span class="Book_comment">
                        <i class="fa fa-comment"></i>2</span>
                </div>
                <span class="Book_time clearfix">2小时前</span>
                <p class="Book_content"><?php echo $vo['content']; ?></p>
                <div class="BookBox2 clearfix">
                    <p>
                        <span>果果：</span>说得有道理！</p>
                    <p>
                        <button>共20条回复></button>
                    </p>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; endif; if(empty($Book1) != false): ?>
            <div class="Title2Box clearfix">
                <span class="Bag"></span>
                <span class="text2">下载APP查看更多精彩评论！</span>
            </div>
            <?php endif; ?>
            <!-- 下载APP，依赖vue -->
<div class="Download">
    <img class="ico" src="__IMG__/appico.jpg" alt="appico">
    优孕宝APP，最懂你的试管婴儿专家
    <a class="bt AppDownLoad">立即下载</a>
</div>
<div class="Download2"></div>
        </div>
    </div>
</body>
<script src="__JS__/jquery.min.js"></script>
<script src="__COMPONENT__/vue/vue.min.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    //Fm插件
    var FmPopups = new Popups;
    var FmTool = new Tool;
    //设置Rem
    FmTool.Rem();
    //判断设备
    if(FmTool.CheckIsAppleDevice()) {
        //alert("iOS设备");
        $('.AppDownLoad').attr('href','https://itunes.apple.com/cn/app/%E4%BC%98%E5%AD%95%E5%AE%9D/id1235697957?mt=8');
    } else if(FmTool.CheckIsAndroidDevice()) {
        //alert("安卓设备");
        $('.AppDownLoad').attr('href','http://a.app.qq.com/o/simple.jsp?pkgname=cn.dankal.yyh_client&channel=0002160650432d595942&fromcase=60001');
    }
    //初始化Vue
    var app = new Vue({
        el: '#app',
        //数据源
        data:{
            //导航栏名称
            title: '<?php echo $Title; ?>'
        },
        //自定义数据处理方法封装(执行与数据相关的操作)
        methods:{
            
        },
        //渲染前执行的内容(执行与数据相关的操作)
        computed:{
            
        }
    });
</script>

</html>