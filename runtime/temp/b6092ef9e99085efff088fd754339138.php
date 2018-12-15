<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:88:"D:\kaifa\php\PHPTutorial\WWW\Unkonwn\public/../application/admin\view\Article\index.html";i:1544865979;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS__/doctor/doctor.css" rel="stylesheet">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
    <link href="__COMPONENT__/Fm/Fm.css" rel="stylesheet">
</head>
<body>
    <!-- <h3 class="title"><?php echo $title; ?></h3> -->
    <!-- 筛选 -->


    <form action="index" method="get" >
        <label>切换栏目</label>
        <select name="id" >
            <?php if(is_array($cate) || $cate instanceof \think\Collection || $cate instanceof \think\Paginator): if( count($cate)==0 ) : echo "" ;else: foreach($cate as $key=>$v): ?>
            <option value="<?php echo $v['id']; ?>"><?php echo $v['title']; ?></option>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
        <input type="submit" value="确定">
        <!-- <button id="DoctorAddBt" style="position:relative;left: 450px">确定切换栏目</button> -->
    </form>
    <!-- add -->
    <button id="DoctorAddBt" onclick="AddData()">添加文章</button>
    <!-- 搜索 -->
    <form id="SearchForm" action="index" method="get">
        <!-- <input type="text" name="phone" placeholder="输入要搜索的手机号" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" maxlength="11"/> -->
        <input type="text" name="title" placeholder="输入关键字"/>
        <!-- <input type="text" name="tt" placeholder="输入要搜索的主题"/> -->
        <input type="submit" onclick="" value="搜&nbsp;索">
    </form>
    <table id="DoctorTab" border="1" class="DataTables_Table_1">
        <tr>
            <th class="allchoose">全选</th>
            <th width="5%">ID</th>
            <th>相关图片</th>
            <th>主题</th>
            <th>所属栏目</th>
            <th>浏览器标题</th>
            <th>关键词</th>
            <th>是否显示</th>
            <th>是否推荐</th>
            <th>是否置顶</th>
            <th>添加时间</th>
            <th>操作</th>
        </tr> 
        <!-- 开始循环 -->
        <?php if(is_array($info) || $info instanceof \think\Collection || $info instanceof \think\Paginator): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>

        <tr>
            <td class="fruit"><input type="checkbox" value="<?php echo $vo['id']; ?>"></td> 
            <td><?php echo $vo['id']; ?></td>
            <td>
                <img class="DoctorTx ImgMag" src="<?php echo $vo['pic']; ?>" alt="医生头像" onclick="imgmag(this)">
            </td>
            <td><?php echo $vo['title']; ?></td>
            <td><?php echo $vo['ntitle']; ?></td>
            <td width="180"><?php echo $vo['ptitle']; ?></td>
            <td><?php echo $vo['keywords']; ?></td>
            <td class="shown"><?php if($vo['shown'] == '1'): ?>显示<?php else: ?>隐藏<?php endif; ?></td>
            <td><?php if($vo['iscom'] == '1'): ?>推荐<?php else: ?>默认<?php endif; ?></td>
            <td class="isTop"><?php if($vo['istop'] == '1'): ?><a href="javascript:viod(0)">置顶</a><?php else: ?><a href="javascript:viod(0)">默认</a><?php endif; ?> </td>
            <td><?php echo $vo['addtime']; ?></td>
            <td>
                <button class=<?php echo $vo['id']; ?> onclick="Alter(this)" style="color:#18a689"><i class="fa fa-paste"></i>编辑</button>
                &nbsp;&nbsp;&nbsp;
                <button class=<?php echo $vo['id']; ?> onclick="DeleteData(this)" style="color:#ff2251"><i class="fa fa-trash-o"></i>删除</button>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <tr>
            <td colspan="11">
                <!-- <a href="javascript:void(0)" class="btn btn-warning del">删除</a> -->
                <input type="checkbox" class="allchoose">全选<a href="javascript:void(0)" class="btn btn-warning nochoose">批量删除</a>

            </td>
       </tr>
       
    </table>
    <!-- 总条数 -->
    <span style="margin:5px 0 0 1%; float:left;">总共<?php echo $count; ?>条记录</span>
    <!-- 分页按钮 -->
    <?php echo $info->render(); ?>
