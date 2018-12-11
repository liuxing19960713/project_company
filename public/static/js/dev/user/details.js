define(function (){
    return function (){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
            }

        });

        function formSubmit(){
            var submit_data = {
                'real_name': $("input[name=real_name]").val(),
                'user_id': $("input[name=user_id]").val(),
                'nick_name': $("input[name=nick_name]").val(),
                'birthday': $("input[name=birthday]").val(),
                'mobile': $("input[name=mobile]").val(),
                'career': $("input[name=career]").val(),
                'gender': $("input[name=gender]:checked").val(),
                'money' : $("input[name=money]").val(),
                // $("#s option:selected").val();
                'marriage': $("input[name=marriage]:checked").val(),

                'blood_type': $("#blood_type").val(),
                'age': $("input[name=age]").val(),
                'province': $("input[name=province]").val(),
                'city': $("input[name=city]").val(),

            };

            var url = appConfig.adminPath + 'user/userEdit';
            var history = $("input[name=url]").val();

            $.loader(true);

            $.ajax({
                url: url,
                data: submit_data,
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $.loader(false);
                    BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_SUCCESS,
                        message: '更新成功',

                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = history;
                        }
                    });
                },
                error: function (raw) {
                    $.loader(false);
                    showErrorDialog(raw);
                }
            });
        }

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

        //删除试管记录
        $(".js-del").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除编号为' + $(this).data('id') + '的试管记录？',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'tr_id': $delBtn.data('id'),
                            'trc_id': $delBtn.data('cid')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'User/deleted',
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

        return;
    };
});