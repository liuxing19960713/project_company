<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:72:"/var/www/html/Unkonwn/public/../application/admin/view/appoint/edit.html";i:1535617231;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1535617233;s:67:"/var/www/html/Unkonwn/public/../application/admin/view/base/js.html";i:1535617233;}*/ ?>
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
                    <h5>编辑成功率文章</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered">
                        <tr>
                            <th class="text-right">姓名</th>
                            <td><?php echo $info['real_name']; ?></td>
                            <th class="text-right">年龄</th>
                            <td><?php echo $info['age']; ?>&nbsp;岁</td>
                            <td rowspan="4" width="150px" height="150px">
                                <?php if(!empty($info['avatar'])): ?>
                                <a href="javascript:void(0)" class="js-thumb" data-thumb="<?php echo $info['avatar']; ?>"> <img src="<?php echo $info['avatar']; ?>" width="150px" height="150px" /></a>
                                <?php else: ?>
                                <a href="javascript:void(0)" class="js-thumb" data-thumb="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png"><img width="150px" height="150px"src="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png" /></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-right">性别</th>
                            <td><?php if($info['gender'] == 'male'): ?>男<?php else: ?>女<?php endif; ?></td>
                            <th class="text-right">职业</th>
                            <td><?php echo $info['career']; ?></td>
                        </tr>
                        <tr>
                            <th class="text-right">手机号 </th>
                            <td><?php echo $info['mobile']; ?></td>
                            <th class="text-right">血型 </th>
                            <td><?php echo $info['blood_type']; ?> &nbsp;型</td>
                        </tr>
                        <tr>
                            <th class="text-right">婚姻状况 </th>
                            <td><?php echo $info['marriage']; ?></td>
                            <th class="text-right">挂单号创建时间</th>
                            <td ><?php echo date('Y-m-d H:i:s',$info['create_time']); ?></td>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-center" style="font-size:20px;">用户个人挂号详细信息</th>
                        </tr>
                        <tr>
                            <th class="text-right">预约时间</th>
                            <td colspan="4"><?php echo date('Y-m-d H:i:s',$info['appoint_time']); ?></td>
                        </tr>
                        <tr>
                            <th class="text-right">预计咨询费用：</th>
                            <td><?php echo $info['money']; ?> &nbsp;元</td>
                            <td class="text-right"><b>咨询医生详细信息：</b></td>
                            <td colspan="2"><a href="<?php echo url('Doctor/details'); ?>?id=<?php echo $info['doctor_id']; ?>"><?php echo $info['nick_name']; ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>医院名称：</b></td>
                            <td class="text-left"><?php echo $info['hospital']; ?></td>
                            <td class="text-right"><b>医生职称:</b></td>
                            <td colspan="2"><?php echo $info['title']; ?></td>
                        </tr>
                        <tr>
                            <th class="text-right">预约状态：</th>
                            <td colspan="4">
                                <?php switch($info['status']): case "yes": ?>
                                    预约成功
                                    <?php break; case "no": ?>
                                    预约失败
                                    <?php break; case "wait": ?>
                                    预约中....
                                    <?php break; case "end": ?>
                                    预约结束
                                    <?php break; endswitch; ?>
                            </td>
                        </tr>
                        <?php if($info['status'] == 'no'): ?>
                        <tr>
                            <th class="text-right">拒绝原因：</th>
                            <td colspan="4"><?php echo $info['reason']; ?></td>
                        </tr>
                        <?php endif; if($info['status'] != 'no'): ?>
                        <tr>
                            <th class="text-center" rowspan="8" style="vertical-align: middle;"><h3>用户个人病历详情：</h3></th>
                        </tr>
                        <tr>
                            <td class="text-left" width="150px;"><b>是否饮酒:</b>&nbsp;&nbsp;<?php if($info['drink'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td class="text-left"><b>是否有生育史:</b>&nbsp;&nbsp;<?php if($info['is_born'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td class="text-left"><b>生育方式:</b>&nbsp;&nbsp;<?php echo $info['born_type']; ?></td>
                            <td colspan=""><b>生育时间：</b>&nbsp;&nbsp;<br/><?php if($info['born_time'] != ''): ?><?php echo date('Y-m-d H:i',$info['born_time']); endif; ?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><b>是否动过手术：</b><?php if($info['operation_history'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td class="text-left"><b>是否抽烟：</b><?php if($info['smoke'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td class="text-left"><b>是否过敏:</b>&nbsp;&nbsp;<?php if($info['is_allergy'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td colspan=""><b>药物过敏史：</b><?php echo $info['allergy']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-left"><b>是否有遗传病史:</b><?php if($info['has_genetic_disease'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td colspan="3"><b>遗传病描述：</b><?php echo $info['genetic_disease']; ?></td>
                        </tr>
                        <?php if($info['gender'] == 'male'): ?>
                        <tr>
                            <td class="text-left"><b>是否手淫:</b><?php if($info['masturbation_history'] == 'yes'): ?>是<?php else: ?>否<?php endif; ?></td>
                            <td class="text-left"><b>精子浓度:</b><?php echo $info['semen_density']; ?></td>
                            <td class="text-right"><b>精液量:</b><?php echo $info['semen_volume']; ?></td>
                            <td colspan=""></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-left"><b>禁欲天数：</b><?php echo $info['abstinent_days']; ?> 天</td>
                            <td><b>备孕时间：</b><?php echo $info['prepare_pregnant_time']; ?> 天</td>
                            <td colspan="2"><b>诊断科室：</b><?php echo $info['department']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="4"><b>病情描述：</b><?php echo $info['content']; ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    <div class="form-group" style="margin-top:20px;">
                        <div class="col-sm-4 col-sm-offset-8">
                            <a class="btn btn-default" href="javascript:history.back(-1)" style="float:right;">返回</a>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
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
