<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:78:"/var/www/html/Unkonwn/public/../application/activity/view/727/DoctorLogin.html";i:1535538472;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $Title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
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
        #Logo {
            width: 4.75rem;
            margin: 3.14rem 7.6rem 1.5rem;
        }
        .buttonBox {
            color: #fff;
            width: 6rem;
            margin: 0 auto;
        }
        .buttonBox button{
            float: left;
            width: 3rem;
            line-height: 1.2rem;
        }
        .signin,.login {
            width: 14.35rem;
            margin: 0 auto;
        }
        .inpBox {
            width: 14.35rem;
            position: relative;
            border-bottom: 0.027rem solid #fff;
        }
        .inpBox input,.inpBox select {
            color: #fff;
            padding: 0 15%;
            margin-top: 0.8rem;
            width: 70%;
            font-size: 0.8rem;
            line-height: 1.8rem;
            background: none;
        }
        .inpBox .CodeBt {
            border: 0.027rem solid #fff;
            color: #fff;
            font-size: 0.6rem;
            padding: 0.3rem 0.5rem;
            /* border-radius: 0.5rem; */
            position: absolute;
            top: 1rem; right: 0rem;
        }
        .inpBox select {
            position: absolute;
            top: 0; left: 2rem;
            border: none;
        }
        .signin i,.login i {
            color: #fff;
            position: absolute;
            top: 0.8rem;
            font-size: 1.6rem;
        }
    </style>
</head>
<body>
    <div class="Bg"></div>
    <img id="Logo" src="__IMG__/Logo.png" alt="Logo">
    <!-- 注册 -->
    <div class="signin">
        <!-- 姓名 -->
        <div class="inpBox">
            <i class="iconfont icon-shouye"></i>
            <input type="text" name="doctorname" maxlength="11" placeholder="请输入您的姓名">
        </div>
        <!-- 手机号 -->
        <div class="inpBox">
            <i class="iconfont icon-shouji"></i>
            <input type="text" name="phone" placeholder="请输入您的手机号码" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')">
        </div>
        <!-- 医院 -->
        <!-- <div class="inpBox clearfix" onclick="Select('.select')">
            <i class="iconfont icon-yiyuanzixunkaobei" style="font-size:1.4rem; left:0.2rem;"></i>
            <input type="text" name="hospital" maxlength="11" placeholder="请输入您的任职医院" readonly="readonly">
        </div> -->
        <!-- 科室 -->
        <div class="inpBox clearfix" onclick="Select('.select')">
            <i class="iconfont icon-haofangtuo400iconfont2yiyuan"></i>
            <input type="text" name="desk" maxlength="11" placeholder="请选择您的科室" readonly="readonly">
            <i class="iconfont icon-xiangxia" style="top:1.2rem; right:0.2rem; font-size:0.8rem;"></i>
        </div>
        <div class="select" style="color:#fff; display:none;">
            <?php if(is_array($desk) || $desk instanceof \think\Collection || $desk instanceof \think\Paginator): $i = 0; $__LIST__ = $desk;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data1): $mod = ($i % 2 );++$i;?>
                <div onclick="SelectOption('<?php echo $data1['title']; ?>')" style="text-align:center; font-size:0.8rem; padding:0.25rem 0; border: 1px solid #fff;">
                    <?php echo $data1['title']; ?> 
                </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!-- 密码 -->
        <div class="inpBox">
            <i class="iconfont icon-mima"></i>
            <input type="password" name="password" maxlength="20" placeholder="请设置您的密码">
        </div>
        <div class="inpBox">
            <i class="iconfont icon-mima"></i>
            <input type="password" name="password2" maxlength="20" placeholder="请再次确认密码">
        </div>
        <!-- 验证码 -->
        <div class="inpBox" style="margin-bottom: 1rem;">
            <i class="iconfont icon-duanxinyanzhengma"></i>
            <input type="code" name="code" maxlength="20" placeholder="请输入验证码">
            <button class="CodeBt" onclick="Code1()">获取验证码</button>
        </div>
        <div class="buttonBox clearfix">
            <button class="QhBt1" style="color:#fff; font-size:1rem; border-right: 0.027rem solid #fff;">注册</button>
            <button class="QhBt2" style="color:#fff; font-size:1rem;">登录</button>
        </div>
        <button id="signinBt" style="width:100%; color:#ab80f1; display:block; margin:1rem auto; background:#fff; padding:0.5rem 0;">注&nbsp;&nbsp;册</button>
    </div>
    <!-- 登录 -->
    <div class="login" style="display:none;">
        <div class="inpBox">
            <i class="iconfont icon-shouji"></i>
            <input type="text" name="loginphone" placeholder="请输入您的手机号码" maxlength="11" onkeyup="this.value=this.value.replace(/\D/g,'')">
        </div>
        <div class="inpBox" style="margin-bottom: 1rem;">
            <i class="iconfont icon-mima"></i>
            <input type="password" name="loginpassword" placeholder="请设置您的密码">
        </div>
        <div class="buttonBox clearfix">
            <button class="QhBt1" style="color:#fff; font-size:1rem; border-right: 0.027rem solid #fff;">注册</button>
            <button class="QhBt2" style="color:#fff; font-size:1rem;">登录</button>
        </div>
        <button id="loginBt" style="width:100%; color:#ab80f1; display:block; margin:1rem auto; background:#fff; padding:0.5rem 0;">登&nbsp;&nbsp;录</button>
        <a href="/activity/Actindex727/ForPassword" style="color:#fff; text-align:center; display:block; margin:0 auto;">忘记密码？点我重设密码吧</a>
    </div>
