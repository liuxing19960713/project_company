<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"/var/www/html/Unkonwn/public/../application/admin/view/statement/zhawen.html";i:1541563438;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>杂文管理</title>
<link href="__CSS__/CssReset.css" rel="stylesheet">
<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
<link href="__CSS__/animate.min.css" rel="stylesheet">
<link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
<link href="__CSS__/statement/statement.css" rel="stylesheet">
<link href="//at.alicdn.com/t/font_763769_uimtwpj186.css" rel="stylesheet">
<link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
<style>
    h3 {
        color: rgb(41, 41, 41);
        text-align: center;
        font-size: 30px;
        margin: 20px 0;
    }
    table {
        width: 900px; 
        margin: 0 auto;
        text-align: center;
    }
    table tr td {
        padding: 10px 0;
        border: 1px solid rgb(70, 70, 70);
    }
    .AddBt {
        display: block;
        padding: 10px 20px;
        width: 150px; 
        margin: 20px auto;
        text-align: center;
        color: #fff;
        background: rgb(24, 134, 14);
    }
    .AddBt:hover {
        color: #fff;
        background: rgb(71, 155, 63);
    }
</style>
</head>
<body>
    <h3><?php echo $title; ?></h3>
    <table>
        <tr style="background:rgb(49, 49, 49); color:#fff;">
            <td>标题名</td>
            <td>绑定的文章</td>
            <td>最后修改时间</td>
            <!-- <td>操作</td> -->
        </tr>
        <?php if(is_array($titleData) || $titleData instanceof \think\Collection || $titleData instanceof \think\Paginator): $i = 0; $__LIST__ = $titleData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$titleData): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $titleData['titlename']; ?></td>
            <td><?php echo $titleData['article']; ?></td>
            <td><?php echo $titleData['update_time']; ?></td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <h3><?php echo $title2; ?></h3>
    <table>
        <tr style="background:rgb(49, 49, 49); color:#fff;">
            <td>绑定的文章</td>
            <td>绑定的标题</td>
            <td>数据类型</td>
            <td>最后修改时间</td>
            <td>操作</td>
        </tr>
        <!-- 开始循环 -->
        <?php if(is_array($zhawendata) || $zhawendata instanceof \think\Collection || $zhawendata instanceof \think\Paginator): $i = 0; $__LIST__ = $zhawendata;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$zhawen): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $zhawen['article']; ?></td>
            <td><?php echo $zhawen['title']; ?></td>
            <td><?php echo $zhawen['type']; ?></td>
            <td><?php echo $zhawen['update_time']; ?></td>
            <td>
                <a href="<?php echo $alr_url[$key]; ?>" style="color: rgb(25, 112, 153);"><i class="iconfont icon-edit-fill"></i>修改</a>
                &nbsp;
                <a onclick="DeleteID('<?php echo $zhawen['id']; ?>')" href="#" style="color: rgb(236, 44, 44);"><i class="iconfont icon-shanchu"></i>删除</a>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <!-- 总条数 -->
    <!-- <div style="width:200px; margin:0 auto; text-align:center;">总共<?php echo $count; ?>条记录</div> -->
    <!-- 分页按钮 -->
    <div style="width:200px; text-align:center; margin:0 auto;">
        <?php echo $zhawendata->render(); ?>
    </div>
    
    <!-- 添加按钮 -->
    <a class="AddBt" href="zhawenadd">新增一条数据<i style="position:relative; top:1.5px; left:2px;" class="iconfont icon-plus-circle"></i></a>
</body>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<!-- 富文本编辑器 -->
<script type="text/javascript" src="__COMPONENT__/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="__COMPONENT__/ueditor/ueditor.all.js"></script>
<!-- Fm -->
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    //Fm插件
    Popups = new Popups;
    function DeleteID(id) {
        Popups.Confirm(300,"是否确定删除",'是','否',function(){
            $.ajax({
                url: "deleteApi",
                type: 'post',
                data: {'id': id},
                success: function(data) {
                    alert(data);
                    location.reload();
                }
            });
        },function(){
            //location.reload();
        });

    }
    //添加文章
    $('.AddBt').click(function() {

    });
    
</script>
</html>