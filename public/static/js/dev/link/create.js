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
                // 'url': {
                //     required: true,
                //     digits: true
                // },
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
                // 'url': {
                //     required: "链接必填",
                //     digits:"不能为空"

                // },
                'content': {
                    required: "帖子内容不能为空",
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'title': $("input[name=title]").val(),
                'img_url': $("input[name=img_url]").val(),
                //'video_url': $("input[name=video_url]").val(),
                //'url': $("input[name=url]").val(),
                'url': $("input[name=url]").val(),
                'memo': $("#content").val()
            };

            var url = appConfig.adminPath + 'Link/save';

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
                            location.href = appConfig.adminPath + 'Link/index';
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