define(function (){
    return function(){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'mobile': {
                    required: true
                },
                'password': {
                    required: true

                },
                'nick_name': {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'mobile': {
                    required: "账号必填"
                },
                'nick_name': {
                    required: "姓名必填"


                },
                'password': {
                    required: "密码不能为空"
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'mobile': $("input[name=mobile]").val(),
                'password': $("input[name=password]").val(),
                'nick_name': $("input[name=nick_name]").val(),
            };

            var url = appConfig.adminPath + 'Doctor/add';

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
                        message: '新增成功',
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = appConfig.adminPath + 'Doctor/index';
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