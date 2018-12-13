<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:87:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\Advert\alert.html";i:1544683003;}*/ ?>
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
    <div class="DoctorData clearfix">
        <h3 class="h3"><?php echo $title; ?></h3>
        <!-- 头像模块 -->
        <div class="Portrait Box clearfix">
            <h4 class="h4">广告图片</h4>
            <img id="PortraitImg" class="Portrait_img" src="<?php echo $info['img_url']; ?>" alt="医生头像" onclick="imgmag(this)" >
            
            <form id="UpImg-Portrait_img">
                <input id="UpToken" name="token" type="hidden" value="<?php echo $QnToken; ?>">
                <div class="bt1" onclick="$('#file').click();">
                    <i class="fa fa-cloud-upload"></i>
                    &nbsp;上传头像
                </div>
                <input id="file" name="file" type="file" onchange="UpImgChangeToop(this)"/>
            </form>
        </div>
        <!-- 基本信息模块 -->
        <div class="Inf Box clearfix">
            <h4 class="h4">基本信息</h4>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">广告名称</h5>
                <input type="text" style="width: 90%" name="ad_name" value="<?php echo $info['ad_name']; ?>" placeholder="广告名称">
            </div>
           
        </div>
     
     
        <div class="ConFee Box clearfix">
            <div class="Box2" style="width: 100%">
                <h5 class="h5">栏目</h5>
                <!-- 栏目分类 -->
                <select name="colum_id" id="is_open_image" style="width: 24%">
                        <?php if(is_array($colum) || $colum instanceof \think\Collection || $colum instanceof \think\Paginator): if( count($colum)==0 ) : echo "" ;else: foreach($colum as $key=>$vo): ?>
                        <option value="<?php echo $vo['id']; ?>" <?php if($info['colum_id'] ==  $vo['id']): ?> selected <?php endif; ?>><?php echo $vo['title']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
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
    //alert('<?php echo $QnToken; ?>');
    var T = new Tool();
    Popups = new Popups();
    //创建用来读取此文件的对象(具体二进制内容)
    var reader = new FileReader();

    //头像上传预览图生成
    function UpImgChangeToop(dom) {
        //.get()将dom对象转换为转为原生对象
        var file = $(dom).get(0).files[0];
        //将文件读取为 DataURL
        reader.readAsDataURL(file);
        reader.onload = function (e) {
            //console.log(e.target.result);
            var data = e.target.result;
            $('#PortraitImg').attr('src',data);
        }
    }

    //图片放大效果
    function imgmag(dom) {
        Popups.ImgMag(dom,500);
    }

    //数据修改提交
    function SubmitData() {
        Popups.Confirm(500,"确认提交？","提交","不提交",callfun1,callfun2);
        function callfun1() {
            //数据封装
            var data = {};
            //医生头像上传
            var file = $('#file').get(0).files[0];
            //检查input[type=file]是否为空
            if(T.Empty(file)) {
                data['img_url'] = "";
            } else {
                //获取图片信息
                var UpImgName = file.name;
                var UpImgType = file.type;
                var UpImgSize = file.size;
                var formData = new FormData($('#UpImg-Portrait_img')[0]); //把数据封装成FormData对象
                //图片上传七牛云
                $.ajax({
                    //将信息传递至七牛云
                    url: 'http://up.qiniu.com',
                    type: 'post',
                    data: formData,
                    async: false,  
                    cache: false,  
                    contentType: false,  
                    processData: false,  
                    success: function(returndata) {  
                        data['img_url'] = "http://cdn.uyihui.cn/" + returndata['key'];
                    },
                    beforeSend: function(XMLHttpRequest){
                        console.log("等待七牛云服务器返回值中....");
                    },
                    error: function(returndata) { 
                        console.log(returndata);
                    }  
                });
            }
            
            // 医生id
            data['id'] = T.GetData('id');
            // 医生基本信息
            data['ad_name'] = $("input[name='ad_name']").val();
            data['colum_id'] = $("select[name='colum_id']").val();
            // 医生科室信息
            // data['hospital'] = $("input[name='hospital']").val();

            
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