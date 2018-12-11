<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"/var/www/html/Unkonwn/public/../application/admin/view/feedback/index.html";i:1541563433;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
            <h5>反馈列表</h5>
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
                            <option value="customer">用户反馈</option>
                            <option value="doctor">医生反馈</option>
                        </select>
                    </div>
                </div>
                <!--搜索框开始-->
                <div class="content clearfix m-b pull-right">
                    <div class="form-group" style="margin-right:20px;">
                        <label><?php echo $Name; ?>：</label>
                        <input type="text" class="form-control" id="Name" name="searchName">
                    </div>
                    <div class="form-group">
                        <label>内容关键词：</label>
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
                          <th width="80" style="text-align:center;">ID</th>
                          <th width="150"><?php echo $Name; ?></th>
                          <th>反馈内容</th>
                          <th width="200">时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['id']; ?></td>
                              <td><?php echo $vo['nick_name']; ?></td>
                              <td><?php echo $vo['content']; ?></td>
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
            $.getJSON("<?php echo url('feedback/Del'); ?>", {'id' : id}, function(res){
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
	
	$(".filter-title").attr("placeholder","用户反馈");  
		
	<?php if($filter != ''): ?>
	$("select option[value='<?php echo $filter; ?>']").attr("selected", "selected");
	$(".filter-title").attr("placeholder","<?php echo $filter; ?>");  
	<?php endif; ?>
	
	$('.filter-box').selectFilter({
		callBack : function (val){
			//返回选择的值
			//console.log(val+'-是返回的值');
			location.href = "<?php echo url('feedback/index'); ?>"+"?filter="+val;
		}
	});
	
</script>
</html>
