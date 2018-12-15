<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:88:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\Article\alert.html";i:1544860960;s:83:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\base\css.html";i:1541563428;s:82:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\base\js.html";i:1541563428;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="__JS__/jquery.min.js"></script>
<title><?php echo $title; ?></title>
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
                    <h5><?php echo $title; ?></h5>
                </div>
                <div class="ibox-content">
                    <form id="form" method="post" action="" class="form-horizontal" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">文章标题：</label>
                            <div class="input-group col-sm-7">
                                <input id="title" type="text" class="form-control" name="title" value="<?php echo $list['title']; ?>" required aria-required="true">
                            </div>
                        </div>
                        
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">关键字：</label>
                            <div class="input-group col-sm-7">
                                <input id="keywords" type="text" class="form-control" name="keywords" required aria-required="true" value="<?php echo $list['keywords']; ?>" >
                            </div>
                        </div>

                         <div class="form-group">
                            <label class="col-sm-3 control-label">浏览器标题：</label>
                            <div class="input-group col-sm-7">
                                <input id="ptitle" type="text" class="form-control" name="ptitle" required aria-required="true" value="<?php echo $list['ptitle']; ?>">
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">缩略图：</label>
                            <input name="pic" id="pic" type="hidden"/>
                            <div class="form-inline"  id="container">
                                <div class="input-group col-sm-2">
                            		<img id="imgshow" name="pic"  style="width: 100px;height:100px; margin-right:5px;" />
                                    <button type="button" class="layui-btn" id="pickfile">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                </div>
                                <div class="input-group col-sm-3">
                                    <div id="sm"></div>
                                </div>
                            </div>
                        </div>
                        <!-- 是否推荐 -->
                     <!--    <div class="form-group">
                            <label class="col-sm-3 control-label">优先级：</label>
                            <div class="input-group col-sm-7" style="padding-top:6px;">
                                <label style="margin-right:15px;"><input type="radio" class="iscom" name="iscom" value="1" > <span class="spans">是</span></label>
                                <label><input type="radio" class="iscom" name="iscom" value="0" checked="checked"> <span class="spans">否</span></label>
                            </div>
                        </div> -->

                         <div class="form-group">
                            <label class="col-sm-3 control-label">是否推荐：</label>
                            <div class="input-group col-sm-7" style="padding-top:6px;">
                                <label style="margin-right:15px;"><input type="radio" class="iscom" name="iscom" value="1" <?php if($list['iscom'] == 1): ?> checked <?php endif; ?> > <span class="spans">是</span></label>
                                <label><input type="radio" class="iscom" name="iscom" value="0" <?php if($list['iscom'] == 0): ?> checked <?php endif; ?>  > <span class="spans">否</span></label>
                            </div>
                        </div>
                        <!-- 是否显示 -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">是否显示：</label>
                            <div class="input-group col-sm-7" style="padding-top:6px;">
                                <label style="margin-right:15px;"><input type="radio" class="shown" name="shown" value="1" <?php if($list['shown'] == 1): ?> checked <?php endif; ?> > <span class="spans">是</span></label>
                                <label><input type="radio" class="shown" name="shown" value="0" <?php if($list['shown'] == 1): ?> checked <?php endif; ?> > <span class="spans">否</span></label>
                            </div>
                        </div>
                        

                          <!-- 添加 标签 -->
                        <div class="form-group" id="tags">
                            <label class="col-sm-3 control-label">添加标签:</label>
                            <div class="input-group col-sm-7" style="padding-top:6px;">
                                <label style="margin-right:15px;">
                                     <?php if(is_array($label) || $label instanceof \think\Collection || $label instanceof \think\Paginator): if( count($label)==0 ) : echo "" ;else: foreach($label as $key=>$vv): ?>

                                     <input type="checkbox" id="check1" value="<?php echo $vv['id']; ?>" <?php if(in_array($vv['id'],$ll)): ?> checked<?php endif; ?> name="label_id[]" class="check"><?php echo $vv['title']; endforeach; endif; else: echo "" ;endif; ?>
                                </label>
                                
                            </div>
                        </div>
                         
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">选择栏目：</label>
                            <div class="input-group  style="padding-top:6px;">
                               <select name="cate"  required aria-required="true" >
                                    <?php if(is_array($cate) || $cate instanceof \think\Collection || $cate instanceof \think\Paginator): if( count($cate)==0 ) : echo "" ;else: foreach($cate as $key=>$v): ?>
                                    <option value="<?php echo $v['id']; ?>" <?php if($list['cate'] == $v['id']): ?> selected <?php endif; ?>><?php echo $v['title']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-3 control-label">文章内容：</label>
                            <div class="input-group col-sm-7" id="editor-container">
                                <textarea name="content"  id="content" style="height:500px;"><?php echo $list['content']; ?></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $list['id']; ?>">

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
    //限制勾选的次数
   $("input:checkbox").click(function(){ 
        var len = $("input:checkbox:checked").length; 
        var id = $("input:checkbox:checked").val(); 
        if(len>2){ 
            alert('亲，最多只能选三个哟~'); 
            return false; //另刚才勾选的取消 
        } 
    })

    // var id_array=new Array();  
    // $('input[name="label_id"]:checked').each(function(){  
    //     id_array.push($(this).val());//向数组中添加元素  
    // });  
    // var idstr=id_array.join(',');//将数组元素连接起来以构建一个字符串  
    // console.log(idstr);  



    require(['app','editor','qiniuUploader', 'Article/edit'],function (app, editor,qiniuUploader, create){
        qiniuUploader($("#imgshow"), $("#pic"), false);

        editor('content');
        create();
    });


</script>
</body>
</html>
