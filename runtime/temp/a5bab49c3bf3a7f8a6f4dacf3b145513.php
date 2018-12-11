<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:71:"/var/www/html/Unkonwn/public/../application/admin/view/admin/index.html";i:1541563426;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;s:71:"/var/www/html/Unkonwn/public/../application/admin/view/base/jslist.html";i:1541563429;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理员列表</title>
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
            <h5>管理员列表</h5>
        </div>
        <div class="ibox-content">
            <div class="form-group clearfix col-sm-1">
                <?php if(authCheck2('1')): ?>
                <a href="<?php echo url('admin/adminAdd'); ?>">
                    <button class="btn btn-outline btn-primary" type="button">添加管理员</button>
                </a>
                <?php endif; ?>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"  method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
                    <div class="form-group">
                        <label>管理员名称：</label>
                        <input type="text" class="form-control" id="username" name="searchText">
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
                          <th>管理员ID</th>
                          <th>管理员名称</th>
                          <th>管理员角色</th>
                          <th>真是姓名</th>
                          <th>上次登录ip</th>
                          <th>上次登录时间</th>
                          <th>操作</th>
                        </thead>
                        
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                        <tr>
                            <td><?php echo $vo['admin_id']; ?></td>
                            <td><?php echo $vo['account']; ?></td>
                            <td><?php echo $vo['admin_rank']; ?></td>
                            <td><?php echo $vo['nickname']; ?></td>
                            <td><?php echo $vo['last_login_ip']; ?></td>
                            <td><?php echo $vo['last_login_time']; ?></td>
                            <td><?php echo $vo['operate']; ?></td>
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
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js?v=2.1.4"></script>
<script type="text/javascript">
    function adminDel(id){
        layer.confirm('确认删除此管理员?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('admin/adminDel'); ?>", {'id' : id}, function(res){
                if(1 == res.code){
                    layer.alert(res.msg, {title: '友情提示', icon: 1, closeBtn: 0}, function(){
                        //initTable();
						window.location.reload();
                    });
                }else if(111 == res.code){
                    window.location.reload();
                }else{
                    layer.alert(res.msg, {title: '友情提示', icon: 2});
                }
            });

            layer.close(index);
        })

    }
</script>
</body>
</html>