</body>
<script src="__JS__/jquery.min.js"></script>
<script src="__COMPONENT__/vue/vue.min.js"></script><script src="__COMPONENT__/vue/vue.min.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    var FmTool = new Tool;
    FmTool.Rem();
    //下拉菜单
    function Select(dom) {
        $(dom).fadeToggle(0);
    }
    function SelectOption(e) {
        $('input[name=desk]').val(e);
        $('.select').fadeToggle(0);
    }
    //获取验证码
    function Code1() {
        $('.CodeBt').fadeOut(0);
        if($('input[name=phone]').val()){
            $.ajax({
                url: "/activity/Actindex727/code1",
                type: 'post',
                data: {
                    "mobile":$('input[name=phone]').val(),
                },
                success: function(data) {
                    if(!data){
                        alert("验证码获取不了了");
                    }
                }
            });
        } else {
            alert('请先填写手机号码~');
        }
        //显示获取验证码按钮
        setTimeout("$('.CodeBt').fadeIn(0)",60000);
    }
    //数据验证规则
    function CheckPassWord(password) {
        //必须为字母加数字且长度不小于8位
        var str = password;
        if (str == null || str.length <8) {
            return false;
        }
        var reg1 = new RegExp(/^[0-9A-Za-z]+$/);
        if (!reg1.test(str)) {
            return false;
        }
        var reg = new RegExp(/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/);
        if (reg.test(str)) {
            return true;
        } else {
            return false;
        }
    }
    //获取页面高度并赋值给父级元素
    $('.Bg').height($(window).height());
    //登录、注册界面切换
    $('.QhBt1').click(function(){
        $('.signin').fadeIn(200);
        $('.login').fadeOut(0);
    });
    $('.QhBt2').click(function(){
        $('.login').fadeIn(200);
        $('.signin').fadeOut(0);
    });
    //sign
    $('#signinBt').click(function() {
        //手机格式验证
        var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
        if(!myreg.test($('input[name=phone]').val())){
            alert('手机号码格式错误~请检查');
            return
        }
        //姓名验证
        if($('input[name=doctorname]').val()=="") {
            alert('请输入您的姓名');
            return
        }
        //科室验证
        if($('input[name=desk]').val()=="") {
            alert('请选择您的科室');
            return
        }
        //password验证
        if(!$('input[name=password]').val()==$('input[name=password1]').val()) {
            alert('两次输入的密码不一致！');
            return
        }
        if(!CheckPassWord($('input[name=password]').val())) {
            alert('密码必须为字母加数字且长度不小于8位！');
            return
        }
        var data = {
            phone:$('input[name=phone]').val(),
            name:$('input[name=doctorname]').val(),
            desk:$('input[name=desk]').val(),
            password:$('input[name=password]').val(),
            code:$('input[name=code]').val()
        }
        //提交注册数据
        $.ajax({
            url: "/activity/Actindex727/doctorsignin",
            type: 'post',
            data: data,
            success: function(data) {
                if(data=="0") {
                    alert("验证码错误");
                    return;
                } else if(data=="2"){
                    alert("验证码已过期，请重新获取");
                    return;
                } else if(data=="false") {
                    alert("该手机号码已被注册!请直接登录");
                    return;
                } else {
                    window.location.href = "/activity/Actindex727/doctorQR?doctorid="+data;
                }
            }
        });
    });
    //login
    $('#loginBt').click(function() {
        var data = {
            phone:$('input[name=loginphone]').val(),
            password:$('input[name=loginpassword]').val()
        }
        //提交登录数据
        $.ajax({
            url: "/activity/Actindex727/doctorlogin2",
            type: 'post',
            data: data,
            success: function(data) {
                if(data==="0"){
                    alert("密码或手机号错误");
                } else {
                    window.location.href = "/activity/Actindex727/doctorQR?doctorid="+data;
                }
            }
        });
    });
</script>
</html>