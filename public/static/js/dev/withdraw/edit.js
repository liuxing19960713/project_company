define(function (){
    return function(){
        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'admin_name': {
                    required: true
                },
                'reason': {
                    required: true
                },
                'remarks': {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'admin_name': {
                    required: "请填写审核人",

                },
                'reason': {
                    required: "请填写审核人",

                },
                'remarks': {
                    required: "备注不能为空",
                }
            }
        });

        function formSubmit(){
            var submit_data = {
                'id': $("input[name=id]").val(),
                'status': $("input[name='status']:checked").val(),
                'admin_name': $("input[name=admin_name]").val(),
                'reason': $("#reason").val(),
                'remarks': $("#remarks").val()
            };

            var url = appConfig.adminPath + 'withdraw/Edit';

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
                            location.href = appConfig.adminPath + 'withdraw/index';
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
});