define(function (){
    return function(){
        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {

                'ad_name': {
                    required: true
                },

                'colum_id': {
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
                'ad_name': {
                    required: "广告名必填",
                },
                  'colum_id': {
                    required: "栏目必填",
                },

                'img_url': {
                    required: "广告图片不能为空"
                }
             
            }

        });

        function formSubmit(){
            var submit_data = {
                'ad_name': $("input[name=ad_name]").val(),
                'colum_id': $("select[name=colum_id]").val(),

                'img_url': $("input[name=img_url]").val(),
                //'video_url': $("input[name=video_url]").val(),
                // //'url': $("input[name=url]").val(),
                // 'keywords': $("input[name=keywords]").val(),
                // 'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Advert/save';

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
                            location.href = appConfig.adminPath + 'Advert/index';
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