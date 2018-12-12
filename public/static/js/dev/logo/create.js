define(function (){
    return function(){
        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {

                'logo_name': {
                    required: true
                },

                'img_url': {
                    required: true
                }
                

               
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'logo_name': {
                    required: "logo名必填",
                },

                'img_url': {
                    required: "logo图片不能为空"
                }
             
            }

        });

        function formSubmit(){
            var submit_data = {
                'logo_name': $("input[name=logo_name]").val(),
                'img_url': $("input[name=img_url]").val(),
                //'video_url': $("input[name=video_url]").val(),
                // //'url': $("input[name=url]").val(),
                // 'keywords': $("input[name=keywords]").val(),
                // 'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Logo/save';

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
                            location.href = appConfig.adminPath + 'logo/index';
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