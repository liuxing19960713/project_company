<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/withdraw/edit.html";i:1535617243;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1535617233;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1535617233;}*/ ?>
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
                    <h5>提现管理</h5>
                </div>
                <div class="ibox-content">
                    <form id="form" method="post" action="" class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $article['wd_id']; ?>"/>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户ID：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0; padding-top:9px;"><?php echo $article['user_id']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户姓名：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0;"><?php echo $article['userInfo']['nick_name']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" >提现金额：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0; padding-top:9px;"><?php echo $article['money']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">银行：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0;"><?php echo $article['bank_name']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">银行账户：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0; padding-top:9px;"><?php echo $article['bank_account']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">持卡人姓名：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0;"><?php echo $article['user_name']; ?></label>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" >绑定手机号：</label>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0; padding-top:9px;"><?php echo $article['user_mobile']; ?></label>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-2 control-label" >状态：</label>
                            <?php if($article['status']=="wait"): ?>
                            <div class="input-group col-sm-7" style="padding-top:5px;">
                                <label style="float:left; margin-right:15px;"><input type="radio" class="rdo" name="status" value="yes" > <span class="spans">通过</span></label>
                                <label style="float:left; margin-right:15px;"><input type="radio" class="rdo" name="status" value="wait" > <span class="spans">审核中</span></label>
                                <label><input type="radio" class="rdo" name="status" value="no" > <span class="spans">拒绝</span></label>
                            </div>
                            <?php else: ?>
                            <label class="col-sm-8 control-label" style="text-align:left; padding-left:0;">
                            	<?php switch($article['status']): case "yes": ?>
                                <font color="#1ab394">通过</font>
                                <?php break; case "no": ?>
                                <font color="#ec4758">拒绝</font>
                                <?php break; endswitch; ?>
                            </label>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" >审核人：</label>
                            <div class="input-group col-sm-2">
                                <input id="admin_name" type="text" class="form-control" name="admin_name" required aria-required="true" value="<?php echo $article['admin_name']; ?>">
                            </div>
                        </div>
                        
                        <?php if($article['status']=="no"): ?>
                        <div class="form-group" id="zj">
                            <label class="col-sm-2 control-label">拒绝原因</label>
                            <div class="input-group col-sm-8">
                                <textarea name="reason" id="reason" class="form-control" style="height: 100px;"><?php echo $article['reason']; ?></textarea>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group" id="bz">
                            <label class="col-sm-2 control-label">备注：</label>
                            <div class="input-group col-sm-8">
                            	<textarea class="form-control" type="text" id="remarks" style="height: 100px;" name="remarks" ><?php echo $article['remarks']; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-8">
                        		<?php if($article['status']=="wait"): ?>
                                <button class="btn btn-primary" type="submit">确认提交</button>
                        		<?php endif; ?>
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
    require(['app', 'withdraw/edit'],function (app, edit){
        edit();
        $("input[name='status'][value='<?php echo $article['status']; ?>']").attr("checked",true);

        $(".rdo").change(function() {
            var $selectedValue = $("input[name='status']:checked").val();
//            alert($selectedValue);
            if ($selectedValue == 'no') {
                $("#bz").before('<div class="form-group wouldHidden" id="zj"><label class="col-sm-2 control-label">拒绝原因：</label><div class="input-group col-sm-8"><textarea name="reason" id="reason" class="form-control" style="height: 100px;"></textarea></div></div>');
            } else {
                $("#zj").remove();
            }
        });

    });
</script>

</body>
</html>
