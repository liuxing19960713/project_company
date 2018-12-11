define(function (){
    return function(){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'user_id': {
                    required: true,
                    digits: true
                },
                'title': {
                    required: true
                },
                'content': {
                    required: true,
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'user_id': {
                    required: "ID必填",
                    digits: "ID必须为整数"
                },
                'title': {
                    required: "帖子标题不能为空"
                },
                'content': {
                    required: "帖子内容不能为空"
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'user_id': $("input[name=user_id]").val(),
                'title': $("input[name=title]").val(),

                'post_type': $("input[name='post_type']:checked").val(),
                'group_type': $("input[name='group_type']:checked").val(),
                'is_top': $("input[name='is_top']:checked").val(),
                'is_best': $("input[name='is_best']:checked").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Post/Doctor_add';

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
                        // onshow: function(dialogItself){
                        //     // setTimeout(function(){
                        //     //
                        //     //     // location.reload();
                        //         location.href = appConfig.adminPath + 'Post/index';
                        //     }, 2000);
                        //
                        // },
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = appConfig.adminPath + 'Post/doctor';
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