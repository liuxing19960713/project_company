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

                 'hospital': {
                    required: true
                },

                // 'nick_name': {
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
                    required: "职称不能为空",
                },

                // 'nick_name': {
                //     required: "姓名不能为空",
                //     digits:"姓名不能为空"

                // },

                'hospital': {
                    required: "所在医院不能为空",
                    digits:"不能为空"

                },


                'content': {
                    required: "介绍不能为空",
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'title': $("input[name=title]").val(),
                'nick_name': $("input[name=nick_name]").val(),
                'hospital': $("input[name=hospital]").val(),
                'img_url': $("input[name=img_url]").val(),
                //'video_url': $("input[name=video_url]").val(),
                //'url': $("input[name=url]").val(),
                // 'keywords': $("input[name=keywords]").val(),
                'introduce': $("#content").val()
            };

            var url = appConfig.adminPath + 'cdoctor/save';

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
                            location.href = appConfig.adminPath + 'cdoctor/index';
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