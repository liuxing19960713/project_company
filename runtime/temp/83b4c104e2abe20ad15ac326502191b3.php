<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:70:"/var/www/html/Unkonwn/public/../application/admin/view/event/edit.html";i:1541563433;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1541563429;}*/ ?>
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
                    <h5>编辑活动</h5>
                </div>
                <div class="ibox-content">
                    <form id="form" method="post" action="" class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $show['event_id']; ?>"/>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">医生ID：</label>
                            <div class="input-group col-sm-1">
                                <input id="views_count" type="text" class="form-control" name="doctor_id" value="<?php echo $show['doctor_id']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">医生姓名：</label>
                            <label class="col-sm-7 control-label" style="text-align:left; padding-left:0;"><?php echo $show['doctor_name']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">医生照片：</label>
                            <label class="col-sm-7 control-label" style="text-align:left; padding-left:0;">
                            	<?php if(!empty($show['doctor_avatar'])): ?>
                                <img src="<?php echo $show['doctor_avatar']; ?>" width="135" />
                                <?php else: ?>
                                <img width="135" src="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png" />
                                <?php endif; ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">活动标题：</label>
                            <div class="input-group col-sm-7">
                                <input id="title" type="text" class="form-control" name="title" value="<?php echo $show['title']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">视频地址：</label>
                            <div class="input-group col-sm-7">
                                <input id="title" type="text" class="form-control" name="video_url"  value="<?php echo $show['video_url']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">参与人数：</label>
                            <div class="input-group col-sm-2">
                                <input id="join_count" type="text" class="form-control" name="join_count" value="<?php echo $show['join_count']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">状态：</label>
                            <div class="input-group col-sm-7" style="padding-top:5px;">
                                <label style="margin-right:15px;"><input type="radio" class="rdo" name="event_status" value="进行中" > <span class="spans">进行中</span></label>
                                <label><input type="radio" class="rdo" name="event_status" value="已结束" > <span class="spans">已结束</span></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">开始时间~~结束时间：</label>
                            <div class="input-group col-sm-7">
                                <input class="form-control" type="text" id="start_time" placeholder="请选择"  name="start_time" style="width:200px" value="<?php echo date('Y-m-d H:i:s',$show['start_time']); ?>" readonly/>
                                <span class="col-lg-3" style="width: 50px;line-height:30px;">--</span>
                                <input class="form-control" id="end_time"  type="text" name="end_time" style="width:200px" value="<?php echo date('Y-m-d H:i:s',$show['end_time']); ?>" readonly/>
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
    require(['app', 'jedate', 'event/edit'],function (app, jedates, create){
        $("#start_time").jeDate({
            isinitVal:true,
            //festival:true,
            ishmsVal:false,
            minDate: '2016-06-16 23:59',
            maxDate: $.nowDate({DD:5}),
            format:"YYYY-MM-DD hh:mm:ss",
            zIndex:3000,
        });

        $("#end_time").jeDate({
            isinitVal:true,
            //festival:true,
            ishmsVal:false,
            minDate: '2016-06-16 23:59',
            maxDate: $.nowDate({DD:5}),
            format:"YYYY-MM-DD hh:mm:ss",
            zIndex:3000,
        });
        $("input[name='event_status'][value='<?php echo $show['event_status']; ?>']").attr("checked",true);
        create();
    });
</script>
</body>
</html>
