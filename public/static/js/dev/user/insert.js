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
                    required: true,
                    digits: true
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
                    required: "手机号必填",
                    digits: "账号为数字"

                },
                'nick_name': {
                    required: "用户昵称必填"


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

            var url = appConfig.adminPath + 'User/useradd';

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
                            location.href = appConfig.adminPath + 'User/index';
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