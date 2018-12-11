<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/var/www/html/Unkonwn/public/../application/admin/view/coupon/index.html";i:1541563431;s:68:"/var/www/html/Unkonwn/public/../application/admin/view/base/css.html";i:1541563429;}*/ ?>
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
            <h5>优惠券列表</h5>
        </div>
        <div class="ibox-content">
            <div class="form-group clearfix col-sm-1">
                <a href="<?php echo url('coupon/add'); ?>"><button class="btn btn-outline btn-primary" type="button">添加优惠券</button></a>
            </div>
            <!--搜索框开始-->
            <form id='commentForm' role="form"   method="get" action="" class="form-inline pull-right">
                <div class="content clearfix m-b">
                    <div class="form-group">
                        <label>类型名称：</label>
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
                          <th>类型名称</th>
                          <th width="100" style="text-align:center;">金额</th>
                          <th width="100" style="text-align:center;">所有医生可用</th>
                          <th width="100" style="text-align:center;">有效时间</th>
                          <th width="200">添加时间</th>
                          <th width="200">操作</th>
                          </thead>
                          
                          <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): if( count($list)==0 ) : echo "" ;else: foreach($list as $key=>$vo): ?>
                          <tr>
                              <td align="center"><?php echo $vo['co_id']; ?></td>
                              <td><?php echo $vo['type']; ?></td>
                              <td align="center"><?php echo $vo['count']; ?>.00</td>
                              <td align="center">
                              	<select onChange="updateData(this.id)"  class="js_top form-control" id="isall_<?php echo $vo['co_id']; ?>" data-id="<?php echo $vo['co_id']; ?>" name="is_for_all" style="width:60px;">
                                    <option  value="yes" <?php if($vo['is_for_all'] == 'yes'): ?>selected<?php endif; ?>>是</option>
                                    <option  value="no" <?php if($vo['is_for_all'] == 'no'): ?>selected<?php endif; ?>>否</option>
                                </select>
                              </td>
                              <td align="center"><?php echo $vo['valid_time']/(24*60*60); ?>天</td>
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
            $.getJSON("<?php echo url('coupon/Del'); ?>", {'id' : id}, function(res){
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
</html>
