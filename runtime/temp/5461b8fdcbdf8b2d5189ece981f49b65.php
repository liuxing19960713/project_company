<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:86:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\Label\index.html";i:1544665003;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS__/doctor/doctor.css" rel="stylesheet">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
</head>
<body>
 
    <!-- add -->
    <button id="DoctorAddBt" onclick="AddData()">添加链接</button>

    <table id="DoctorTab" border="1">
        <tr>
            <th width="5%">ID</th>
            <th>标签名称</th>
            <th>标签URL</th>
            <th>是否推荐标签</th>
            <th>最新修改时间</th>
            <th>操作</th>
        </tr>
        <!-- 开始循环 -->
        <?php if(is_array($info) || $info instanceof \think\Collection || $info instanceof \think\Paginator): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <tr>
           <td><?php echo $vo['id']; ?></td>
           <td><?php echo $vo['title']; ?></td>
           <td><?php echo $vo['label_url']; ?></td>


           <td><?php if($vo['is_reco'] == '0'): ?>不推荐<?php else: ?> 推荐<?php endif; ?> </td>
           <td><?php echo $vo['addtime']; ?></td>
           <td>
              <button class=<?php echo $vo['id']; ?>   onclick="Alter(this)" style="color:#18a689"><i class="fa fa-paste"></i>编辑</button>
                &nbsp;&nbsp;&nbsp;
                <button class=<?php echo $vo['id']; ?>  onclick="DeleteData(this)" style="color:#ff2251"><i class="fa fa-trash-o"></i>删除</button>
           </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <!-- 总条数 -->
    <span style="margin:5px 0 0 1%; float:left;">总共<?php echo $count; ?> 条记录</span>
    <!-- 分页按钮 -->
    <?php echo $info->render(); ?>

 
</body>
<script src="__JS__/jquery.min.js"></script>
<!-- 地区选择插件distpicker.js(注意：引入顺序不能错！)-->
<script src="__JS__/distpicker/distpicker.data.js"></script>
<script src="__JS__/distpicker/distpicker.js"></script>
<script src="__JS__/distpicker/main.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    //Fm插件
    Popups = new Popups;
    //图片放大效果
    function imgmag(dom) {
        Popups.ImgMag(dom,500);
    }

    //当初始选项发生改变时跳转
    // $('#filter').change(function(){
    //     //获取选项的值
    //     var val = $("#filter option:selected").val();
    //     if(val == '医生列表') {
    //         location.reload();
    //     } else if(val == '用户列表') {
    //         window.location.href = "../../admin/user/index.html";
    //     }
    // });
    //添加文章
    function AddData(){
        window.location.href = "create";
    }
    //删除按钮点击事件
    function DeleteData(dom) {
        // 获取点击元素的id
        var id = $(dom).attr("class");
        Popups.Confirm(500,"请确认是否删除？","确定","取消",callfun1,callfun2);
        // 回调函数
        function callfun1() {
            $.ajax({
                url: "delete",
                type: 'post',
                data: {'id': id},
                success: function(data) {
                    alert(data.message);
                    location.reload();
                }
            });
        }
        function callfun2() {
            // 刷新页面
            location.reload();
        }
    }
    //跳转编辑信息编辑页面
    function Alter(dom) {
        var id = $(dom).attr("class");
        window.location.href = "/admin/label/edit?id=" + id;
    }
</script>
</html>