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
                'views_count': {
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
                'title': {
                    required: "标题必填",
                },
                'views_count': {
                    required: "点击率必填",
                    digits:"必须为整数"

                },
                'content': {
                    required: "帖子内容不能为空",
                }
            }
        });

        function formSubmit(){
            var submit_data = {
                'an_id': $("input[name=an_id]").val(),
                'title': $("input[name=title]").val(),
                'img_url': $("input[name=img_url]").val(),
                'views_count': $("input[name=views_count]").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'news/Edit';
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
    };
});