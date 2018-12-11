<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:77:"/var/www/html/Unkonwn/public/../application/admin/view/information/index.html";i:1541563434;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1541563429;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>成功案例</title>
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
    <!-- Panel Other -->
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>咨询列表</h5>
        </div>
        <div class="ibox-content">
        	<form id='commentForm' role="form"   method="get" action="" class="form-inline" style="width:100%;">
                <div style="width:10%; float:left;">
                    <div class="filter-box">
                        <div class="filter-text">
                            <input class="filter-title" type="text" readonly />
                            <i class="icon icon-filter-arrow"></i>
                        </div>
                        <select name="filter" id="filter">
                            <option value="图文咨询">图文咨询</option>
                            <option value="电话咨询">电话咨询</option>
                            <option value="视频咨询">视频咨询</option>
                            <option value="使用优惠券咨询">使用优惠券咨询</option>
                        </select>
                    </div>
                </div>
                <!--搜索框开始-->
                <div class="content clearfix m-b pull-right">
                	<div class="form-group" style="margin-right:20px;">
                        <label style="float:left; margin-top:8px;">时间间隔：</label>
                        <input class="form-control col-lg-4" type="text" id="start_time" style="width: 200px;" name="start_time" value="<?php echo $start_time; ?>" readonly/>
                        <span class="col-lg-3" style="width: 50px;line-height:30px;">--</span>
                        <input class="form-control col-lg-4" id="end_time"  type="text" style=" width: 200px;" name="end_time" value="<?php echo $end_time; ?>" readonly/>
                    </div>
                    <div class="form-group">
                        <label>医生名：</label>
                        <input type="text" class="form-control" id="title" name="searchText">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" style="margin-top:5px" id="search"><strong>搜 索</strong>
                        </button>
                    </div>
                </div>
            </form>
            <!--搜索框结束-->

            <div class="example-wrap">
                <div class="example">
                      <table class="table">
                          <thead>
                              <th width="150" style="text-align:center;">ID</th>
                              <th style="text-align:center;">医生名</th>
                              <th style="text-align:center;">用户名</th>
                              <th width="150" style="text-align:center;">金额</th>
                              <?php if(($filter == '图文咨询') OR ($filter == '')): ?>
                              <th style="text-align:center;">是否使用优惠券</th>
                              <?php endif; ?>
                              <th width="200">发起时间</th>
                              <?php if(($filter == '电话咨询') OR ($filter == '视频咨询')): ?>
                              <th width="200">预约时间</th>
                              <th width="200" style="text-align:center;">预约时长</th>
                              <?php endif; ?>
                              <th width="150" style="text-align:center;">咨询状态</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['con_id']; ?></td>
                              <td align="center"><a href="/admin/doctor/doctoredit/id/<?php echo $vo['d_id']; ?>.html" style="color:#337ab7"><?php echo $vo['doctor_name']; ?></a></td>
                              <td align="center"><a href="/admin/user/useredit/id/<?php echo $vo['c_id']; ?>.html" style="color:#337ab7"><?php echo $vo['user_name']; ?></a></td>
                              <td align="center"><?php echo $vo['money']; ?></td>
                              <?php if(($filter == '图文咨询') OR ($filter == '')): ?>
                              <td align="center"><?php echo $vo['uc_id']; ?></td>
                              <?php endif; ?>
                              <td align="center"><?php echo date('Y-m-d H:i:s',$vo['create_time']); ?></td>
                              <?php if(($filter == '电话咨询') OR ($filter == '视频咨询')): ?>
                              <td align="center"><?php echo date('Y-m-d H:i:s',$vo['appoint_time']); ?></td>
                              <td align="center">进行时间:<font color="#18a689"><?php echo total_time($vo['total_time']); ?></font></td>
                              <?php endif; ?>
                              <td align="center"><?php echo $vo['state']; ?></td>
                          </tr>
                          <?php endforeach; endif; else: echo "" ;endif; ?>
                      </table>
                      <span style="margin-top:10px; float:left;">总共<?php echo $count; ?>条记录</span>
                      <div style="clear:both"></div>
                      <div id="page" style="text-align: right"><?php echo $list->render();; ?></div>
                      <input type="hidden" name="count" value="<?php echo $count; ?>">
                </div>
            </div>
            <!-- End Example Pagination -->
        </div>
    </div>
</div>
<!-- End Panel Other -->
</div>
</body>
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
<script type="text/javascript" src="__JS__/jquery.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<script>
    require(['app', 'jedate'],function (app, jedates){
        $("#start_time").jeDate({
            isinitVal:false,
            //festival:true,
            ishmsVal:false,
            minDate: '2016-06-16 23:59',
            maxDate: $.nowDate({DD:5}),
            format:"YYYY-MM-DD hh:mm:ss",
            zIndex:3000,
        });

        $("#end_time").jeDate({
            isinitVal:false,
            //festival:true,
            ishmsVal:false,
            minDate: '2016-06-16 23:59',
            maxDate: $.nowDate({DD:5}),
            format:"YYYY-MM-DD hh:mm:ss",
            zIndex:3000,
        });
		$(".filter-title").attr("placeholder","图文咨询");  
		
		<?php if($filter != ''): ?>
		$("select option[value='<?php echo $filter; ?>']").attr("selected", "selected");
		$(".filter-title").attr("placeholder","<?php echo $filter; ?>");  
		<?php endif; ?>
		//$("#filter").find("option:contains('3')").attr("selected",true);

        list();
    });
	
	$('.filter-box').selectFilter({
		callBack : function (val){
			//返回选择的值
			//console.log(val+'-是返回的值');
			location.href = "<?php echo url('information/index'); ?>"+"?filter="+val;
		}
	});
</script>



</html>
