define(function (){
    return function(){
        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
            }
        });

        function formSubmit(){
            var submit_data = {
                'id': $("input[name=report_type_id]").val(),
                'report_id': $("input[name=report_id]").val(),
                'report_type': $("input[name=report_type]").val(),
                'report_status': $("input[name=report_status]:checked").val()
            };
			
			var report_types = $("input[name=report_type]").val();
			
			if(report_types=="post"){
				var urls = 'report/index';
			}else{
				var urls = 'report/index'+"?filter=评论举报";
			}

            var url = appConfig.adminPath + 'report/Edit';

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
                        message: '处理成功',
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = appConfig.adminPath + urls;
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