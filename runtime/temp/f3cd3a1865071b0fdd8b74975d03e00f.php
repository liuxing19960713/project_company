<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/increase/edit.html";i:1541560792;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1535617233;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1535617233;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>编辑案例</title>
<link rel="shortcut icon" href="favicon.ico">
<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="__CSS__/font-awesome.min.css?v=4.4.0" rel="stylesheet">
<link href="__CSS__/animate.min.css" rel="stylesheet">
<link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
<link href="__CSS2__/wangEditor/dist/css/wangEditor.min.css" rel="stylesheet">
<link href="__CSS2__/bootstrap3/dist/css/jedate.css" rel="stylesheet">
<link href="__JS__/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet">
<link href="__JS__/layui/css/layui.css"rel="stylesheet">
<link href="__CSS__/selectFilter.css?v=3.3.6" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-10">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>编辑文章</h5>
                </div>
                <div class="ibox-content">
                    <form id="form" method="post" action="" class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $show['in_id']; ?>"/>
                        <input type="hidden" name="url" value="<?php echo $_SERVER['HTTP_REFERER']; ?>"/>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">文章标题：</label>
                            <div class="input-group col-sm-7">
                                <input id="title" type="text" class="form-control" name="title" value="<?php echo $show['title']; ?>">
                            </div>
                        </div>
                        <!--<div class="form-group">
                            <label class="col-sm-3 control-label">关键字：</label>
                            <div class="input-group col-sm-7">
                                <input id="keyword" type="text" class="form-control" name="keyword" value="<?php echo $show['keyword']; ?>">
                            </div>
                        </div>-->
						
                        <div class="form-group">
                            <label class="col-sm-3 control-label">医生ID：</label>
                            <div class="input-group col-sm-1">
                                <input id="doctor_id" type="text" class="form-control" name="doctor_id"  value="<?php echo $show['doctor_id']; ?>">
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label class="col-sm-3 control-label">点击率：</label>
                            <div class="input-group col-sm-1">
                                <input id="views_count" type="text" class="form-control" name="views_count"  value="<?php echo $show['views_count']; ?>">
                            </div>
                        </div>
						
                        <!--<div class="form-group">
                            <label class="col-sm-3 control-label">缩略图：</label>
                            <input name="img_url" id="img_url" value="<?php echo $show['img_url']; ?>" type="hidden"/>
                            <div class="form-inline"  id="container">
                                <div class="input-group col-sm-2">
                            		<img id="imgshow" name="img_url" src="<?php echo $show['img_url']; ?>"  style="width: 100px;height:100px; margin-right:5px;" />
                                    <button type="button" class="layui-btn" id="pickfile">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                </div>
                                <div class="input-group col-sm-3">
                                    <div id="sm"></div>
                                </div>
                            </div>
                        </div>-->
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">文章内容：</label>
                            <div class="input-group col-sm-7">
                                <textarea name="content"  id="content" style="height:500px;"><?php echo $show['content']; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-8">
                                <button class="btn btn-primary" type="submit">确认提交</button>
                        		<a class="btn btn-default" href="javascript:history.back(-1)">返回</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    // 系统全局变量
    var appConfig = {
        'publicPath' : '<?php echo PUBLIC_PATH; ?>',
        'adminPath' : '<?php echo ADMIN_PATH; ?>',
        'qiniuDomain': '<?php echo \think\Config::get('qiniu.bucketDomain'); ?>',
    };
</script>
<script src="<?php echo PUBLIC_PATH; ?>/static/component/requirejs/require.min.js"></script>
<script>
    var componentPath = appConfig.publicPath + "/static/component/";
    require.config({
        baseUrl: "<?php echo PUBLIC_PATH; ?>/static/js/<?php echo \think\Config::get('app_debug')?'dev' : 'dist'; ?>",
        paths: {
            "jquery":                   componentPath + "jquery/dist/jquery.min",
//        "jquery-ui-sortable":       appConfig.publicPath + "/static/js/lib/jquery-ui-sortable.min", // todo: check it
            "bootstrap":                componentPath + "bootstrap3/dist/js/bootstrap.min",
            "bootstrap-dialog":         componentPath + "bootstrap3-dialog/dist/js/bootstrap-dialog.min",
            "bootstrap-notify":         componentPath + "bootstrap-notify/dist/bootstrap-notify.min",
            "jquery-validate":          componentPath + "jquery-validation/dist/jquery.validate.min",
            'additional-methods':       componentPath + "jquery-validation/dist/additional-methods.min",
            'messages_zh':              componentPath + "jquery-validation/dist/localization/messages_zh.min",
//        'moment':                      componentPath + "bootstrap-daterangepicker-master/moment.min",
//        'daterangepicker':          componentPath + "bootstrap-daterangepicker-master/daterangepicker.min",
            'wangEditor':               componentPath + "wangEditor/dist/js/wangEditor.min",
            'plupload':                 componentPath + "plupload/js/plupload.full.min",
            'qiniu':                    componentPath + "qiniu-js/dist/qiniu.min",
            "qiniuUploader":            './component/qiniuUploader',
            "qiniuUploader1":            './component/qiniuUploader1',
            "editor":                   './component/editor',
            "jedate":                   componentPath + "jedate/jquery.jedate.min",
//            "pager":                    "./framework/pager",
//            "loader":                   "./framework/loader",
            "framework":                appConfig.publicPath + "/static/js/dist/framework"
        },
        shim : {
            "bootstrap" : {
                "deps": ['jquery']
            },
            "bootstrap-notify" : {
                "deps": ['jquery','bootstrap']
            },
            "bootstrap-dialog" : {
                "deps": ["jquery", "bootstrap"],
                "exports": "BootstrapDialog"
            },
//            "jquery-validate" : {
//                "deps": ['additional-methods', 'messages_zh']
//            },
            'plupload': {
                "deps": ['jquery']
            },
            'jedate': {
                "deps": ['jquery']
            },
            'qiniu': {
                "deps": ['jquery', 'plupload']
            },
            'wangEditor': {
                "deps": ['jquery', 'bootstrap', 'plupload', 'qiniu']
            },
            'qiniuUploader': {
                "deps": ['jquery', 'plupload', 'qiniu']
            },
            'editor': {
                "deps": ['jquery', 'bootstrap', 'plupload', 'qiniu', 'wangEditor']
            },
            'pager': {
                "deps": ['jquery', 'bootstrap','bootstrap-dialog','bootstrap-notify']
            },
            'loader': {
                "deps": ['jquery', 'bootstrap','bootstrap-dialog','bootstrap-notify']
            },
            'framework': {
                "deps": ['jquery', 'bootstrap','bootstrap-dialog','bootstrap-notify']
            },
            'nav': {
                "deps": ['jquery', 'bootstrap','bootstrap-dialog','bootstrap-notify']
            },
            'app': {
                "deps": [
                    'jquery',
                    'bootstrap',
                    'bootstrap-dialog',
                    'bootstrap-notify',
                    'jquery-validate',
//                    'additional-methods',
//                    'messages_zh',
                    'nav',
//                    'pager',
//                    'loader',
                    'framework',
                ]
            }
        }
    });
</script>
<script>
    require(['app','editor','qiniuUploader', 'increase/edit'],function (app, editor,qiniuUploader, create){
        qiniuUploader($("#imgshow"), $("#img_url"), false);
        editor('content');
        create();
    });
</script>
</body>
</html>
