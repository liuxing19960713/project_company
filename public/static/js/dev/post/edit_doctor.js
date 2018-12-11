define(function (){
    return function(){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'title': {
                    required: true
                },
                'hospital_id': {
                    required: true,
                    digits: true
                }

            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'title': {
                    required: "标题必填",
                },
                'hospital_id': {
                    required: "医院ID必填",
                    digits: "请填入整数",
                }
            }
        });

        function formSubmit(){
            var submit_data = {
                'id': $("input[name=id]").val(),
                'user_id': $("input[name=user_id]").val(),
                'title': $("input[name=title]").val(),
                'post_type': $("input[name='post_type']:checked").val(),
                'group_type': $("input[name='group_type']:checked").val(),
                'views_count': $("input[name='views_count']").val(),
                'is_top': $("input[name='is_top']:checked").val(),
                'is_best': $("input[name='is_best']:checked").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Post/Doctor_edit';

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