<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/var/www/html/Unkonwn/public/../application/admin/view/banner/index.html";i:1541563428;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
            <h5><?php echo $title; ?></h5>
        </div>
        <div class="ibox-content">
            <div style="width:10%; float:left; margin-bottom:20px;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="1">用户轮播图</option>
                        <option value="2">医生轮播图</option>
                        <option value="3">其他</option>
                    </select>
                </div>
            </div>
            <div class="form-group clearfix col-sm-1">
                <a href="add.html?filter=<?php echo $filter; ?>"><button class="btn btn-outline btn-primary" type="button">添加轮播图</button></a>
            </div>

            <div class="example-wrap">
                <div class="example">
                      <table class="table">
                          <thead>
                          <th width="80" style="text-align:center;">ID</th>
                          <th width="150">优先级</th>
                          <th width="150">封面图片</th>
                          <th width="100" style="text-align:center;">隐藏</th>
                          <th>时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['ids']; ?></td>
                              <td align="center"><?php echo $vo['order_num']; ?></td>
                              <td align="center"><img src="<?php echo $vo['img_url']; ?>?imageView2/1/w/120/h/60"/></td>
                              <td align="center">
                              	<select onChange="updateData(this.id)" style="width:60px;" class="js_top form-control" id="ishidden_<?php echo $vo['ids']; ?>" data-id="<?php echo $vo['ids']; ?>" name="is_hidden">
                                    <option value="yes" <?php if($vo['is_hidden'] == 'yes'): ?>selected<?php endif; ?>>是</option>
                                    <option value="no" <?php if($vo['is_hidden'] == 'no'): ?>selected<?php endif; ?>>否</option>
                                </select>
                              </td>
                              <td><?php echo date('Y-m-d H:i:s',$vo['create_time']); ?></td>
                              <td><?php echo $vo['operate']; ?></td>
                          </tr>
                          <?php endforeach; endif; else: echo "" ;endif; ?>

                      </table>
                      <span style="margin-top:10px; float:left;">总共<?php echo $count; ?>条记录</span>
                      <div style="clear:both"></div>
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
        layer.confirm('确认删除此轮播图?', {icon: 3, title:'提示'}, function(index){
            //do something
			var filter = '<?php echo $filter; ?>';
            $.getJSON("<?php echo url('banner/Del'); ?>", {'id' : id, 'filter' : filter}, function(res){
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
	
	
	function Edit(id){
        location.href = "edit/id/"+id+".html"+"?filter=<?php echo $filter; ?>";
    }
	
	
	function updateData(id){
		var post_id=id.split("_")[1];
		var value=$("#"+id).val();
		
		if(value=="no"){
			var message = "确认隐藏？";
		}else{
			var message = "取消隐藏？";
		}
		
        layer.confirm(message, {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('banner/isHidden'); ?>", {'id' : post_id,'value' : value}, function(res){
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

	$(".filter-title").attr("placeholder","用户轮播图");  
		
	<?php if($filter != ''): ?>
		$("select option[value='<?php echo $filter; ?>']").attr("selected", "selected");
		$(".filter-title").attr("placeholder","<?php echo $filter; ?>");  
	<?php endif; ?>
	

	$('.filter-box').selectFilter({
		callBack : function (val){
			//返回选择的值
			//console.log(val+'-是返回的值');
			location.href = "<?php echo url('banner/index'); ?>"+"?filter="+val;

		}
	});

	
</script>
</html>
