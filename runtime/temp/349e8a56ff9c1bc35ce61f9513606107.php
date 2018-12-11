<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/var/www/html/Unkonwn/public/../application/admin/view/report/index.html";i:1541563437;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
            <h5><?php echo $titles; ?>举报列表</h5>
        </div>
        <div class="ibox-content">
            <div style="width:10%; float:left;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly  />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="帖子举报">帖子举报</option>
                        <option value="评论举报">评论举报</option>
                    </select>
                </div>
            </div>
            <!--搜索框开始-->
            <div class="content clearfix m-b pull-right">
                <div class="form-group">
                    <label style="padding-top:8px;">状态：</label>
                    <select class="form-control" id="searchText" name="searchText" style="float:right; width:100px;">
                        <option value="">全部</option>
                        <option value="已处理">已处理</option>
                        <option value="待处理">待处理</option>
                        <option value="通过">通过</option>
                    </select>
                </div>
            </div>
            <!--搜索框结束-->

            <div class="example-wrap">
                <div class="example">
                      <table class="table">
                          <thead>
                          <th width="80" style="text-align:center;">ID</th>
                          <th width="150" style="text-align:center;">举报人</th>
                          <th>被举报的<?php echo $titles; ?></th>
                          <th width="100" style="text-align:center;">原因</th>
                          <th width="100" style="text-align:center;">状态</th>
                          <th width="200">举报时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['report_id']; ?></td>
                              <td align="center"><?php echo $vo['userInfo']['nick_name']; ?></td>
                              <td>
                              <?php if($filter == '' or $filter=='帖子举报'): ?>
                              <?php echo $vo['postInfo']['title']; else: ?>
                              <?php echo $vo['commentInfo']['content']; endif; ?>
                              </td>
                              <td align="center"><?php echo $vo['reason']; ?></td>
                              <td align="center"><?php echo $vo['report_status']; ?></td>
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
        layer.confirm('确认删除此回复?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('reply/Del'); ?>", {'id' : id}, function(res){
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
	
	$(".filter-title").attr("placeholder","帖子举报");  
		
	<?php if($filter != ''): ?>
		$("select option[value='<?php echo $filter; ?>']").attr("selected", "selected");
		$(".filter-title").attr("placeholder","<?php echo $filter; ?>");  
	<?php endif; if($search != ''): ?>
		$("select option[value='<?php echo $search; ?>']").attr("selected", "search");
	<?php endif; ?>
	
	$('.filter-box').selectFilter({
		callBack : function (val){
			location.href = "<?php echo url('report/index'); ?>"+"?filter="+val;
		}
	});
	
	$('#searchText').change(function(){
			var val = $(this).val();
			location.href = "<?php echo url('report/index'); ?>"+"?filter=<?php echo $filter; ?>&searchText="+val;
	});
</script>
</html>
