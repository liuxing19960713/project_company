<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:75:"/var/www/html/Unkonwn/public/../application/admin/view/statement/index.html";i:1541563438;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title; ?></title>
<link href="__CSS__/CssReset.css" rel="stylesheet">
<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
<link href="__CSS__/animate.min.css" rel="stylesheet">
<link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
<link href="__CSS__/statement/statement.css" rel="stylesheet">
<style>
    h3 {
        font-size: 20px;
        text-align: center;
        margin: 20px;
    }
    .listBox {
        display: block;
        width: 800px;
        margin: 0 auto;
    }
    .listBox .button {
        text-align: center;
        color: #fff;
        border: 3px solid rgb(11, 172, 60);
        background: rgb(11, 172, 60);
        width: 300px;
        font-size: 20px;
        display: block;
        margin: 20px auto;
        padding: 20px 0;
        border-radius: 20px;
    }
    .listBox .button:hover {
        animation: Bgfir1 1.5s infinite;
    }
    @keyframes Bgfir1 {
        from {background: rgb(11, 172, 60); color:#fff;}
        to {background: #fff; color:rgb(11, 172, 60);}
    }
</style>
</head>
<body>
    <h3>APP声明文件列表</h3>
    <div class="listBox">
        <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
            <a class="button" href="index<?php echo $key; ?>"><?php echo $val['type']; ?></a>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    
</body>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    //Fm插件
    Popups = new Popups;

</script>
</html>