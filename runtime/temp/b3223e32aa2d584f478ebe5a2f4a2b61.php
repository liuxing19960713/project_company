<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:72:"/var/www/html/Unkonwn/public/../application/activity/view/727/Index.html";i:1535538472;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $Title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__COMPONENT__/font-awesome/css/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
    <style>
        #HearderImg {
            width: 20rem;
        }
        .Nav {
            width: 18rem;
            margin: 0.5rem auto 0.8rem; 
        }
        .Title {
            color: #fff;
            background: #c71d24;
            text-align: center;
            font-size: 0.8rem;
            width: 5rem;
            padding: 0.2rem 0;
            margin: 0.4rem 0;
        }
        .PBox {
            width: 100%;
        }
        .PBox p {
            font-size: 0.8rem;
            line-height: 1.4rem;
        }
        .PBox p span {
            line-height: 1.4rem;
            width: 20%;
        }
        .PBox p input {
            margin: 0.8rem 0;
            padding: 0 5%;
            line-height: 1.4rem;
            width: 69%;
            border: 0.026rem solid #000;
        }
        .PBox button {
            display: block;
            margin: 0 auto;
            padding: 0.25rem 0;
            width: 8rem;
            color: #fff;
            background-color: #000;
            border-radius: 0.4rem;
        }
    </style>
</head>
<body>
    <img id="HearderImg" src="__IMG__/Hear.jpg" alt="头部大图">
    <div class="Nav">
        <h3 class="Title">公益活动</h3>
        <div class="PBox">
            <p>1、国内外专家组活动现场科普宣教</p>
            <p>2、专家现场免费问询亲诊</p>
            <p>3、提供合作医院绿色问诊通道</p>
            <p>4、参加活动皆可获得1万元术前检查援助费</p>
            <p>5、抽取10对难孕家庭，最高可获得5万元公益资金进行生育计划</p>
        </div>
    </div>
    <div class="Nav">
        <h3 class="Title">受益人群</h3>
        <div class="PBox">
            <p>1、卵巢功能衰退</p>
            <p>2、染色体异常</p>
            <p>3、高龄产妇</p>
            <p>4、习惯性流产者</p>
            <p>5、多次人工授精失败者</p>
        </div>
    </div>
    <div class="Nav">
        <h3 class="Title">活动安排</h3>
        <div class="PBox">
            <p>时间、地点会以电话方式另行通知</p>
            <p>请关注公众号“优孕宝yoobi”及时获取最新消息</p>
        </div>
    </div>
    <div class="Nav">
        <h3 class="Title">报名参与</h3>
        <div class="PBox">
            <p><span>姓名：</span><input type="text" name="name"></p>
            <p><span>性别：</span><input type="text" name="sex"></p>
            <p><span>年龄：</span><input type="text" name="age" onkeyup="(this.v=function(){this.value=this.value.replace(/[^0-9-]+/,'');}).call(this)" onblur="this.v();"></p>
            <p><span>手机：</span><input type="text" name="phone" onkeyup="this.value=this.value.replace(/\D/g,'')"></p>
            <button class="TJ">提&nbsp;&nbsp;交</button>
        </div>
    </div>
</body>
<script src="__JS__/jquery.min.js"></script>
<script src="__COMPONENT__/vue/vue.min.js"></script><script src="__COMPONENT__/vue/vue.min.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    var FmTool = new Tool;
    FmTool.Rem();
    //获取GET参数
    var $_GET = (function(){
        var url = window.document.location.href.toString();
        var u = url.split("?");
        if(typeof(u[1]) == "string"){
            u = u[1].split("&");
            var get = {};
            for(var i in u){
                var j = u[i].split("=");
                get[j[0]] = j[1];
            }
            return get;
        } else {
            return {};
        }
    })();
    $('.TJ').click(function() {
        //手机格式验证
        var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
        if(!myreg.test($('input[name=phone]').val())){
            alert('手机号码格式错误~请检查');
            return
        }
        //姓名验证
        if($('input[name=name]').val()=="") {
            alert('请输入您的姓名');
            return
        }
        var data = {
            doctorid:$_GET['doctorid']?$_GET['doctorid']:0,
            phone:$('input[name=phone]').val(),
            name:$('input[name=name]').val(),
            sex:$('input[name=sex]').val(),
            age:$('input[name=age]').val()
        }
        //提交注册数据
        $.ajax({
            url: "/activity/Actindex727/usersbinding",
            type: 'post',
            data: data,
            success: function(data) {
                if(data=='手机号已存在') {
                    alert("该手机号已经参与过报名~不能重复报名");
                } else if(data=='报名成功！') {
                    alert(data);
                    window.location.href = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzI2NTQ1MTMxOQ==&scene=123#wechat_redirect";
                }
            }
        });
    });
</script>
</html>