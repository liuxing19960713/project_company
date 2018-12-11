<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:85:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\tips\index.html";i:1541572238;s:83:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\base\css.html";i:1541563428;}*/ ?>
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
<script src="__JS__/selectFilter.js"></script>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- Panel Other -->
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>文章列表</h5>
        </div>
        <div class="ibox-content">
        	<div style="width:10%; float:left;">
                <div class="filter-box">
                    <div class="filter-text">
                        <input class="filter-title" type="text" readonly placeholder="优孕攻略" />
                        <i class="icon icon-filter-arrow"></i>
                    </div>
                    <select name="filter" id="filter">
                        <option value="最新资讯">最新资讯</option>
                        <option value="成功案例">成功案例</option>
                        <option value="优孕攻略" selected>优孕攻略</option>
                        <option value="医生讲堂">医生讲堂</option>
                    </select>
                </div>
            </div>
            <div class="form-group clearfix col-sm-1">
                <a href="<?php echo url('tips/add'); ?>"><button class="btn btn-outline btn-primary" type="button">添加文章</button></a>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"   method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
					<div class="form-group" style="margin-right:20px;">
						<label style="padding-top:8px;">置顶：</label>
						<select class="form-control" id="searchtop" name="searchtop">
							<option value="">否</option>
							<option value="yes" <?php if($searchtop == 'yes'): ?>selected<?php endif; ?>>是</option>
						</select>
					</div>
					<div class="form-group" style="margin-right:20px;">
						<label style="padding-top:8px;">热门：</label>
						<select class="form-control" id="searchhot" name="searchhot">
							<option value="">否</option>
							<option value="yes" <?php if($searchhot == 'yes'): ?>selected<?php endif; ?>>是</option>
						</select>
					</div>
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
                          <th style="text-align:center;" width="100">文章分类</th>
                          <th width="150">封面图片</th>
                          <th width="100" style="text-align:center;">置顶</th>
                          <th width="100" style="text-align:center;">热门</th>
                          <th width="200">添加时间</th>
                          <th width="230">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['tip_id']; ?></td>
                              <td><?php echo $vo['title']; ?></td>
                              <td align="center"><?php echo $vo['cate']; ?></td>
                              <td><img src="<?php echo $vo['img_url']; ?>?imageView2/1/w/120/h/60"/></td>
                              <td align="center">
                              	<select  onChange="updateData(this.id)" class="js_top form-control" id="istop_<?php echo $vo['tip_id']; ?>" data-id="<?php echo $vo['tip_id']; ?>" name="is_top">
                                    <option value="1" <?php if($vo['is_top'] == '1'): ?>selected<?php endif; ?>>是</option>
                                    <option value="0" <?php if($vo['is_top'] == '0'): ?>selected<?php endif; ?>>否</option>
                                </select>
                              </td>
                              <td align="center">
                              	<select  onChange="updateHot(this.id)" class="js_hot form-control" id="ishot_<?php echo $vo['tip_id']; ?>" data-id="<?php echo $vo['tip_id']; ?>" name="is_hot">
                                    <option value="1" <?php if($vo['is_hot'] == '1'): ?>selected<?php endif; ?>>是</option>
                                    <option value="0" <?php if($vo['is_hot'] == '0'): ?>selected<?php endif; ?>>否</option>
                                </select>
                              </td>
                              <td><input class=time<?php echo $vo['tip_id']; ?> type="text" value=<?php echo $vo['create_time']; ?> placeholder="请输入日期 年-月-日"></td>
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
    function Del(id){
        layer.confirm('确认删除此文章?', {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('tips/Del'); ?>", {'id' : id}, function(res){
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
            $.getJSON("<?php echo url('tips/is_top'); ?>", {'id' : post_id,'value' : value}, function(res){
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
	
	
	function updateHot(id){
		var post_id=id.split("_")[1];
		var value=$("#"+id).val();
		
		if(value==1){
			var message = "确认热门？";
		}else{
			var message = "取消热门？";
		}
		
        layer.confirm(message, {icon: 3, title:'提示'}, function(index){
            //do something
            $.getJSON("<?php echo url('tips/is_hot'); ?>", {'id' : post_id,'value' : value}, function(res){
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
			}else if(val=="成功案例"){
				location.href = "<?php echo url('success/index'); ?>";
			}
		}
	});
	
	
	
	//提交关键词修改
    function AlterData(dom) {
        var id = dom
        var data = {
            'tip_id':id,
            'create_time':$('.time' + id).val()
        };
        if(!$('.time' + id).val()) {
            alert('请填写时间');
        } else {
            $.ajax({
                url: "/admin/tips/Updata",
                type: 'post',
                data: data,
                async: false,
                success: function(data) {
                    alert(data);
                    //window.location.href = "/admin/news/index";
                }
            });
        }
	}
</script>
</html>
