define(function (){
    return function(){
        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'post_id': {
                    required: true,
                    digits: true
                },
                'user_id': {
                    required: true,
                    digits: true
                },
                'content': {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'post_id': {
                    required: "帖子ID必填",
                    digits:"必须为整数"
                },
                'user_id': {
                    required: "用户ID必填",
                    digits:"必须为整数"

                },
                'content': {
                    required: "评论内容不能为空",
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'post_id': $("#post_id").val(),
                'user_id': $("#user_id").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'comment/Add';

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
                            location.href = appConfig.adminPath + 'comment/index';
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