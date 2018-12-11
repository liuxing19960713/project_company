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
                'sub_title': {
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
                'sub_title': {
                    required: "副标题必填",
                },
                'content': {
                    required: "帖子内容不能为空",
                }
            }
        });

        function formSubmit(){
            var submit_data = {
                'tip_id': $("input[name=tip_id]").val(),
                'title': $("input[name=title]").val(),
                'sub_title': $("input[name=sub_title]").val(),
                'img_url': $("input[name=img_url]").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Tips/update';
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