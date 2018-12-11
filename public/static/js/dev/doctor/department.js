define(function (){
    return function (){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'audit_hospital': {
                    required: true
                },
                'audit_department1': {
                    required: true
                },
                'audit_department2': {
                    required: true
                },
                'audit_department_phone': {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'audit_hospital': {
                    required: "审核医院必填",
                },
                'audit_department1': {
                    required: "父级科室必填",

                },
                'audit_department2': {
                    required: "子集科室",
                },
                'audit_department_phone': {
                    required: "审核电话不能为空"
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'de_id': $("input[name=de_id]").val(),
                'd_id': $("input[name=d_id]").val(),
                'audit_hospital': $("input[name=audit_hospital]").val(),
                'audit_department1': $("input[name=audit_department1]").val(),
                'audit_department2': $("input[name=audit_department2]").val(),
                'audit_department_phone': $("input[name=audit_department_phone]").val(),
                'feedback': $("input[name=feedback]").val(),
                'is_default': $("input[name='is_default']:checked").val(),
                'is_audited': $("input[name='is_audited']:checked").val()

            };

            var url = appConfig.adminPath + 'Doctor/editDepartment';

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
                        message: '编辑成功',
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = 'javascript:history.back(-1)';
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

        // 加载分页
        $("#pager").pagination({'dataCount' : $("input[name=count]").val()});

        return;
    };
});