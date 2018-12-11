<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/success/index.html";i:1541572214;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
            <h5>案例列表</h5>
        </div>
        <div class="ibox-content">
        	<div style="width:10%; float:left;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly placeholder="成功案例" />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="最新资讯">最新资讯</option>
                        <option value="成功案例" selected>成功案例</option>
                        <option value="优孕攻略">优孕攻略</option>
                        <option value="医生讲堂">医生讲堂</option>
                    </select>
                </div>
            </div>
            <div class="form-group clearfix col-sm-1">
                <a href="<?php echo url('success/add'); ?>"><button class="btn btn-outline btn-primary" type="button">添加文章</button></a>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"   method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
                    <div class="form-group">
                        <label>文章标题：</label>
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
                          <th width="80" style="text-align:center;">文章ID</th>
                          <th>文章标题</th>
                          <th width="150">封面图片</th>
                          <th width="80" style="text-align:center;">浏览数</th>
                          <th width="200">添加时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['as_id']; ?></td>
                              <td><?php echo $vo['title']; ?></td>
                              <td><img src="<?php echo $vo['img_url']; ?>?imageView2/1/w/120/h/60"/></td>
                              <td align="center"><?php echo $vo['views_count']; ?></td>
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
            $.getJSON("<?php echo url('success/Del'); ?>", {'id' : id}, function(res){
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
			if(val=="最新资讯"){
				location.href = "<?php echo url('news/index'); ?>";
			}else if(val=="医生讲堂"){
				location.href = "<?php echo url('increase/index'); ?>";
			}else if(val=="优孕攻略"){
				location.href = "<?php echo url('tips/index'); ?>";
			}
		}
	});
	
</script>
</html>
