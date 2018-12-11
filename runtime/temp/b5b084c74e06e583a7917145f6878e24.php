<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:75:"/var/www/html/Unkonwn/public/../application/activity/view/727/DoctorQR.html";i:1535538472;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $Title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__COMPONENT__/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/bootstrap3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
    <style>
        .Bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            background: url("__IMG__/bg1.jpg");
            z-index: -9999;
        }
        h2 {
            color: #fff;
            text-align: center;
            margin: 1.6rem 0 0.5rem;
            font-size: 1.6rem;
        }
        #qrcode img {
            margin: 0 auto;
            width: 10rem !important;
        }
        .Nav {
            width: 18rem;
            margin: 0.5rem auto;
        }
        .Nav table {
            width: 100%;
            text-align: center;
        }
        .Nav .Th {
            color: #ab83ed;
            background: #fff;
            border: 0.024rem solid #fff;
            padding: 0.25rem 0;
        }
        .Nav table tr td {
            color: #fff;
            border: 0.024rem solid #fff;
            padding: 0.25rem 0;
        }
        /* 分页按钮 */
        /* .pagination {
            display: block;
            width: auto;
            height: 1rem; line-height: 1rem;
            font-size: 0.8rem;
        }
        .pagination .disabled {
            float: left;
        } */
    </style>
</head>
<body>
    <div class="Bg"></div>
    <h2>我的二维码:</h2>
    <div id="qrcode"></div>
    <h2>我的患者:</h2>
    <div class="Nav">
        <table cellspacing="0">
            <tr>
                <td class="Th">用户名</td>
                <td class="Th">性别</td>
                <td class="Th">手机号</td>
            </tr>
            <?php if(is_array($Data) || $Data instanceof \think\Collection || $Data instanceof \think\Paginator): $i = 0; $__LIST__ = $Data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data1): $mod = ($i % 2 );++$i;?>
            <tr>
                <td><?php echo $data1['name']; ?></td>
                <td><?php echo $data1['sex']; ?></td>
                <td><?php echo $data1['phone']; ?></td>
            </tr>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <tr id="None" style="display:none;">
                <td colspan="3">暂无数据！</td>
            </tr>
        </table>
        <!-- 分页按钮 -->
        <div style="float:right; margin:0 1rem 0;">
            <?php echo $Data->render(); ?>
        </div>
    </div>
</body>
<script src="__JS__/jquery.min.js"></script>
<script src="__COMPONENT__/vue/vue.min.js"></script><script src="__COMPONENT__/vue/vue.min.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script src="__COMPONENT__/Fm/qrcode.js"></script>
<script>
    var FmTool = new Tool;
    FmTool.Rem();
    if("<?php echo $return; ?>"=="0") {
        $('#None').fadeIn(0);
    }
    //获取页面高度并赋值给父级元素
    $('.Bg').height($(window).height());
    //生成二维码
    var QRurl = '<?php echo $QRurl; ?>';
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        width : 100,//设置宽高
        height : 100
    });
    qrcode.makeCode(QRurl);


    
</script>
</html>