<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:74:"/var/www/html/Unkonwn/public/../application/admin/view/doctorwd/index.html";i:1541582986;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $title; ?></title>
    <style>
        .title { width: 100%; text-align: center; color: rgb(90, 90, 90); }
        .Nav { display: block; width: 1200px; margin: 0 auto; border-collapse:collapse;}
        .Nav tr { text-align: center; }
    </style>
</head>
<body>
    <h2 class='title'><?php echo $title; ?></h2>
    <table class='Nav' border="1">
        <tr>
            <td style='width:10%;'>id</td>
            <td style='width:10%;'>医生id</td>
            <td style='width:10%;'>银行卡号</td>
            <td style='width:10%;'>银行卡所属银行</td>
            <td style='width:10%;'>提现金额</td>
            <td style='width:10%;'>提现后账户余额</td>
            <td style='width:10%;'>状态</td>
            <td style='width:10%;'>提现发起时间</td>
            <td style='width:20%;'>操作</td>
        </tr>
        <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
        <tr>
            <td><?php echo $data['id']; ?></td>
            <td><?php echo $data['doctor_id']; ?></td>
            <td><?php echo $data['bank_card']; ?></td>
            <td><?php echo $data['back_name']; ?></td>
            <td><?php echo $data['money']; ?></td>
            <td><?php echo $data['money_2']; ?></td>
            <td><?php echo $data['state']; ?></td>
            <td><?php echo $data['time']; ?></td>
            <td>
                <button>同意</button>
                <button>拒绝</button>
            </td>
        </tr>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </table>
</body>
<script></script>
</html>