<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"/var/www/html/Unkonwn/public/../application/activity/view/727/DataQuery.html";i:1535538472;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__COMPONENT__/bootstrap3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_767141_oe5ssffog6.css">
    <style>
        .Bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            background: url("__IMG__/bg1.jpg");
            z-index: -9999;
        }
        h2 {
            text-align: center;
            color:#fff;
            font-size: 1.4rem;
            margin: 1.5rem 0;
        }
        table {
            width: 18rem;
            text-align: center;
            margin: 0 auto;
        }
        td {
            font-size: 0.7rem;
            border: 0.025rem solid #fff;
            color: #fff;
            padding: 0.25rem 0.3rem;
        }
        .pagination {
            margin: 0 !important;
        }
    </style>
</head>
<body>
    <div class="Bg"></div>
    <div id="app">
        <h2><?php echo $title2; ?></h2>
        <table cellspacing='0'>
            <tr style="background:#fff;">
                <td style="color:#a982ee;">医生姓名</td>
                <td style="color:#a982ee;">手机号</td>
                <td style="color:#a982ee;">入驻时间</td>
                <td style="color:#a982ee;">绑定用户</td>
            </tr>
            <?php if(is_array($returnData) || $returnData instanceof \think\Collection || $returnData instanceof \think\Paginator): if( count($returnData)==0 ) : echo "" ;else: foreach($returnData as $key=>$data): ?>
            <tr>
                <td><?php echo $data['name']; ?></td>
                <td><?php echo $data['phone']; ?></td>
                <td><?php echo date('Y-m-d',$data['time']); ?></td>
                <td><a href="<?php echo $data['url']; ?>">点击查看</a></td>
            </tr>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </table>
        <span style="display:block; width:100%; margin:0.5rem 0; text-align:center; color:#fff;">总共有<?php echo $count; ?>条医生数据</span>
        <!-- 分页按钮 -->

        
    </div>
</body>
<script src="__JS__/jquery.min.js"></script>
<script src="__COMPONENT__/vue/vue.min.js"></script><script src="__COMPONENT__/vue/vue.min.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    var FmTool = new Tool;
    FmTool.Rem();
    //获取页面高度并赋值给父级元素
    $('.Bg').height($(window).height());
    var app = new Vue({
        el: '#app',
        data: {

        }
    });
</script>
</html>