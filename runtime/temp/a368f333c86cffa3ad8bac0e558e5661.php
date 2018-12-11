<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:73:"/var/www/html/Unkonwn/public/../application/admin/view/apptags/index.html";i:1541563428;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title; ?></title>
<link href="__CSS__/CssReset.css" rel="stylesheet">
<link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
<link href="__CSS__/font-awesome.min.css?v=4.7.0" rel="stylesheet">
<link href="__CSS__/animate.min.css" rel="stylesheet">
<link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
<link href="__CSS__/apptags/apptags.css" rel="stylesheet">
</head>
<body>
    <div class="header clearfix">
        <select name="filter" id="filter">
            <option value="0" style="color: rgb(172, 172, 172)" disabled selected>点击筛选关键词</option>
            <option value="99">所有关键词</option>
            <option value="0">搜索结果关键词</option>
            <option value="1">预加载关键词</option>
            <option value="2">医生搜索结果</option>
            <option value="3">医生搜索预加载</option>
        </select>

        <!--搜索框开始-->
        <div id='commentForm' role="form" method="get" class="form-inline pull-right clearfix">
            <div class="content clearfix m-b">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchText" name="searchText" placeholder="输入需要搜索的关键词名称">
                </div>
                <div class="form-group">
                    <button class="btn-primary bt2" type="submit" id="search" onclick="Search()">
                        <strong><i class="fa fa-search fa-fw"></i>搜 索</strong>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- 标题 -->
    <div class="T-Box clearfix">
        <h3 class="Title"><?php echo $title; ?></h3>
        <button id="Add" class="btn-primary bt2" type="submit" id="search">
            <strong><i class="fa fa-plus-circle fa-fw"></i>新增关键词</strong>
        </button>
    </div>
    <!-- 数据展现 -->
    <table id="ApptagsTab" border="1">
        <tr>
            <td width="10%">ID</td>
            <td width="15%">关键词名称</td>
            <td width="15%">优先级（越大排名越靠前）</td>
            <td width="10%">是否隐藏</td>
            <td width="10%">类型</td>
            <td width="20%">最后修改时间</td>
            <td width="20%">编辑</td>
        </tr>
        <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): if( count($data)==0 ) : echo "" ;else: foreach($data as $key=>$data): ?>
        <tr>
            <!-- id -->
            <td align="center"><?php echo $data['id']; ?></td>
            <!-- tagname -->
            <td class="data"><input class=tagname<?php echo $data['id']; ?> type="text" value=<?php echo $data['tagname']; ?> placeholder="请输入关键词名字"></td>
            <!-- pro -->
            <td class="data"><input class=pro<?php echo $data['id']; ?> type="text" value=<?php echo $data['pro']; ?> placeholder="请输入关键词优先级(请输入数值)" onkeyup="value=value.replace(/[^\d]/g,'')"></td>
            <!-- hide -->
            <td class="data">
                <select name="DataHide" id="DataHide" class=hide<?php echo $data['id']; ?>>
                    <option value="1"  <?php if($data['hide'] == 1): ?>selected<?php endif; ?>>是</option>
                    <option value="0" <?php if($data['hide'] == 0): ?>selected<?php endif; ?>>否</option>
                </select>
            </td>
            <!-- type -->
            <td class="data">
                <select name="DataType" id="DataType" class=type<?php echo $data['id']; ?>>
                    <option value="0" <?php if($data['type'] == 0): ?>selected<?php endif; ?>>结果</option>
                    <option value="1" <?php if($data['type'] == 1): ?>selected<?php endif; ?>>预加载</option>
                    <option value="2" <?php if($data['type'] == 2): ?>selected<?php endif; ?>>医生搜索结果</option>
                    <option value="3" <?php if($data['type'] == 3): ?>selected<?php endif; ?>>医生搜索预加载</option>
                </select>
            </td>
            <!-- addtime -->
            <td><?php echo $data['addtime']; ?></td>
            <td align="center">
                <a onclick="DelBut(this)" class=<?php echo $data['id']; ?> style="margin: 0 5px; color: #d10000" href="#"><i class="fa fa-trash fa-fw"></i>删除</a>
                <a onclick="AlterData(this)" class=<?php echo $data['id']; ?> style="margin: 0 5px; color: #18a689" href="#"><i class="fa fa-edit fa-fw"></i>提交修改</a>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
    <!-- 浮动层 -->
    <div id="floating" style="display: none;">
        <!-- 添加关键词表单 -->
        <div class="FloatDom AddBox">
            <i class="fa fa-times delete"></i>
            <h3>新增搜索关键词</h3>
            <input id="AddName" type="text" placeholder="请输入关键词名字">
            <input id="AddPriority" type="text" placeholder="请输入关键词优先级(请输入数值)" onkeyup="value=value.replace(/[^\d]/g,'')">
            <select id="AddHide" name="AddHide" placeholder="请选择是否隐藏">
                <option value="1">隐藏</option>
                <option value="0" selected>不隐藏</option>
            </select>
            <select id="AddType" name="AddType" placeholder="请选择关键词类型">
                <option value="0" selected>结果关键词</option>
                <option value="1">预加载关键词</option>
                <option value="2" selected>医生搜索结果</option>
                <option value="3">医生搜索预加载</option>
            </select>
            <button class="btn-primary bt2 AddBt" type="submit">
                <strong><i class="fa fa-plus-circle fa-fw"></i>&nbsp;确&nbsp;认&nbsp;添&nbsp;加</strong>
            </button>
        </div>

        <!-- 提示框（删除操作） -->
        <div class="FloatDom DeleteBox">
            <h3>确定删除?</h3>
            <button class="btn-primary bt2 DeleteBt1" type="submit">
                <strong>&nbsp;确&nbsp;认</strong>
            </button>
            <button class="btn-primary bt2 DeleteBt2" type="submit">
                <strong>&nbsp;取&nbsp;消</strong>
            </button>
        </div>

        <!-- 修改关键词表单 -->

    </div>
