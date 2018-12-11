define(function () {
    return function () {

        // 显示缩略图
        $(".js-thumb").on('click', function(){
            var $textAndPic = $('<div style="margin: auto;text-align: center;"></div>');
            $textAndPic.append('<img style="width: 70%;height:60%;" src="' + $(this).data('thumb') + '" />');
            BootstrapDialog.show({
                size: BootstrapDialog.SIZE_WIDE,
                type: BootstrapDialog.TYPE_INFO,
                title: '查看医生图片',
                message: $textAndPic
            });


        });
        // 验证表单
        $("#form").validate({
            submitHandler: function (form) {
                formSubmit();
            },
            ignore: "",
            rules: {
                'department': {
                    required: true
                }
            },
            errorPlacement: function (error, element) {
                $(error).addClass('alert alert-danger');
                $(element).after(error);
            },
            errorElement: "div",
            messages: {
                'department': {
                    required: "转诊科室名称必填",
                }
            }

        });

        function formSubmit() {
            var submit_data = {
                'department': $("input[name=department]").val(),

            };

            var url = appConfig.adminPath + 'Exdoctor/insert';

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
                        title: '转诊科室信息',
                        message: '新增成功',

                        buttons: [{
                            label: '添加医生',
                            action: function (dialog) {
                                location.href = appConfig.adminPath + 'Doctor/add_doctor?id=' + userId;
                                dialog.close();
                            }
                        }, {
                            label: '关闭',
                            action: function (dialogItself) {
                                dialogItself.close();
                            }

                        }],
                        onhide: function (dialogRef) {
                            $.loader(true);
                            location.href = appConfig.adminPath + 'Exdoctor/index';
                        }
                    });
                },
                error: function (raw) {
                    $.loader(false);
                    showErrorDialog(raw);
                }
            });
        }
    };
})