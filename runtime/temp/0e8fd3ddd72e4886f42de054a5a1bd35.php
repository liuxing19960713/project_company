<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:79:"/var/www/html/Unkonwn/public/../application/admin/view/statement/zhawenadd.html";i:1541563438;}*/ ?>
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
<link href="//at.alicdn.com/t/font_763769_uimtwpj186.css" rel="stylesheet">
<style>
    .Box {
        width: 800px;
        margin: 0 auto;
    }
    .Box .Bt {
        text-align: center;
        width: 150px;
        font-size: 20px;
        padding: 5px 10px;
        display: block;
        background: rgb(37, 150, 14);
        color: #fff;
        border: 2px solid #fff;
    }
    .Box .Bt:hover {
        background: #fff;
        color: rgb(37, 150, 14);
        border: 2px solid rgb(37, 150, 14);
    }
</style>
</head>
<body>
    <div class="Box">
        <div class="clearfix" style="width:400px; margin:10px 0;">
            <!-- 标题名 -->
            <div style="width:30%; float: left;">绑定的文章：</div>
            <input name="Title" type="text" style="width:70%; border:1px solid rgb(54, 54, 54);" class="Title1">
            <!-- 标题说明 -->
            <div style="width:30%; float: left;">绑定的二级标题：</div>
            <input name="Title2" type="text" style="width:70%; border:1px solid rgb(54, 54, 54);"class="Title2">
            <!-- 抵属文章 -->
            <div style="width:30%; float: left;">数据类型：</div>
            <input name="zhawen" type="text" style="width:70%; border:1px solid rgb(54, 54, 54);"class="Title2">
        </div>
        <!-- 加载编辑器的容器 -->
        <script id="container" name="content" type="text/plain"></script>
    </div> 
    <div class="Box">
        <button onclick="javascript:window.history.back(-1);" style="float:left; margin:20px;" class="Bt" href="#">取消返回</button>
        <button style="float:right; margin:20px;" class="Bt" onclick="Submit()">保存数据</button>
    </div>
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
    //实例化富文本编辑器
    var html,txt;
    var ue = UE.getEditor('container',{
        //富文本编辑器参数
        //设置外框高度
        //initialFrameHeight: 400
        //关闭远程抓取图片功能
        catchRemoteImageEnable: false,
    });
    ue.ready(function() {
        //富文本编辑器构造函数
        //设置最大高度
        ue.setHeight(300);
        //设置默认内容
        ue.setContent('');
        //获取纯文本内容，返回: hello
        txt = ue.getContentTxt();
    });
    //设置文件上传模块提交图片地址:
    UE.Editor.prototype.QnUpImg = UE.Editor.prototype.getActionUrl;
    UE.Editor.prototype.getActionUrl = function(action) {
        if (action == 'uploadimage') {
            return '/admin/statement/ueditor';
        } else {
            return this.QnUpImg.call(this, action);
        }
    }
    //数据提交
    function Submit() {
        //获取html内容
        html = {};
        html['oper'] = '新增';
        html['type'] = $("input[name='zhawen']").val();
        html['title'] = $("input[name='Title']").val();
        html['title2'] = $("input[name='Title2']").val();
        html['content'] = ue.getContent();
        //html['content'] = ue.getContentTxt();
        console.log(html);
        $.ajax({
            url: "addApi",
            type: 'post',
            data: html,
            success: function(data) {
                console.log(data);
                alert('保存成功');
                window.history.back(-1);
            }
        });
    }
</script>
</html>