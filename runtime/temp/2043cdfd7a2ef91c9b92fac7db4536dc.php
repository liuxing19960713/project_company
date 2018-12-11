<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"/var/www/html/Unkonwn/public/../application/admin/view/post/doctor.html";i:1541563436;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>帖子</title>
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
            <h5>帖子列表</h5>
        </div>
        <div class="ibox-content">
        	<div style="width:10%; float:left;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly placeholder="医生帖子" />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="用户帖子">用户帖子</option>
                        <option value="医生帖子" selected>医生帖子</option>
                        <option value="评论管理">评论管理</option>
                        <option value="评论回复">评论回复管理</option>
                    </select>
                </div>
            </div>
            <div class="form-group clearfix col-sm-1">
                <a href="<?php echo url('post/doctor_add'); ?>"><button class="btn btn-outline btn-primary" type="button">添加帖子</button></a>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"   method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
                    <div class="form-group">
                        <label>关键字：</label>
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
                          <th width="80" style="text-align:center;">帖子ID</th>
                          <th width="200" style="text-align:center;">医生名</th>
                          <th>文章标题</th>
                          <th width="80" style="text-align:center;">类型</th>
                          <th width="100" style="text-align:center;">圈子类型</th>
                          <th width="80" style="text-align:center;">浏览数</th>
                          <th width="100" style="text-align:center;">置顶帖</th>
                          <th width="100" style="text-align:center;">精华帖</th>
                          <th width="150">添加时间</th>
                          <th width="150">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['post_id']; ?></td>
                              <td align="center"><?php echo $vo['nick_name']; ?></td>
                              <td><?php echo $vo['title']; ?></td>
                              <td align="center"><?php echo $vo['post_type']; ?></td>
                              <td align="center"><?php echo $vo['group_type']; ?></td>
                              <td align="center"><?php echo $vo['views_count']; ?></td>
                              <td align="center">
                              	<select  onChange="updateData(this.id)" class="js_top form-control" id="istop_<?php echo $vo['post_id']; ?>" data-id="<?php echo $vo['post_id']; ?>" name="is_top">
                                    <option value="1" <?php if($vo['is_top'] == '1'): ?>selected<?php endif; ?>>是</option>
                                    <option value="0" <?php if($vo['is_top'] == '0'): ?>selected<?php endif; ?>>否</option>
                                </select>
                              </td>
                              <td align="center">
                              	<select  onChange="updateBest(this.id)" class="js_top form-control" id="isbest_<?php echo $vo['post_id']; ?>" data-id="<?php echo $vo['post_id']; ?>" name="is_best">
                                    <option value="0" <?php if($vo['is_best'] == '0'): ?>selected<?php endif; ?>>否</option>
                                    <option value="1" <?php if($vo['is_best'] == '1'): ?>selected<?php endif; ?>>是</option>
                                </select>
                              </td>
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
        layer.confirm('确认删除?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('post/postDel'); ?>", {'id' : id}, function(res){
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
	
    function updateData(id){
		var post_id=id.split("_")[1];
		var value=$("#"+id).val();
		
		if(value==1){
			var message = "确认置顶？";
		}else{
			var message = "取消置顶？";
		}
		
        layer.confirm(message, {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('post/is_top'); ?>", {'id' : post_id,'value' : value}, function(res){
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
	
	
	function updateBest(id){
		var post_id=id.split("_")[1];
		var value=$("#"+id).val();
		
		if(value==1){
			var message = "确认精华？";
		}else{
			var message = "取消精华？";
		}
		
        layer.confirm(message, {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('post/isBest'); ?>", {'id' : post_id,'value' : value}, function(res){
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
			if(val=="用户帖子"){
				location.href = "<?php echo url('post/index'); ?>";
			}else if(val=="评论管理"){
				location.href = "<?php echo url('comment/index'); ?>";
			}else if(val=="评论回复"){
				location.href = "<?php echo url('reply/index'); ?>";
			}
		}
	});
	
</script>
</html>
