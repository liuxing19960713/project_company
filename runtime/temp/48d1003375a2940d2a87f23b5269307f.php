<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:65:"/var/www/html/Unkonwn/public/../application/admin/view/index.html";i:1544371334;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="renderer" content="webkit">
<meta http-equiv="Cache-Control" content="no-siteapp"/>
<title>后台管理系统</title>
<meta name="keywords" content="">
<meta name="description" content="">

<!--[if lt IE 9]>
<meta http-equiv="refresh" content="0;ie.html"/>
<![endif]-->

<link rel="shortcut icon" href="favicon.ico">
<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
<link href="__CSS__/animate.min.css" rel="stylesheet">
<link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
<!-- 字体图标 -->
<link href="__COMPONENT__/iconfont/iconfont.css" rel="stylesheet">
<!-- 阿里字体图标库 -->
<link href="//at.alicdn.com/t/font_763769_irjvs4mi65f.css" rel="stylesheet">
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
    <!--左侧导航开始-->
    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="nav-close"><i class="fa fa-times-circle"></i>
        </div>
        <div class="sidebar-collapse">
            <ul class="nav" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        <span><img alt="image" class="img-circle" src="__IMG__/profile_small.jpg"/></span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear">
                                <span class="block m-t-xs"><strong class="font-bold"><?php echo $username; ?></strong></span>
                                <span class="text-muted text-xs block"><?php echo $rolename; ?><b class="caret"></b></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li>
                                <a href="<?php echo url('admin/login/loginOut'); ?>">安全退出</a>
                            </li>
                        </ul>
                    </div>
                    <div class="logo-element">AD</div>
                </li>
                <?php if(authCheck2('1')): ?>
                <li class="menu">
                    <a href="<?php echo url('admin/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-users fa-fw"></i>
                        <span class="nav-label">管理员管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('user/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-drivers-license-o fa-fw"></i>
                        <span class="nav-label">用户/医生管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="http://app.uyihui.cn/admin/doctor_label_admin/" class="J_menuItem" >
                        <i class="iconfont icon-tags-fill"></i>
                        <span class="nav-label">医生标签管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="http://app.uyihui.cn/admin/doctor_destoon/" class="J_menuItem" >
                        <i class="fa fa-credit-card fa-fw"></i>
                        <span class="nav-label">提现管理</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="menu">
                    <a href="<?php echo url('post/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-comments-o fa-fw"></i>
                        <span class="nav-label">帖子/评论管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="http://app.uyihui.cn/admin/webim_msg_admin/" class="J_menuItem" >
                        <i class="iconfont icon-news"></i>
                        <span class="nav-label">WebIM消息管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('report/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-exclamation fa-fw"></i>
                        <span class="nav-label">举报管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('tipssort/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-newspaper-o fa-fw"></i>
                        <span class="nav-label">攻略分类</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('news/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-newspaper-o fa-fw"></i>
                        <span class="nav-label">资讯/案例/攻略/讲堂</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('event/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-video-camera fa-fw"></i>
                        <span class="nav-label">活动专区</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('feedback/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-pencil-square-o fa-fw"></i>
                        <span class="nav-label">反馈管理</span>
                    </a>
                </li>
                <?php if(authCheck2('1')): ?>
                <li class="menu">
                    <a href="<?php echo url('information/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-volume-control-phone fa-fw"></i>
                        <span class="nav-label">咨询管理</span>
                    </a>
                </li>
                <!--<li class="menu">
                    <a href="<?php echo url('information/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-hospital-o fa-fw"></i>
                        <span class="nav-label">转诊管理</span>
                    </a>
                </li> -->
                <?php endif; ?>
                <!--<li class="menu">
                    <a href="<?php echo url('tips/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-book fa-fw"></i>
                        <span class="nav-label">优孕攻略</span>
                    </a>
                </li> -->
                <li class="menu">
                    <a href="<?php echo url('appoint/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-stethoscope fa-fw"></i>
                        <span class="nav-label">预约/问诊报告管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('banner/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-photo fa-fw"></i>
                        <span class="nav-label">轮播图管理</span>
                    </a>
                </li>
                <?php if(authCheck2('1')): ?>
                <li class="menu">
                    <a href="<?php echo url('consultation/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-smile-o fa-fw"></i>
                        <span class="nav-label">评价管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('coupon/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-money fa-fw"></i>
                        <span class="nav-label">优惠券管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('Appannouncements/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-envelope fa-fw"></i>
                        <span class="nav-label">APP公告推送管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('apptags/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-search-plus fa-fw"></i>
                        <span class="nav-label">APP搜索关键词管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('statement/index'); ?>" class="J_menuItem" >
                        <i class="fa fa-info-circle fa-fw"></i>
                        <span class="nav-label">APP声明/帮助</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('statement/zhawen'); ?>" class="J_menuItem" >
                        <i class="iconfont icon-huodongguanli"></i>
                        <span class="nav-label">杂文管理</span>
                    </a>
                </li>
                <li class="menu">
                    <a href="<?php echo url('appdownloadlink/index'); ?>" class="J_menuItem" >
                        <i class="iconfont icon-xiazai"></i>
                        <span class="nav-label">APP下载链接设置</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <!--左侧导航结束-->
    <!--右侧部分开始-->
    <div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header"><a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                        class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li class="dropdown hidden-xs">
                        <a class="right-sidebar-toggle" aria-expanded="false">
                            <i class="fa fa-tasks"></i> 主题
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row content-tabs">
            <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
            </button>
            <nav class="page-tabs J_menuTabs">
                <div class="page-tabs-content">
                    <a href="javascript:;" class="active J_menuTab" data-id="index_v1.html">首页</a>
                </div>
            </nav>
            <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown J_tabClose" data-toggle="dropdown">常用操作<span class="caret"></span>

                </button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li class="J_tabGo"><a>前进</a>
                    </li>
                    <li class="J_tabBack"><a>后退</a>
                    </li>
                    <li class="J_tabFresh"><a>刷新</a>
                    </li>
                    <li class="divider"></li>
                    <li class="J_tabShowActive"><a>定位当前选项卡</a>
                    </li>
                    <li class="divider"></li>
                    <li class="J_tabCloseAll"><a>关闭全部选项卡</a>
                    </li>
                    <li class="J_tabCloseOther"><a>关闭其他选项卡</a>
                    </li>
                </ul>
            </div>
            <a href="<?php echo url('admin/login/loginOut'); ?>" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i>
                退出</a>
        </div>
        <div class="row J_mainContent" id="content-main">
            <iframe class="J_iframe" name="iframe0" width="100%" height="100%"
                    src="<?php echo url('Index/indexPage'); ?>" frameborder="0"
                    data-id="index_v1.html" seamless></iframe>
        </div>
        <div class="footer">
            <div class="pull-right">&copy; 2018-2019 <a>Unkonwn</a>
            </div>
        </div>
    </div>
    <!--右侧部分结束-->
    <!--右侧边栏开始-->
    <div id="right-sidebar">
        <div class="sidebar-container">
            <ul class="nav nav-tabs navs-3">
                <li class="active">
                    <a data-toggle="tab" href="#tab-1">
                        <i class="fa fa-gear"></i> 主题
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="sidebar-title">
                        <h3> <i class="fa fa-comments-o"></i> 主题设置</h3>
                        <small><i class="fa fa-tim"></i> 你可以从这里选择和预览主题的布局和样式，这些设置会被保存在本地，下次打开的时候会直接应用这些设置。</small>
                    </div>
                    <div class="skin-setttings">
                        <div class="title">主题设置</div>
                        <div class="setings-item">
                            <span>收起左侧菜单</span>
                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="collapsemenu">
                                    <label class="onoffswitch-label" for="collapsemenu">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                            <span>固定顶部</span>

                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="fixednavbar" class="onoffswitch-checkbox" id="fixednavbar">
                                    <label class="onoffswitch-label" for="fixednavbar">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="setings-item">
                                <span>
                        固定宽度
                    </span>

                            <div class="switch">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="boxedlayout" class="onoffswitch-checkbox" id="boxedlayout">
                                    <label class="onoffswitch-label" for="boxedlayout">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="title">皮肤选择</div>
                        <div class="setings-item default-skin nb">
                                <span class="skin-name ">
                         <a href="#" class="s-skin-0">
                             默认皮肤
                         </a>
                    </span>
                        </div>
                        <div class="setings-item blue-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-1">
                            蓝色主题
                        </a>
                    </span>
                        </div>
                        <div class="setings-item yellow-skin nb">
                                <span class="skin-name ">
                        <a href="#" class="s-skin-3">
                            黄色/紫色主题
                        </a>
                    </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--右侧边栏结束-->
    <!--mini聊天窗口开始-->
</div>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/bootstrap.min.js?v=3.3.6"></script>
<script src="__JS__/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="__JS__/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/hplus.min.js?v=4.1.0"></script>
<!--<script type="text/javascript" src="__JS__/contabs.min.js"></script>-->
<script type="text/javascript" src="__JS__/contabs.js"></script>
<script src="__JS__/plugins/pace/pace.min.js"></script>
</body>

</html>
