<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:80:"/var/www/html/Unkonwn/public/../application/admin/view/appannouncements/add.html";i:1541563427;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_763769_uimtwpj186.css">
    <style>
        h3 {
            font-weight: bold;
            text-align: center;
        }
        .but {
            float: right;
            font-size: 18px;
            color: #fff;
            background: rgb(92, 190, 52);
            padding: 15px 20px;
            margin: 15px 5px;
        }
        .but:hover {
            background: rgb(63, 161, 25);
        }
        #KinBox {
            width: 1400px;
            margin: 0 auto;
        }
        .titleBox {
            margin: 10px 0;
        }
        input {
            padding: 0 10px;
            width: 200px;
            border: 1px solid rgb(107, 107, 107);
        }
        select {
            height: 22px;
        }
    </style>
</head>
<body>
    <div id="KinBox">
        <h3><?php echo $title; ?></h3>
        <div class="titleBox">
            <span style="margin-left:20px;">公告标题：</span>
            <input id="title" type="text" value="">
            <span style="margin-left:20px;">公告类型：</span>
            <select name="sub_type" id="sub_type">
                <option value="user" selected>用户端推送</option>
                <option value="doctor">医生端推送</option>
            </select>
            <span style="margin-left:20px;">是否隐藏：</span>
            <select name="show" id="show">
                <option value="show" selected>显示</option>
                <option value="hide">隐藏</option>
            </select>
        </div>
        <!-- 加载编辑器的容器 -->
        <script id="container" name="content" type="text/plain"></script>
        <button class="but" onclick="datasubmitted()">确认提交</button>
        <button class="but" onclick="window.history.go(-1);">返回上级</button>
    </div>
</body>
<script src="__JS__/Fm.js"></script>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<!-- 富文本编辑器 -->
<script type="text/javascript" src="__COMPONENT__/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="__COMPONENT__/ueditor/ueditor.all.js"></script>
<script>
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
        ue.setHeight(400);
        //设置默认内容
        ue.setContent("");
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
    //获取内容
    function datasubmitted() {
        var data = {};
        data['title'] = $('#title').val();
        data['sub_type'] = $('#sub_type option:selected').val();
        data['show'] = $('#show option:selected').val();
        data['content'] = ue.getContent();
        $.ajax({
            type: 'post',
            url: 'datasubmitted2',
            data: data,
            dataType: "json",
            success: function(e) {
                alert(e);
                window.history.go(-1);
            }
        });
    }
</script>
</html>