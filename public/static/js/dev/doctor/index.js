define(function (){
    return function (){

        // 删除按钮事件
        $(".js-del").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除编号为' + $(this).data('id') + '的医生账号？',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'post_id': $delBtn.data('id')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'Doctor/destroy',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    message: '删除成功',
                                    onshow: function(dialogItself){
                                        setTimeout(function(){

                                            location.reload();
                                        }, 2000);

                                    },
                                    buttons: [{
                                        label: '关闭',
                                        action: function(dialogItself){
                                            dialogItself.close();
                                        }
                                    }],
                                    onhide: function () {
                                        $.loader(true);
                                        location.reload();
                                    }
                                });
                            },
                            error: function (raw) {
                                $.loader(false);
                                showErrorDialog(raw);
                            }
                        });
                    }
                }, {
                    label: '取消',
                    action: function(dialog) {
                        dialog.close();
                    }
                }]
            });
        });

        // 删除按钮事件
        $(".js-department").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除' +$(this).data('name') + '的编号为' + $(this).data('id') + '的科室？',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'post_id': $delBtn.data('id')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'Doctor/deleteDepartment',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    message: '删除科室成功',
                                    onshow: function(dialogItself){
                                        setTimeout(function(){

                                            location.reload();
                                        }, 2000);

                                    },
                                    buttons: [{
                                        label: '关闭',
                                        action: function(dialogItself){
                                            dialogItself.close();
                                        }
                                    }],
                                    onhide: function () {
                                        $.loader(true);
                                        location.reload();
                                    }
                                });
                            },
                            error: function (raw) {
                                $.loader(false);
                                showErrorDialog(raw);
                            }
                        });
                    }
                }, {
                    label: '取消',
                    action: function(dialog) {
                        dialog.close();
                    }
                }]
            });
        });

        // 编辑事件
        $(".js-insert").on('click', function(){
            var $editBtn = $(this);

            var messageHtml =
                '医生ID号: <input type="text" readonly class="form-control" value="' + $editBtn.data('id') +'"><br>' +
                '医院: <input type="text" name="hospital" class="form-control" value=""><br>' +
                '父级科室: <input type="text" name="department1" class="form-control" value=""><br>' +
                '子集科室: <input type="text"  name="department2" class="form-control" value=""><br>' +
                '科室电话: <input type="text" name="department_phone" class="form-control" value=""><br>';
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '添加相关案例',
                message: messageHtml,
                buttons: [{
                    label: '确定',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'd_id': $editBtn.data('id'),
                            'hospital': $("input[name=hospital]").val(),
                            'department1': $("input[name=department1]").val(),
                            'department2': $("input[name=department2]").val(),
                            'department_phone': $("input[name=department_phone]").val()

                        };
                        $.ajax({
                            url: appConfig.adminPath + 'Doctor/addDeparment',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    message: '添加成功',
                                    buttons: [{
                                        label: '关闭',
                                        action: function(dialogItself){
                                            dialogItself.close();
                                        }
                                    }],
                                    onhide: function () {
                                        $.loader(true);
                                        location.reload();
                                    }
                                });
                            },
                            error: function (raw) {
                                $.loader(false);
                                showErrorDialog(raw);
                            }
                        });
                    }
                }, {
                    label: '取消',
                    action: function(dialog) {
                        dialog.close();
                    }
                }]
            });
        });

        // 显示缩略图
        $(".js-thumb").on('click', function(){
            var $textAndPic = $('<div style="margin: auto;text-align: center;"></div>');
            $textAndPic.append('<img style="width: 70%;height:60%;" src="' + $(this).data('thumb') + '" />');
            BootstrapDialog.show({
                size: BootstrapDialog.SIZE_WIDE,
                type: BootstrapDialog.TYPE_INFO,
                title: '查看医生图片',
                message: $textAndPic,
            });
        });

        // $(".depar2").on('click', function(){
        //     alert('666');exit;
        //     $(".depar2").attr('checked','false');
        //     $("#deparment2").attr('checked','true');
        //     $(".depar3").attr('checked','false');
        //     $("#deparment3").attr('checked','true');
        //     // return ($("#deparment3"));
        // });

        // 加载分页
        $("#pager").pagination({'dataCount' : $("input[name=count]").val()});

        return;
    };
});