</body>
<script src="__JS__/Fm.js"></script>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/selectFilter.js"></script>
<script>
    //获取当前页面域名(不带参数)
    var url = document.URL;
    var url = url.split('/');
    if(url[2]) {
        url = url[2];
    } else {
        url = ''; //如果url不正确就取空
    }
    //当初始选项发生改变时跳转
    $('#filter').change(function(){
        //获取选项的值
        var val = $("#filter option:selected").val();
        if(val == '99') {
            //alert(url);
            window.location.href = "/admin/Apptags/index";
        } else {
            window.location.href = "/admin/Apptags/ScreenData?Screenid="+val;
            //$.get('ScreenData',{'Screenid':val});
        }
    });

    //点击事件
    //浮动层
    //提示框(添加)
    PShow($('#Add'),$('#floating'),400,1);
    PShow($('#Add'),$('.AddBox'),400,1);
    PHide($('.delete'),$('#floating'),400,1);
    PHide($('.delete'),$('.AddBox'),400,1);

    //提示框(删除)
    function DelBut(dom) {
        var id = {'id':$(dom).attr("class")};
        $('#floating').fadeIn(400);
        $('.DeleteBox').fadeIn(400);
        PHide($('.DeleteBt2'),$('#floating'),400,1);
        PHide($('.DeleteBt2'),$('.DeleteBox'),400,1);
        $('.DeleteBt1').click(function () {
            PHide($('.DeleteBt2'),$('#floating'),400,1);
            PHide($('.DeleteBt2'),$('.DeleteBox'),400,1);
            DeleteData(id);
        });
    }

    //ajax事件
    //添加关键词数据
    $('.AddBt').click(function () {
        var AddName = $('#AddName').val(),
            AddPrty = $('#AddPriority').val(),
            AddHide = $('#AddHide option:selected').val(),
            AddType = $('#AddType option:selected').val(),
            data = {"AddName":AddName,"AddPrty":AddPrty,"AddHide":AddHide,"AddType":AddType};
        if(AddName == "" || AddPrty == "" || AddHide == "" || AddType == "") {
            alert("信息填写不完整");
        } else {
            $.ajax({
                url: "/admin/Apptags/adddata",
                type: 'post',
                data: data,
                async: false,
                success: function(data) {
                    alert(data);
                    window.location.href = "/admin/Apptags/index";
                }
            });
        }
    });
    
    //删除关键词数据
    function DeleteData(id) {
        $.ajax({
            url: "/admin/Apptags/DeleteData",
            type: 'post',
            data: id,
            async: false,
            success: function(data) {
                alert(data);
                window.location.href = "/admin/Apptags/index";
            }
        });
    }

    //提交关键词修改
    function AlterData(dom) {
        var id = $(dom).attr("class")
        var data = {
            'id':id,
            'tagname':$('.tagname' + id).val(),
            'pro':$('.pro' + id).val(),
            'hide':$('.hide' + id + ' option:selected').val(),
            'type':$('.type' + id + ' option:selected').val()
        };
        if(!$('.tagname' + id).val() && !$('.tagname' + id).val()) {
            alert('信息填写不完整！');
        } else {
            $.ajax({
                url: "/admin/Apptags/SearchData",
                type: 'post',
                data: data,
                async: false,
                success: function(data) {
                    alert(data);
                    window.location.href = "/admin/Apptags/index";
                }
            });
        }
    }
    //关键词模糊搜索
    function Search() {
        if(!$('#searchText').val()){
            alert('请输入要搜索的内容！');
        } else {
            window.location.href = "/admin/Apptags/Search" + "?" + "tagname=" + $('#searchText').val();
        }
    }
</script>



</html>