</body>
<script src="__JS__/jquery.min.js"></script>
<!-- 地区选择插件distpicker.js(注意：引入顺序不能错！)-->
<script src="__JS__/distpicker/distpicker.data.js"></script>
<script src="__JS__/distpicker/distpicker.js"></script>
<script src="__JS__/distpicker/main.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<script>
    //全选
     $(".allchoose").click(function(){
        //遍历table 下的所有tr
        $(".DataTables_Table_1").find("tr").each(function(){
          //查找复选框 设置属性
          $(this).find(":checkbox").attr("checked",true);
        });
     });

     // 批量删除
     $(".nochoose").click(function(){
        // alert('ss');
        // /批量删除 
        Popups.Confirm(500,"是否批量删除","确定","取消",datadel);

            function datadel() 
            { 
                id=''; 
                var check = $('.fruit>input:checked'); 
                $('.fruit>input:checked').each(function(){ 
                    id=id+($(this).parent().next().html())+','; 
                }) 
                id=id.slice(0,-1); 
                $.ajax({ 
                    type: 'GET', 
                    url: 'batchdel', 
                    data: {id:id}, 
                    dataType: 'json', 
                    success: function(data){ 
                        alert("删除成功");
                        location.reload();
               
                    }, 
                    error:function(data) { 
                        console.log(data.msg); 
                    }, 
                });  
            } 
   
      });


     /*改变显示的值*/
     $(".shown").click(function(){
        // alert('11');
       var id =  $(this).parents("tr").find("td:first").next().html();
       var tr = $(this).parents(tr);
       // console.log(id);
       $.ajax({ 
            type: 'GET', 
            url: 'changShown', 
            data: {id:id}, 
            // dataType: 'json', 
            success: function(data){ 
                console.log(data);
                // console.log(data);
                alert('操作成功');
            }, 
            error:function(data) { 
            console.log(data.msg); 
            }, 
        });  

     })
 

     //全不选
     // $(".nochoose").click(function(){
     //  $(".DataTables_Table_1").find("tr").each(function(){
     //    $(this).find(":checkbox").attr("checked",false);
     //  });
     // });

     //反选
     $(".fchoose").click(function(){
      $(".DataTables_Table_1").find("tr").each(function(){
        //判断
        if($(this).find(":checkbox").attr("checked")){
          //取消选中
          $(this).find(":checkbox").attr("checked",false);
        }else{
          $(this).find(":checkbox").attr("checked",true);

        }
      });
     });

 

   // Ajax删除
     $(".del").click(function(){
       var arr = new Array();
      //遍历
      $(":checkbox").each(function(){
        Popups.Confirm(500,"是否批量删除","确定","取消",callfun1);

        if($(this).find(":checkbox").attr("checked",true)){
          //获取选中数据的id
          id=$(this).val();
          arr.push(id);
          // alert(id);
        }
      //   
        
      });
  
        function callfun1() {
              $.get("/admin/article/batchdel",{arr:arr},function(data){
                // alert(data);
                // alert(data);
                if(data==1){
                  alert("删除成功");
                  //遍历arr数组
                  for(var i=0;i<arr.length;i++){
                    //获取选中数据input
                    $("input[value='"+arr[i]+"']").parents("tr").remove();
                     location.reload();
                  } 
                }else{
                  // alert(data);
                }
              });
        }
     });

     //置顶功能
     $(".isTop").click(function(){
        // alert('11');
        var id = $(this).parents('tr').find("td:first").next().html();
        var tr = $(this).parents('tr');

        Popups.Confirm(500,"确定把这篇文章置顶/下架的操作","确定","取消",top);
        function top() {
            $.ajax({
                url: "isTop",
                type: 'get',
                data: {'id': id},
                success: function(data) {

                    // console.log(data);
                  if (data == "置顶") {

                        tr.css("background-color","pink");

                        location.reload();

                    } else if (data == "下架") {
                        location.reload();

                        tr.css("background-color","white");

                    }
                }
            });
             
        }
     })



    //Fm插件
    Popups = new Popups;
    //图片放大效果
    function imgmag(dom) {
        Popups.ImgMag(dom,500);
    }

    //当初始选项发生改变时跳转
    $('#filter').change(function(){
        //获取选项的值
        var id = $("#filter option:selected").val();
        // alert(id);
        Popups.Confirm(500,"是否切换栏目？","确定","取消",callfun1);
        // 回调函数
        function callfun1() {
            $.ajax({
                url: "index",
                type: 'get',
                data: {'id': id},
                success: function(data) {
                     
                }
            });
        }

    });
    //添加用户
    function AddData(){
        window.location.href = "create";
    }
    //删除按钮点击事件
    function DeleteData(dom) {
        // 获取点击元素的id
        var id = $(dom).attr("class");
        Popups.Confirm(500,"请确认是否删除？","确定","取消",callfun1,callfun2);
        // 回调函数
        function callfun1() {
            $.ajax({
                url: "delete",
                type: 'post',
                data: {'id': id},
                success: function(data) {
                    alert(data);
                    location.reload();
                }
            });
        }
        function callfun2() {
            // 刷新页面
            location.reload();
        }
    }
    //跳转编辑信息编辑页面
    function Alter(dom) {
        var id = $(dom).attr("class");
        window.location.href = "/admin/article/edit?id=" + id;
    }




</script>
</html>