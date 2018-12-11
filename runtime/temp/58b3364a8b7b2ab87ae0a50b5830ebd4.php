<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:79:"/var/www/html/Unkonwn/public/../application/admin/view/doctor/doctor_alter.html";i:1543558321;}*/ ?>
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
<body style="background-color: #f3f3f4">
    <div class="DoctorData clearfix">
        <h3 class="h3"><?php echo $title; ?></h3>
        <!-- 头像模块 -->
        <div class="Portrait Box clearfix">
            <h4 class="h4">头像</h4>
            <img id="PortraitImg" class="Portrait_img" src="<?php echo $DoctorData['avatar']; ?>" alt="医生头像" onclick="imgmag(this)" >
            
            <form id="UpImg-Portrait_img">
                <input id="UpToken" name="token" type="hidden" value="<?php echo $QnToken; ?>">
                <div class="bt1" onclick="$('#file').click();">
                    <i class="fa fa-cloud-upload"></i>
                    &nbsp;上传头像
                </div>
                <input id="file" name="file" type="file" onchange="UpImgChangeToop(this)"/>
            </form>
            <h4 class="h4">账号状态</h4>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">认证状态(审核修改):</h5>
                <select name="audit_status" id="audit_status" style="width: 90%">
                    <option value="yes"
                        <?php if($DoctorData['audit_status'] == 'yes'): ?>
                            selected
                        <?php endif; ?>
                    >已认证</option>
                    <option value="no"
                        <?php if($DoctorData['audit_status'] == 'no'): ?>
                            selected
                        <?php endif; ?>
                    >未通过认证</option>
                    <option value="yes"
                        <?php if($DoctorData['audit_status'] == 'wait'): ?>
                            selected
                        <?php endif; ?>
                    >未审核认证</option>
                    <option value="no"
                        <?php if($DoctorData['audit_status'] == 'emp'): ?>
                            selected
                        <?php endif; ?>
                    >未提交资料</option>
                    <option value="test"
                        <?php if($DoctorData['audit_status'] == 'test'): ?>
                            selected
                        <?php endif; ?>
                    >内部账号</option>
                    <option value="rep"
                        <?php if($DoctorData['audit_status'] == 'rep'): ?>
                            selected
                        <?php endif; ?>
                    >生殖中心</option>
                </select>
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">账户余额:&nbsp;<?php echo $DoctorData['money']; ?></h5>
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">粉丝数量:&nbsp;<?php echo $DoctorData['follower_count']; ?></h5>
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">服务次数:&nbsp;<?php echo $DoctorData['service_times']; ?></h5>
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">收到心意次数:&nbsp;<?php echo $DoctorData['gift_times']; ?></h5>
            </div>
            <h4 class="h4">认证信息:</h4>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">上传医生执业证书:</h5>
                <img class="ImgMag" onclick="imgmag(this)" width="100%" src="<?php echo $DoctorData['qualification_front']; ?>" alt="医生资格证书正面">
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">上传职称证书:</h5>
                <img class="ImgMag" onclick="imgmag(this)" width="100%" src="<?php echo $DoctorData['qualification_back']; ?>" alt="医生资格证书反面">
            </div>
        </div>
        <!-- 基本信息模块 -->
        <div class="Inf Box clearfix">
            <h4 class="h4">基本信息</h4>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">医生姓名：</h5>
                <input type="text" style="width: 90%" name="nick_name" value="<?php echo $DoctorData['nick_name']; ?>" placeholder="医生姓名">
            </div>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">手机号码：</h5>
                <input type="text" style="width: 90%" name="mobile" value="<?php echo $DoctorData['mobile']; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="11" placeholder="手机号码">
            </div>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">邮箱：</h5>
                <input type="text" style="width: 90%" name="email" value="<?php echo $DoctorData['email']; ?>" placeholder="邮箱">
            </div>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">身份证号码：</h5>
                <input type="text" style="width: 90%" name="id_card" value="<?php echo $DoctorData['id_card']; ?>" placeholder="身份证号码">
            </div>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">性别：</h5>
                <select name="gender" id="Gender" style="width: 90%">
                    <option value="male"
                        <?php if($DoctorData['gender'] == 'male'): ?>
                            selected
                        <?php endif; ?>
                    >男</option>
                    <option value="female"
                        <?php if($DoctorData['gender'] == 'female'): ?>
                            selected
                        <?php endif; ?>
                    >女</option>
                </select>
            </div>
            <div class="Box2" style="width: 25%">
                <h5 class="h5">医生职称：</h5>
                <select name="Doctor_jod" id="Doctor_jod" style="width: 90%">
                    <?php if(is_array($DoctorJobData) || $DoctorJobData instanceof \think\Collection || $DoctorJobData instanceof \think\Paginator): $i = 0; $__LIST__ = $DoctorJobData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data1): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $data1['title']; ?>" 
                            <?php if($data1['title'] == $DoctorData['title']): ?>
                                selected
                            <?php endif; ?>
                        ><?php echo $data1['title']; ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </div>
            <div class="Box2" data-toggle="distpicker" style="width: 50%">
                <h5 class="h5">医生所在地区：</h5>
                <select name="province" data-province="<?php echo $DoctorData['province']; ?>" style="width: 47%"></select>
                <select name="city" data-city="<?php echo $DoctorData['city']; ?>" style="width: 47%"></select>
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">医生标签</h5>
                <!-- 医生标签1 -->
                <select name="Label1" id="Label1" style="width: 30%">
                    <?php if(is_array($DoctorLabelData) || $DoctorLabelData instanceof \think\Collection || $DoctorLabelData instanceof \think\Paginator): $i = 0; $__LIST__ = $DoctorLabelData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data2): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $i; ?>"
                            <?php if($i == $DoctorLabel[0]): ?>
                                selected
                            <?php endif; ?>
                        ><?php echo $data2['name']; ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <option value="" 
                        <?php if($DoctorLabel[0] == ''): ?>
                            selected
                        <?php endif; ?>
                    >{标签为空}</option>
                </select>
                <!-- 医生标签2 -->
                <select name="Label2" id="Label2" style="width: 30%">
                    <?php if(is_array($DoctorLabelData) || $DoctorLabelData instanceof \think\Collection || $DoctorLabelData instanceof \think\Paginator): $i = 0; $__LIST__ = $DoctorLabelData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data2): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $i; ?>"
                            <?php if($i == $DoctorLabel[1]): ?>
                                selected
                            <?php endif; ?>
                        ><?php echo $data2['name']; ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <option value="" 
                        <?php if($DoctorLabel[1] == ''): ?>
                            selected
                        <?php endif; ?>
                    >{标签为空}</option>
                </select>
                <!-- 医生标签3 -->
                <select name="Label3" id="Label3" style="width: 30%">
                    <?php if(is_array($DoctorLabelData) || $DoctorLabelData instanceof \think\Collection || $DoctorLabelData instanceof \think\Paginator): $i = 0; $__LIST__ = $DoctorLabelData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data2): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $i; ?>"
                            <?php if($i == $DoctorLabel[2]): ?>
                                selected
                            <?php endif; ?>
                        ><?php echo $data2['name']; ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <option value="" 
                        <?php if($DoctorLabel[2] == ''): ?>
                            selected
                        <?php endif; ?>
                    >{标签为空}</option>
                </select>
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">登录密码修改</h5>
                <input type="text" style="width: 90%" name="password" value="" placeholder="登录密码，不需要修改时请不要随意填写内容，以免覆盖旧密码！">
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">擅长领域：</h5>
                <textarea name="intro1" style="width: 90%;" placeholder="擅长及诊所介绍"><?php echo $DoctorData['intro1']; ?></textarea>
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">医学背景：</h5>
                <textarea name="intro2" style="width: 90%;" placeholder="医学背景介绍"><?php echo $DoctorData['intro2']; ?></textarea>
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">学术成就：</h5>
                <textarea name="intro3" style="width: 90%;" placeholder="学术研究成果，获奖介绍"><?php echo $DoctorData['intro3']; ?></textarea>
            </div>
            <div class="Box2" style="width: 50%">
                <h5 class="h5">医生寄语：</h5>
                <textarea name="intro4" style="width: 90%;" placeholder="医生寄语"><?php echo $DoctorData['intro4']; ?></textarea>
            </div>
        </div>
        <!-- 医生科室信息 -->
        <div class="Office Box clearfix">
            <h4 class="h4">医生科室信息</h4>
            <div class="Box2" style="width: 33.3%">
                <h5 class="h5">医院：</h5>
                <input type="text" style="width: 90%" name="hospital" value="<?php echo $DoctorData['hospital']; ?>" placeholder="医院">
            </div>
            <div class="Box2" style="width: 33.3%">
                <h5 class="h5">科室：</h5>
                <select name="department_parent" id="department_parent" style="width: 90%">
                    <?php if(is_array($DoctorDepartmentData) || $DoctorDepartmentData instanceof \think\Collection || $DoctorDepartmentData instanceof \think\Paginator): $i = 0; $__LIST__ = $DoctorDepartmentData;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data2): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo $data2['title']; ?>" 
                            <?php if($data2['title'] == $DoctorData['department_parent']): ?>
                                selected
                            <?php endif; ?>
                        ><?php echo $data2['title']; ?></option>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
            </div>
            <div class="Box2" style="width: 33.3%">
                <h5 class="h5">科室电话：</h5>
                <input type="text" style="width: 90%" name="department_phone" value="<?php echo $DoctorData['department_phone']; ?>" placeholder="科室电话" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
        </div>
        <!-- 咨询金额设置 -->
        <div class="ConFee Box clearfix">
            <h4 class="h4">咨询金额设置</h4>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">图文咨询价格：</h5>
                <!-- 是否开启 -->
                <select name="is_open_image" id="is_open_image" style="width: 24%">
                        <option value="yes" 
                            <?php if($DoctorData['is_open_image'] == 'yes'): ?>
                                selected
                            <?php endif; ?>
                        >开启</option>
                        <option value="no" 
                            <?php if($DoctorData['is_open_image'] == 'no'): ?>
                                selected
                            <?php endif; ?>
                        >关闭</option>
                </select>
                <!-- 价格 -->
                <input type="text" style="width: 24%" name="text_consulting_1" value="<?php echo $ConFee_data['0']['text_consulting_1']; ?>" placeholder="单次价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">电话咨询价格：</h5>
                <select name="is_open_phone" id="is_open_phone" style="width: 24%">
                    <option value="yes" 
                        <?php if($DoctorData['is_open_phone'] == 'yes'): ?>
                            selected
                        <?php endif; ?>
                    >开启</option>
                    <option value="no" 
                        <?php if($DoctorData['is_open_phone'] == 'no'): ?>
                            selected
                        <?php endif; ?>
                    >关闭</option>
                </select>
                <input type="text" style="width: 24%" name="phone_consulting_15min" value="<?php echo $ConFee_data['0']['phone_consulting_15min']; ?>" placeholder="15分钟价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
                <input type="text" style="width: 24%" name="phone_consulting_30min" value="<?php echo $ConFee_data['0']['phone_consulting_30min']; ?>" placeholder="30分钟价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">视频咨询价格：</h5>
                <select name="is_open_video" id="is_open_video" style="width: 24%">
                    <option value="yes" 
                        <?php if($DoctorData['is_open_video'] == 'yes'): ?>
                            selected
                        <?php endif; ?>
                    >开启</option>
                    <option value="no" 
                        <?php if($DoctorData['is_open_video'] == 'no'): ?>
                            selected
                        <?php endif; ?>
                    >关闭</option>
                </select>
                <input type="text" style="width: 24%" name="video_consulting_15min" value="<?php echo $ConFee_data['0']['video_consulting_15min']; ?>" placeholder="15分钟价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
                <input type="text" style="width: 24%" name="video_consulting_30min" value="<?php echo $ConFee_data['0']['video_consulting_30min']; ?>" placeholder="30分钟价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
            <div class="Box2" style="width: 100%">
                <h5 class="h5">私人医生价格：</h5>
                <select name="is_open_private" id="is_open_private" style="width: 24%">
                    <option value="yes" 
                        <?php if($DoctorData['is_open_private'] == 'yes'): ?>
                            selected
                        <?php endif; ?>
                    >开启</option>
                    <option value="no" 
                        <?php if($DoctorData['is_open_private'] == 'no'): ?>
                            selected
                        <?php endif; ?>
                    >关闭</option>
                </select>
                <input type="text" style="width: 24%" name="family_dactor_1month" value="<?php echo $ConFee_data['0']['family_dactor_1month']; ?>" placeholder="1个月价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
                <input type="text" style="width: 24%" name="family_dactor_6month" value="<?php echo $ConFee_data['0']['family_dactor_6month']; ?>" placeholder="半年价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
                <input type="text" style="width: 24%" name="family_dactor_1year" value="<?php echo $ConFee_data['0']['family_dactor_1year']; ?>" placeholder="1年价格/元" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
        </div>
        <div class="But-Box clearfix">
            <button class="Bt1" onclick="javascript:window.history.back(-1);">取消返回</button>
            <button class="Bt2" onclick="SubmitData();">提交修改</button>
        </div>
    </div>
    
