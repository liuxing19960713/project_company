<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"/var/www/html/Unkonwn/public/../application/admin/view/appannouncements/index.html";i:1541837148;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <link href="__CSS__/CssReset.css" rel="stylesheet">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_763769_uimtwpj186.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_760616_amuthn0b2rf.css">
    <style>
        .Nav {
            margin: 0 auto;
            width: 1200px;
            padding: 10px 20px;
        }
        .title {
            text-align: center;
        }
        table {
            margin: 20px 0;
            width: 100%;
        }
        tr {
            text-align: center;
            font-size: 15px;
            height: 40px;
            line-height: 40px;
        }
        td {
            border: 1px solid rgb(88, 88, 88);
        }
    </style>
</head>
<body>
    <div class="Nav">
        <h2 class="title"><?php echo $title; ?></h2>
        <div>
            <span>推送类型：</span>
            <select name="sub_type" id="sub_type">
                <option value="1"<?php if($type == 1): ?>selected<?php endif; ?>>用户端推送</option>
                <option value="2"<?php if($type == 2): ?>selected<?php endif; ?>>医生端推送</option>
                <option value="0"<?php if($type == 0): ?>selected<?php endif; ?>>全部数据</option>
            </select>
            <span style="float:right; margin:0 20px;">&nbsp;&nbsp;&nbsp;&nbsp;<a href="add.html">新增公告<i class="iconfont icon-plus-circle-fill"></i></a></span>
        </div>
        <table>
            <tr style="font-weight:bolder; color:#fff; background:rgb(49, 49, 49);">
                <td>Id</td>
                <td>公告标题</td>
                <td>公告内容</td>
                <td>公告类型</td>
                <td>添加时间</td>
                <td>操作</td>
            </tr>
            <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $k = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($k % 2 );++$k;?>
            <tr>
                <td><?php echo $data['id']; ?></td>
                <td><?php echo $data['title']; ?></td>
                <td style="padding:0 5px; max-width:40px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <?php echo $data['content']; ?>
                </td>
                <td><?php echo $data['sub_type']; ?></td>
                <td><?php echo $data['time']; ?></td>
                <td>
                    <a href="http://unkonwn.uyihui.cn/admin/appannouncements/editor?id=<?php echo $data['id']; ?>"><i class="iconfont icon-edit-square"></i>编辑</a>
                    <a style="color: #FF3030;" onclick="DeleteData(<?php echo $data['id']; ?>);"><i class="iconfont icon-wrong-"></i>删除</a>
                </td>
            </tr>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </table>
    </div>
    <script src="__JS__/Fm.js"></script>
    <script src="__JS__/jquery.min.js?v=2.1.4"></script>
    <script>
        //select内容监听
        $("#sub_type").change(function(){
            var value = $('#sub_type option:selected').val();
            if(value=='0') {
                window.location.href = 'index.html?type=0';
            }
            if(value=='1') {
                window.location.href = 'index.html?type=1';
            }
            if(value=='2') {
                window.location.href = 'index.html?type=2';
            }
        });
        //数据删除
        function DeleteData(id) {
            $.ajax({
                type: 'post',
                url: 'http://unkonwn.uyihui.cn/admin/appannouncements/deletedata',
                data: { 'id':id },
                dataType: 'json',
                success: function(e) {
                    alert('删除成功');
                    location.reload();
                },
                error: function(e) {
                    alert('删除失败');
                }
            });
        }
    </script>
</body>
</html>