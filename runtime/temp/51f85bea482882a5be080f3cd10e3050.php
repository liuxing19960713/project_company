<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/appoint/index.html";i:1541563427;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
<link href="__CSS__/selectFilter.css?v=3.3.6" rel="stylesheet"></head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- Panel Other -->
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>预约列表</h5>
        </div>
        <div class="ibox-content">
        	<form id='commentForm' role="form"   method="get" action="" class="form-inline" style="width:100%;">
                <div style="width:10%; float:left;">
                    <div class="filter-box">
                        <div class="filter-text">
                            <input class="filter-title" type="text" readonly placeholder="预约列表" />
                            <i class="icon icon-filter-arrow"></i>
                        </div>
                        <select name="filter" id="filter">
                            <option value="appoint">预约列表</option>
                            <option value="conreport">问诊报告列表</option>
                        </select>
                    </div>
                </div>
                <!--搜索框开始-->
                <div class="content clearfix m-b pull-right">
                    <div class="form-group" style="margin-right:20px;">
                        <label style="padding-top:8px;">状态：</label>
                        <select class="form-control" id="searchStatus" name="searchStatus">
                            <option value="">全部</option>
                            <option value="yes">预约成功</option>
                            <option value="no">预约失败</option>
                            <option value="wait">预约中</option>
                            <option value="end">预约结束</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:20px;">
                        <label>医生名：</label>
                        <input type="text" class="form-control" id="Name" name="searchText">
                    </div>
                    <div class="form-group" style="margin-right:20px;">
                        <label>用户名：</label>
                        <input type="text" class="form-control" id="searchName" name="searchName">
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
                          <th width="150">医生名</th>
                          <th width="150">患者名/用户名</th>
                          <th width="100" style="text-align:center;">状态</th>
                          <th>时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['ap_id']; ?></td>
                              <td><?php echo $vo['doctorInfo']['nick_name']; ?></td>
                              <td><?php echo $vo['userInfo']['nick_name']; ?></td>
                              <td align="center"><?php echo $vo['status']; ?></td>
                              <td><?php echo date('Y-m-d H:i:s',$vo['create_time']); ?></td>
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
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<script type="text/javascript">
    function Del(id){
        layer.confirm('确认删除此文章?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('appoint/Del'); ?>", {'id' : id}, function(res){
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
		callBack : function (val){
			//返回选择的值
			//console.log(val+'-是返回的值');
			location.href = "<?php echo url('"+val+"/index'); ?>";

		}
	});

	
</script>
</html>
