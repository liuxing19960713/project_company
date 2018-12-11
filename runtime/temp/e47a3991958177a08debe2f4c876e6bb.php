<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:71:"/var/www/html/Unkonwn/public/../application/admin/view/index/index.html";i:1541563434;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>snake后台</title>
    <link rel="shortcut icon" href="favicon.ico"> 
	<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.4.0" rel="stylesheet">

    <link href="__CSS__/animate.min.css" rel="stylesheet">
    <link href="__CSS__/style.min.css?v=4.1.1" rel="stylesheet">
</head>

<body>
    <div class="middle-box text-center animated fadeInDown">    
        <div class="page-header">
            <h2><b>系统信息</b></h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tbody>
                <?php if(is_array($sys_info) || $sys_info instanceof \think\Collection || $sys_info instanceof \think\Paginator): $i = 0; $__LIST__ = $sys_info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo $key; ?></td>
                    <td><?php echo $vo; ?></td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="__JS__/jquery.min.js?v=2.1.4"></script>
    <script src="__JS__/bootstrap.min.js?v=3.3.6"></script>
</body>
</html>
