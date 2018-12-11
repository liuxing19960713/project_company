<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:70:"/var/www/html/Unkonwn/public/../application/admin/view/user/index.html";i:1541572123;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>文章列表</title>
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
            <h5>用户列表</h5>
        </div>
        <div class="ibox-content">
        	<div style="width:10%; float:left;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly placeholder="用户列表" />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="用户列表" selected>用户列表</option>
                        <option value="医生管理">医生管理</option>
                        <option value="医生邀请">医生邀请</option>
                    </select>
                </div>
            </div>
            <div class="form-group clearfix col-sm-1">
                <?php if(authCheck2('1')): ?>
                <a href="<?php echo url('user/useradd'); ?>"><button class="btn btn-outline btn-primary" type="button">添加用户</button></a>
                <?php endif; ?>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"   method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
					<div class="form-group" style="margin-right:20px;">
						<label style="padding-top:8px;">排序：</label>
						<select class="form-control" id="searchStatus" name="searchStatus">
							<option value="">默认</option>
							<option value="youb" <?php if($searchStatus == 'youb'): ?>selected<?php endif; ?>>优币</option>
						</select>
					</div>
					
                    <div class="form-group">
                        <label>昵称：</label>
                        <input type="text" class="form-control" id="title" name="searchText" value="<?php echo $search; ?>">
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
                          <th width="80" style="text-align:center;">ID</th>
                          <th>昵称</th>
                          <th width="80" style="text-align:center;">头像</th>
                          <th width="130">手机</th>
                          <th width="80" style="text-align:center;">下载来源</th>
                          <th width="80">优币</th>
                          <th width="170">签到时间</th>
                          <th width="170">注册时间</th>
                          <th>备注</th>
                          <th width="220">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['user_id']; ?></td>
                              <td><?php echo $vo['nick_name']; ?></td>
                              <td align="center">
                                  <?php if(!empty($vo['avatar'])): ?>
                                  <img src="<?php echo $vo['avatar']; ?>" width="50" />
                                  <?php else: ?>
                                  <img src="http://ogu99wuzj.bkt.clouddn.com/o_1bfof6snt951c32j8i1sjnm67g.png" width="50" />
                                  <?php endif; ?>
                              </td>
                              <td><?php echo $vo['mobile']; ?></td>
                              <td style="text-align:center;"><?php echo $vo['app_store']; ?></td>
                              <td align="center"><?php echo $vo['all_sign']; ?></td>
                              <td><?php if(!empty($vo['sign_time'])): ?><?php echo date('Y-m-d  H:i:s',$vo['sign_time']); endif; ?></td>
                              <td><?php echo date('Y-m-d  H:i:s',$vo['create_time']); ?></td>
                              <td><input class=remarks<?php echo $vo['user_id']; ?> type="text" value="<?php echo $vo['remarks']; ?>" placeholder="备注"></td>
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
</body>
<script src="__JS__/Fm.js"></script>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<script type="text/javascript">
    function userDel(id){
        layer.confirm('确认删除此用户?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('user/userDel'); ?>", {'id' : id}, function(res){
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
	
	$('.filter-box').selectFilter({
		callBack : function (val){;
			if(val=="医生管理"){
				location.href = "<?php echo url('doctor/index'); ?>";
			}else if(val=="医生邀请"){
				location.href = "<?php echo url('doctor/invite'); ?>";
			}
		}
	});
	
	
	//提交备注修改
    function AlterData(dom) {
        var id = dom
        var data = {
            'user_id':id,
            'remarks':$('.remarks' + id).val()
        };
        $.ajax({
			url: "/admin/user/Updata",
			type: 'post',
			data: data,
			async: false,
			success: function(data) {
				alert(data);
				//window.location.href = "/admin/news/index";
			}
		});
    }

</script>
</html>
