<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:77:"/var/www/html/Unkonwn/public/../application/admin/view/user/user_details.html";i:1535617243;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1535617233;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1535617233;}*/ ?>
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
                    <h5>用户病历表</h5>
                </div>
                <div class="ibox-content">
                    <div class="row-fluid">
                      <table class="table table-bordered">
                            <tr>
                                <th class="text-right">姓名</th>
                                <td><?php echo $show['real_name']; ?></td>
                                <th class="text-right">年龄</th>
                                <td><?php echo $show['age']; ?>&nbsp;岁</td>
                                <td rowspan="4" width="150px" height="150px">
                                    <?php if(!empty($show['avatar'])): ?>
                                    <a href="javascript:void(0)" class="js-thumb" data-thumb="<?php echo $show['avatar']; ?>"> <img src="<?php echo $show['avatar']; ?>" width="150px" height="150px" /></a>
                                    <?php else: ?>
                                    <a href="javascript:void(0)" class="js-thumb" data-thumb="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png"><img width="150px" height="150px"src="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png" /></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-right">性别</th>
                                <td><?php if($show['gender'] == 'male'): ?>男<?php else: ?>女<?php endif; ?></td>
                                <th class="text-right">职业</th>
                                <td><?php echo $show['career']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">手机号 </th>
                                <td>XXXXXXXXXX</td>
                                <th class="text-right">血型 </th>
                                <td><?php echo $show['blood_type']; ?> &nbsp;型</td>
                            </tr>
                            <tr>
                                <th class="text-right">婚姻状况 </th>
                                <td><?php echo $show['marriage']; ?></td>
                                <th class="text-right">病历创建时间</th>
                                <?php if(empty($show['create_time'])): ?>
                                <td >暂无</td>
                                <?php else: ?>
                                <td ><?php echo date('Y-m-d H:i:s',$show['create_time']); ?></td>
                                <?php endif; ?>
                            </tr>
    
                            <tr>
                                <th colspan="5" class="text-center" style="font-size:20px;">用户个人检查详细信息</th>
                            </tr>
                            <tr>
                                <th class="text-right">身高</th>
                                <td><?php echo $show['body_height']; ?></td>
                                <th class="text-right">体重</th>
                                <td colspan="2"><?php echo $show['body_weight']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">初潮年龄</th>
                                <td><?php echo $show['menarche_age']; ?></td>
                                <th class="text-right">闭经</th>
                                <td colspan="2"><?php echo $show['amenia']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">有无子女</th>
                                <td><?php echo $show['has_child']; ?></td>
                                <th class="text-right">子女个数</th>
                                <td colspan="2"><?php echo $show['child_num']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">闭经时间</th>
                                <td><?php echo $show['amenia_time']; ?></td>
                                <th class="text-right">上次月经</th>
                                <td colspan="2"><?php echo $show['last_menses']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">月经紊乱</th>
                                <td><?php echo $show['menses_disorder']; ?></td>
                                <th class="text-right">月经天数</th>
                                <td colspan="2"><?php echo $show['menses_days']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">月经量</th>
                                <td><?php echo $show['menses_quantity']; ?></td>
                                <th class="text-right">月经周期</th>
                                <td colspan="2"><?php echo $show['menses_cycle']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">痛经  </th>
                                <td><?php echo $show['menalgia']; ?> </td>
                                <th class="text-right">严重程度</th>
                                <td colspan="2"><?php echo $show['severity']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">避孕方法 </th>
                                <td> <?php echo $show['birth_control_method']; ?></td>
                                <th class="text-right">解除避孕</th>
                                <td colspan="2"><?php echo $show['birth_control_release']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">避孕多长时间？</th>
                                <td> <?php echo $show['birth_control_time']; ?></td>
                                <th class="text-right">怀孕次数 </th>
                                <td colspan="2"><?php echo $show['pregnant_times']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">自然流产</th>
                                <td><?php echo $show['spontaneous_abortion']; ?></td>
                                <th class="text-right"> 人流 </th>
                                <td colspan="2"><?php echo $show['induced_abortion']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">胚胎停育</th>
                                <td><?php echo $show['embryo_damage']; ?></td>
                                <th class="text-right">顺产/刨宫产 </th>
                                <td colspan="2"><?php echo $show['eutocia']; ?></td>
                            </tr>
    
                            <tr>
                                <th class="text-right">性交痛 </th>
                                <td><?php echo $show['sex_pain']; ?></td>
                                <th class="text-right">隐性下腹痛</th>
                                <td colspan="2"><?php echo $show['latent_abdominal_pain']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">性生活</th>
                                <td> <?php echo $show['sex_life']; ?></td>
                                <th class="text-right">大便痛</th>
                                <td colspan="2"><?php echo $show['poo_pain']; ?></td>
                            </tr>
                            <tr>
                                <th class="text-right">所患疾病</th>
                                <td colspan="4" width="900px;"> <?php echo $show['disease']; ?> </td>
                            </tr>
                            <tr>
                                <th class="text-right">病情描述</th>
                                <td colspan="4" width="900px;"><?php echo $show['disease_description']; ?> </td>
                            </tr>
                        </table>
                        <div class="form-group" style="margin-top:20px;">
                            <div class="col-sm-4 col-sm-offset-8">
                        		<a class="btn btn-default" href="JavaScript:history.back(-1)" style="float:right;">返回</a>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
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
</body>
</html>
