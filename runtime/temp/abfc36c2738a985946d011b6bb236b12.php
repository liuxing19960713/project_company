<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:84:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\Seo\alert.html";i:1544688683;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $subject; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS__/doctor/doctor.css" rel="stylesheet">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
</head>
<body style="background-color: #f3f3f4">
    <div class="DoctorData clearfix">
        <h3 class="h3"><?php echo $subject; ?></h3>
        <div class="Inf Box clearfix">
            <div class="Box2" style="width: 25%">
                <h5 class="h5">SEO名：</h5>
                <input type="text" style="width: 90%" name="title" value="<?php echo $info['title']; ?>" placeholder="SEO名：">
            </div>

            <div class="Box2" style="width: 70%">
                <h5 class="h5">SEO关键字：</h5>
                <input type="text" style="width: 90%" name="keywords" value="<?php echo $info['keywords']; ?>" placeholder="SEO关键字：">
            </div>
          
        </div>
     
     
        <div class="But-Box clearfix">
            <button class="Bt1" onclick="javascript:window.history.back(-1);">取消返回</button>
            <button class="Bt2" onclick="SubmitData();">提交修改</button>
        </div>
    </div>
    
</body>
<script src="__JS__/jquery.min.js"></script>
<!-- 地区选择插件distpicker.js(注意：引入顺序不能错！)-->
<script src="__JS__/distpicker/distpicker.data.js"></script>
<script src="__JS__/distpicker/distpicker.js"></script>
<script src="__JS__/distpicker/main.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<!-- 七牛云SDK -->
<!-- <script src="https://unpkg.com/qiniu-js/dist/qiniu.min.js"></script> -->
<script>
    var T = new Tool();
    Popups = new Popups();
    //创建用来读取此文件的对象(具体二进制内容)
    var reader = new FileReader();

   

    //图片放大效果
    // function imgmag(dom) {
    //     Popups.ImgMag(dom,500);
    // }

    //数据修改提交
    function SubmitData() {
        Popups.Confirm(500,"确认提交？","提交","不提交",callfun1,callfun2);
        function callfun1() {
            //数据封装
            var data = {};
             
            //id
            data['id'] = T.GetData('id');
            //seo名称
            data['title'] = $("select[name='title']").val();
            //标签链接
            data['keywords'] = $("input[name='keywords']").val();
           

            
            //医生标签
            console.log(data);
            $.ajax({
                url: "update",
                type: 'post',
                data: data, 
                success: function(data) {
                    alert(data);
                    //location.reload();
                    //window.location.href = "index";
                    var Referrer = document.referrer;
                    window.location.href = Referrer;
                }
            });
        }
        function callfun2() {
            // 刷新页面
            location.reload();
        }
    }

    
</script>
</html>