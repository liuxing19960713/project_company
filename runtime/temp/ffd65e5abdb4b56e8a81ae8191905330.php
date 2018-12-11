<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"/var/www/html/Unkonwn/public/../application/admin/view/doctor/doctor_add.html";i:1541563432;}*/ ?>
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
<body style="background-color: #f3f3f4">
    <div class="Add-box clearfix">
        <h3 class="title"><i class="fa fa-puzzle-piece"></i>&nbsp;<?php echo $title; ?></button></h3>
        <input type="text" name="phone" placeholder="请输入要添加的账号（手机号）" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" maxlength="11"/>
        <input type="text" name="name" placeholder="请输入添加的用户名称" maxlength="8"/>
        <input type="password" name="password1" placeholder="请设置密码" maxlength="12"/>
        <input type="password" name="password2" placeholder="请再次输入密码" maxlength="12"/>
        <input type="submit" onclick="submit()" value="提&nbsp;交">
        <input type="submit" onclick="javascript:window.history.back(-1);" value="取&nbsp;消&nbsp;返&nbsp;回">
    </div>
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
    function cancel() {
        window.location.href = '/test/test/index.html';
    }
    function submit() {
        var phone = $("input[name='phone']").val();
        var name = $("input[name='name']").val();
        var password1 = $("input[name='password1']").val();
        var password2 = $("input[name='password2']").val();
        if(phone=="" || name=="" || password1=="" || password2=="") {
            alert("信息输入不完整，请核对后重新填写!");  
            return false; 
        }
        if(!(/0?(13|14|15|18|17)[0-9]{9}/.test(phone)) || phone=="") { 
            alert("手机号码有误，请核对后重新填写!");  
            return false; 
        }
        if(!(/[A-Za-z0-9_\-\u4e00-\u9fa5]+/.test(password1))) { 
            alert("密码格式错误，请核对后重新填写!");  
            return false; 
        }
        if(!(password1 == password2)) {
            alert("两次填写的密码不一致！");  
            return false; 
        }
        $.ajax({
            url: "/admin/Doctor/adddata",
            type: 'post',
            data: {
                'phone': phone,
                'name': name,
                'password': password1
            },
            success: function(data) {
                if(data="1") {
                    alert("医生添加成功！新增"+data+"条数据");
                    window.location.href = "index";
                } else if(data="false") {
                    alert("该医生手机号码已被注册！");
                    location.reload();
                }
                //location.reload();
            }
        });
    }
</script>
</html>