</body>
<script src="__JS__/jquery.min.js"></script>
<!-- 地区选择插件distpicker.js(注意：引入顺序不能错！)-->
<script src="__JS__/distpicker/distpicker.data.js"></script>
<script src="__JS__/distpicker/distpicker.js"></script>
<script src="__JS__/distpicker/main.js"></script>
<script src="__COMPONENT__/Fm/Fm.js"></script>
<!-- 七牛云SDK -->
<!-- <script src="https://unpkg.com/qiniu-js/dist/qiniu.min.js"></script> -->
<script>
    //alert('<?php echo $QnToken; ?>');
    var T = new Tool();
    Popups = new Popups();
    //创建用来读取此文件的对象(具体二进制内容)
    var reader = new FileReader();

    //头像上传预览图生成
    function UpImgChangeToop(dom) {
        //.get()将dom对象转换为转为原生对象
        var file = $(dom).get(0).files[0];
        //将文件读取为 DataURL
        reader.readAsDataURL(file);
        reader.onload = function (e) {
            //console.log(e.target.result);
            var data = e.target.result;
            $('#PortraitImg').attr('src',data);
        }
    }

    //图片放大效果
    function imgmag(dom) {
        Popups.ImgMag(dom,500);
    }

    //数据修改提交
    function SubmitData() {
        Popups.Confirm(500,"确认提交？","提交","不提交",callfun1,callfun2);
        function callfun1() {
            //数据封装
            var data = {};
            //医生头像上传
            var file = $('#file').get(0).files[0];
            //检查input[type=file]是否为空
            if(T.Empty(file)) {
                data['avatar'] = "";
            } else {
                //获取图片信息
                var UpImgName = file.name;
                var UpImgType = file.type;
                var UpImgSize = file.size;
                var formData = new FormData($('#UpImg-Portrait_img')[0]); //把数据封装成FormData对象
                //图片上传七牛云
                $.ajax({
                    //将信息传递至七牛云
                    url: 'http://up.qiniu.com',
                    type: 'post',
                    data: formData,
                    async: false,  
                    cache: false,  
                    contentType: false,  
                    processData: false,  
                    success: function(returndata) {  
                        data['avatar'] = "http://cdn.uyihui.cn/" + returndata['key'];
                    },
                    beforeSend: function(XMLHttpRequest){
                        console.log("等待七牛云服务器返回值中....");
                    },
                    error: function(returndata) { 
                        console.log(returndata);
                    }  
                });
            }
            
            // 医生id
            data['doctor_id'] = T.GetData('doctor_id');
            //认证状态
            data['audit_status'] = $("select[name='audit_status']").val();
            // 医生基本信息
            data['nick_name'] = $("input[name='nick_name']").val();
            data['mobile'] = $("input[name='mobile']").val();
            data['email'] = $("input[name='email']").val();
            data['id_card'] = $("input[name='id_card']").val();
            data['gender'] = $("select[name='gender']").val();
            data['title'] = $("select[name='Doctor_jod']").val();
            data['province'] = $("select[name='province']").val();
            data['city'] = $("select[name='city']").val();
            data['intro1'] = $("textarea[name='intro1']").val();
            data['intro2'] = $("textarea[name='intro2']").val();
            data['intro3'] = $("textarea[name='intro3']").val();
            data['intro4'] = $("textarea[name='intro4']").val();
            // 医生科室信息
            data['hospital'] = $("input[name='hospital']").val();
            data['department_parent'] = $("select[name='department_parent']").val();
            data['department_phone'] = $("input[name='department_phone']").val();
            // 医生咨询开启信息
            data['is_open_image'] = $("select[name='is_open_image']").val();
            data['is_open_phone'] = $("select[name='is_open_phone']").val();
            data['is_open_video'] = $("select[name='is_open_video']").val();
            data['is_open_private'] = $("select[name='is_open_private']").val();
            // 医生咨询金额信息
            data['text_consulting_1'] = $("input[name='text_consulting_1']").val();
            data['phone_consulting_15min'] = $("input[name='phone_consulting_15min']").val();
            data['phone_consulting_30min'] = $("input[name='phone_consulting_30min']").val();
            data['video_consulting_15min'] = $("input[name='video_consulting_15min']").val();
            data['video_consulting_30min'] = $("input[name='video_consulting_30min']").val();
            data['family_dactor_1month'] = $("input[name='family_dactor_1month']").val();
            data['family_dactor_6month'] = $("input[name='family_dactor_6month']").val();
            data['family_dactor_1year'] = $("input[name='family_dactor_1year']").val();
            //医生登录密码
            data['password'] = $("input[name='password']").val();
            //医生标签
            data['LabeData'] = $("select[name='Label1']").val() + ',' + $("select[name='Label2']").val() + ',' + $("select[name='Label3']").val();
            console.log(data);
            $.ajax({
                url: "AlterPost",
                type: 'post',
                data: data,
                success: function(data) {
                    alert(data);
                    //location.reload();
                    //window.location.href = "index";
                    var Referrer = document.referrer;
                    window.location.href = Referrer;
                }
            });
        }
        function callfun2() {
            // 刷新页面
            location.reload();
        }
    }
    
</script>
</html>