<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/user/useredit.html";i:1541572129;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1541563429;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>编辑文章</title>
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
                    <h5>用户信息</h5>
                </div>
                <div class="ibox-content">
                    <form id="form" method="post" action="" class="form-horizontal" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo $article['user_id']; ?>"/>
                        <input type="hidden" name="url" value="<?php echo $_SERVER['HTTP_REFERER']; ?>"/>
                        
                        <div class="row-fluid">
                        
                          <table class="table table-bordered">
                              <tr>
                                  <td width="200">头像</td>
                                  <td>用户信息</td>
                                  <td width="350">用户试管记录</td>
                              </tr>
                              <tr>
                                  <td align="center">
                                      <?php if(!empty($article['avatar'])): ?>
                                      <img src="<?php echo $article['avatar']; ?>" width="135" />
                                      <?php else: ?>
                                      <img width="135" src="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png" />
                                      <?php endif; ?>
                                  </td>
                                  
                                  <td rowspan="4" style=" vertical-align:top;">
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">昵称：</label>
                                        <div class="input-group col-sm-7">
                                            <input type="text" class="form-control" name="nick_name" required aria-required="true" value="<?php echo $article['nick_name']; ?>">
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">真实姓名：</label>
                                        <div class="input-group col-sm-7">
                                            <input type="text" class="form-control" name="real_name" value="<?php echo $article['real_name']; ?>">
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">手机号：</label>
                                        <div class="input-group col-sm-7">
                                            <input type="text" class="form-control" name="mobile" value="<?php echo $article['mobile']; ?>" onkeyup="this.value=this.value.replace(/[^0-9]/g,'')" maxlength="11" />
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">生日：</label>
                                        <div class="input-group col-sm-7">
                                            <input type="text" class="form-control" name="birthday" value="<?php echo date('Y-m-d',$article['birthday']); ?>" />
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">职业：</label>
                                        <div class="input-group col-sm-7">
                                            <input type="text" class="form-control" name="career" value="<?php echo $article['career']; ?>" />
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">账户余额：</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="money" value="<?php echo $article['money']; ?>" id="money" style="width:100px;" onkeyup="this.value=this.value.replace(/[^(0-9+\.0-9{2}?$)]/g,'')" maxlength="10">
                                        </div>
                                    </div>
                                  	<div class="form-group" style="margin-bottom:0;">
                                        <label class="col-sm-2 control-label">性别：</label>
                                        <div class="input-group col-sm-7" style="padding-top:5px;">
                                            <label style="float:left; margin-right:15px;"><input type="radio" class="rdo" name="gender" value="male" > <span class="spans">男</span></label>
                                            <label><input type="radio" class="rdo" name="gender" value="female" > <span class="spans">女</span></label>
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">婚姻状况：</label>
                                        <div class="input-group col-sm-7" style="padding-top:5px;">
                                            <label style="float:left; margin-right:15px;"><input type="radio" class="rdo" name="marriage" value="未婚" > <span class="spans">未婚</span></label>
                                            <label><input type="radio" class="rdo" name="marriage" value="已婚" > <span class="spans">已婚</span></label>
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">血型：</label>
                                        <div class="input-group col-sm-7">
                                            <select class="form-control" style="width:100px;" name="blood_type" id="blood_type">
                                                <option value=""></option>
                                                <option value="A">A 型</option>
                                                <option value="B">B 型</option>
                                                <option value="O">O 型</option>
                                                <option value="AB">AB 型</option>
                                            </select>
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">年龄：</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="age" value="<?php echo $article['age']; ?>" id="age" style="width:100px;">
                                            <div class="input-group-addon" style="width:40px;font-size:20px;float:left;">岁</div>
                                        </div>
                                    </div>
                                  	<div class="form-group">
                                        <label class="col-sm-2 control-label">地区：</label>
                                        <div class="input-group">
                                            <input class="form-control col-lg-4" type="text" style="width: 150px;" name="province" value="<?php echo $article['province']; ?>" />
                                            <span class="col-lg-1" style="margin-top:8px;">-</span>
                                            <input class="form-control col-lg-4" type="text" style=" width: 150px;" name="city" value="<?php echo $article['city']; ?>" />
                                        </div>
                                    </div>
                                  </td>
                                  
                                  <td rowspan="4" style="vertical-align:top;">
                                  	<?php
                                    $textArray = ['0' => '前期准备','1' => '降调','2' => '促排','3' => '取卵','4' => '移植','5' => '验孕',];
                                    ?>
                                  	<table class="table table-bordered">
                                      <tr>
                                        <td align="center">所处阶段</td>
                                        <td align="center">完成百分比</td>
                                        <td align="center">操作</td>
                                      </tr>
                                      <?php if(is_array($tube) || $tube instanceof \think\Collection || $tube instanceof \think\Paginator): if( count($tube)==0 ) : echo "" ;else: foreach($tube as $key=>$vo): ?>
                                      <tr>
                                        <td align="center"><?php echo $textArray[$vo['tube_stage']]; ?></td>
                                        <td align="center"><?php echo $vo['tube_stage_value']; ?>%</td>
                                        <td align="center"><a href="record?id=<?php echo $article['user_id']; ?>&tub=<?php echo $vo['tube_stage']; ?>" class="btn btn-success">查看更多</a></td>
                                      </tr>
                                      <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </table>
                                  </td>
                                  
                                  
                              </tr>
                              <tr>
                                  <td align="center">
                                      <b>绑定的第三方账户:
                                          <?php switch($article['oauth_type']): case "wechat": ?>
                                          <a data-toggle="tooltip" title="wechat">微信</a>
                                          <?php break; case "qq": ?>
                                          <a data-toggle="tooltip" title="qq">QQ</a>
                                          <?php break; default: ?><a data-toggle="tooltip" title="未绑定">未绑定</a>
                                          <?php endswitch; ?>
                                      </b>
                                  </td>
                              </tr>
                              <tr>
                                  <td align="center"><b>粉丝:<?php echo $article['fans_count']; ?></b>&nbsp;&nbsp;&nbsp;&nbsp;<b>关注数量 :<?php echo $article['follow_count']; ?></b> </td>
                              </tr>
                              <tr><td align="center" height="300"></td></tr>
                          </table>
                        </div>
                        
                        <div class="form-group" style="margin-top:20px;">
                                <a href="<?php echo url('User/consultation', ['id'=>$article['user_id']]); ?>" class="btn btn-danger" style="margin-left:18px;">问诊报告</a>
                                <a href="<?php echo url('User/user_details', ['id'=>$article['user_id']]); ?>" class="btn btn-success">用户病历</a>
                        		<a class="btn btn-default" href="javascript:history.back(-1)"  style="float:right; margin:0 18px 0 10px;">返回</a>
                                <button class="btn btn-primary" type="submit" style="float:right;">确认提交</button>
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
    require(['app', 'user/details'],function (app, list){
        list();
        $("input[name='gender'][value='<?php echo $article['gender']; ?>']").attr("checked",true);
        $("input[name='marriage'][value='<?php echo $article['marriage']; ?>']").attr("checked",true);
        $("#blood_type").val("<?php echo $article['blood_type']; ?>");
    });
</script>
</body>
</html>
