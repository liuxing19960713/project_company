<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:70:"/var/www/html/Unkonwn/public/../application/admin/view/banner/add.html";i:1535617647;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1535617233;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1535617233;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>添加案例</title>
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
                    <div id="form" class="form-horizontal">
                        <!-- 安卓 -->
                        <h3>安卓版本</h3>
                        <input id="filter" type="hidden" name="filter" value="<?php echo $filter; ?>"/>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">优先级：</label>
                            <div class="input-group col-sm-1">
                                <input id="order_num" type="text" class="form-control" name="order_num" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">缩略图：</label>
                            <input name="img_url" id="img_url" type="hidden"/>
                            <div class="form-inline"  id="container">
                                <div class="input-group col-sm-2">
                            		<img id="imgshow" name="img_url" style="width: 300px; min-height:100px;" />
                                    <button type="button" class="layui-btn" id="pickfile" style="margin-top:20px;">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                </div>
                                <div class="input-group col-sm-3">
                                    <div id="sm"></div>
                                </div>
                            </div>
                        </div>
                        <!-- ios -->
                        <h3>IOS版本</h3>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">缩略图：</label>
                            <input name="img_url2" id="img_url2" type="hidden"/>
                            <div class="form-inline"  id="container">
                                <div class="input-group col-sm-2">
                            		<img id="imgshow2" name="img_url2" style="width: 300px; min-height:100px;" />
                                    <button type="button" class="layui-btn" id="pickfile2" style="margin-top:20px;">
                                        <i class="layui-icon">&#xe67c;</i>上传图片
                                    </button>
                                </div>
                                <div class="input-group col-sm-3">
                                    <div id="sm"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">跳转地址：</label>
                            <div class="input-group col-sm-7">
                                <input id="href_url2" type="text" class="form-control" name="href_url2" >
                            </div>
						</div>
						<!-- 确认提交 -->
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-8">
                                <button id="OK" class="btn layui-btn" >确认提交</button>
                        		<a class="btn layui-btn" href="javascript:history.back(-1)">返回</a>
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
	
    require(['app','qiniuUploader','banner/create'],function (app,qiniuUploader,create){
		function qiniuUploader(elementImg, elementInput, isMulti, browseBt){
			var uploader = Qiniu.uploader({
				runtimes: 'html5,flash,html4',      // 上传模式,依次退化
				browse_button: browseBt,         // 上传选择的点选按钮，**必需**
				uptoken_url: appConfig.publicPath + '/index.php/api/Index/uptoken',  //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
				get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
				unique_names: true,              // 默认 false，key 为文件名。若开启该选项，JS-SDK 会为每个文件自动生成key（文件名）
				// save_key: true,                  // 默认 false。若在服务端生成 uptoken 的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
				domain: appConfig.qiniuDomain,     // bucket 域名，下载资源时用到，如：'http://xxx.bkt.clouddn.com/' **必需**
				container: 'container',             // 上传区域 DOM ID，默认是 browser_button 的父元素，
				max_file_size: '4mb',             // 最大文件体积限制
				flash_swf_url: appConfig.publicPath + '/static/component/plupload/js/Moxie.swf',  //引入 flash,相对路径
				max_retries: 3,                     // 上传失败最大重试次数
				filters: {
					mime_types: [
						//只允许上传图片文件 （注意，extensions中，逗号后面不要加空格）
						{ title: "图片文件", extensions: "jpg,gif,png,bmp,jpeg" }
					]
				},
				dragdrop: true,                     // 开启可拖曳上传
				drop_element: 'container',          // 拖曳上传区域元素的 ID，拖曳文件或文件夹后可触发上传
				chunk_size: '4mb',                  // 分块上传时，每块的体积
				auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传,
				multi_selection: isMulti,             // 设置一次只能选择一个文件
				init: {
					'FilesAdded': function(up, files) {
						plupload.each(files, function(file) {
							// 文件添加进队列后,处理相关的事情
						});
					},
					'BeforeUpload': function(up, file) {
						// 每个文件上传前,处理相关的事情
						$.loader(true);
					},
					'UploadProgress': function(up, file) {
						// 每个文件上传时,处理相关的事情
						// console.log(file.percent);
					},
					'FileUploaded': function(up, file, info) {
						// 每个文件上传成功后,处理相关的事情
						// 其中 info 是文件上传成功后，服务端返回的json，形式如
						// {
						//    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
						//    "key": "gogopher.jpg"
						//  }
						// 参考http://developer.qiniu.com/docs/v6/api/overview/up/response/simple-response.html
						if(browseBt=="pickfile"){
							var domain = up.getOption('domain');
							var res = $.parseJSON(info);
							var sourceLink = domain + res.key; //获取上传成功后的文件的Url
							elementImg.attr("src", sourceLink);
							elementInput.val(sourceLink);
						}else{
							var domain = up.getOption('domain');
							var res = $.parseJSON(info);
							var sourceLink = domain + res.key; //获取上传成功后的文件的Url
							elementImg.attr("src", sourceLink);
							elementInput.val(sourceLink);
						}
						
						$.loader(false);
						
					},
					'Error': function(up, err, errTip) {
						//上传出错时,处理相关的事情
						$.loader(false);
						BootstrapDialog.show({
							type: BootstrapDialog.TYPE_DANGER,
							title: '图片上传失败',
							message: errTip,
							buttons: [{
								label: '关闭',
								action: function(dialogItself){
									dialogItself.close();
								}
							}]
						});
					},
					'UploadComplete': function() {
						//队列文件处理完毕后,处理相关的事情
					}
				}
			});
			return;
		};
        qiniuUploader($("#imgshow"), $("#img_url"), false, "pickfile");
		qiniuUploader($("#imgshow2"), $("#img_url2"), false, "pickfile2");
		create();
	});
</script>
<script src="__JS__/jquery.min.js"></script>
<script>
	//轮播图新增
	$('#OK').click(function(){
		var ImgData = {
			"filter": $('#filter').val(),
			"order": $('#order_num').val(),
			"img_1": $('#img_url').val(),
			"img_2": $('#img_url2').val(),
			"href": $('#href_url2').val()
		};
		$.ajax({
			type: 'post',
			url: 'http://unkonwn.uyihui.cn/admin/Banner/addsdk',
			data: { Add: ImgData },
			dataType: 'json',
			success: function(e) {
				if(e=='1') {
					alert("轮播图添加成功");
					window.location.href = "javascript:history.back(-1)";
				}
			},
			error: function(e) {
				alert('服务器返回错误');
			}
		});
	});
</script>
</body>
</html>
