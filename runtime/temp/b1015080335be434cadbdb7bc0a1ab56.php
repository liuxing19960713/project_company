<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:87:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\doctor\index.html";i:1544492841;}*/ ?>
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
    <!-- <h3 class="title"><?php echo $title; ?></h3> -->
    <!-- 筛选 -->
    <select name="filter" id="filter">
        <option value="医生列表">医生列表</option>
        <option value="用户列表">用户列表</option>
    </select>
    <!-- add -->
    <button id="DoctorAddBt" onclick="AddData()">添加医生</button>
    <!-- 搜索 -->
    <form id="SearchForm" action="Search" method="get">
        <input type="text" name="phone" placeholder="输入要搜索的手机号" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" maxlength="11"/>
        <input type="text" name="name" placeholder="输入要搜索的医生名称"/>
        <input type="text" name="hospital" placeholder="输入要搜索的医院名称"/>
        <input type="submit" onclick="" value="搜&nbsp;索">
    </form>
    <table id="DoctorTab" border="1">
        <tr>
            <th width="5%">ID</th>
            <th>头像</th>
            <th>姓名</th>
            <th>性别</th>
            <th>手机</th>
            <th>医院</th>
            <th>地区</th>
            <th>状态</th>
            <th>注册时间</th>
            <th>最新修改时间</th>
            <th>操作</th>
        </tr> 
        <!-- 开始循环 -->
        <?php if(is_array($pagdata) || $pagdata instanceof \think\Collection || $pagdata instanceof \think\Paginator): $i = 0; $__LIST__ = $pagdata;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$doctor): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $doctor['doctor_id']; ?></td>
            <td>
                <img class="DoctorTx ImgMag" src="<?php echo $doctor['avatar']; ?>" alt="医生头像" onclick="imgmag(this)">
            </td>
            <td><?php echo $doctor['nick_name']; ?></td>
            <td><?php echo $doctor['gender']; ?></td>
            <td><?php echo $doctor['mobile']; ?></td>
            <td><?php echo $doctor['hospital']; ?></td>
            <td><?php echo $doctor['province']; ?><?php echo $doctor['city']; ?></td>
            <td><?php echo $doctor['audit_status']; ?></td>
            <td><?php echo $doctor['create_time']; ?></td>
            <td><?php echo $doctor['update_time']; ?></td>
            <td>
                <button class=<?php echo $doctor['doctor_id']; ?> onclick="Alter(this)" style="color:#18a689"><i class="fa fa-paste"></i>编辑</button>
                &nbsp;&nbsp;&nbsp;
                <button class=<?php echo $doctor['doctor_id']; ?> onclick="DeleteData(this)" style="color:#ff2251"><i class="fa fa-trash-o"></i>删除</button>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <!-- 总条数 -->
    <span style="margin:5px 0 0 1%; float:left;">总共<?php echo $count; ?>条记录</span>
    <!-- 分页按钮 -->
    <?php echo $pagdata->render(); ?>
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
    $('#filter').change(function(){
        //获取选项的值
        var val = $("#filter option:selected").val();
        if(val == '医生列表') {
            location.reload();
        } else if(val == '用户列表') {
            window.location.href = "../../admin/user/index.html";
        }
    });
    //添加用户
    function AddData(){
        window.location.href = "add";
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
                    alert(data);
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
        window.location.href = "/admin/doctor/Alter?doctor_id=" + id;
    }
</script>
</html>