define(function (){
    return function(){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'order_num': {
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
                'order_num': {
                    required: "优先级必填",
                    digits:"必须为整数"
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'filter': $("input[name=filter]").val(),
                'order_num': $("input[name=order_num]").val(),
                'img_url': $("input[name=img_url]").val(),
                'href_url': $("input[name=href_url]").val()
            };

            var url = appConfig.adminPath + 'banner/add';
            var filter = $("input[name=filter]").val();

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
                            location.href = appConfig.adminPath + 'banner/index'+"?filter=" + filter;;
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