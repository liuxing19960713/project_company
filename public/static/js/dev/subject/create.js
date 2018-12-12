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
                'keywords' : {
                    required:true
                },

                'descr' : {
                    required:true
                },

  
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
                'keywords': {
                    required: "关键字不能为空",
                },

                'desrc': {
                    required: "描述不能为空",
                },
                // 'url': {
                //     required: "链接必填",
                //     digits:"不能为空"

                // // },
                // 'content': {
                //     required: "帖子内容不能为空",
                // }
            }

        });

        function formSubmit(){
            var submit_data = {
                'title': $("input[name=title]").val(),
                'keywords': $("input[name=keywords]").val(),
                // 'desrc': $("textarea[name=content]").val(),

                'desrc': $("#content").val(),


                // 'img_url': $("input[name=img_url]").val(),
                //'video_url': $("input[name=video_url]").val(),
                //'url': $("input[name=url]").val(),
                // 'url': $("input[name=url]").val(),
            };

            var url = appConfig.adminPath + 'subject/save';

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
                            location.href = appConfig.adminPath + 'subject/index';
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