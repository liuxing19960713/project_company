define(function (){
    return function(){

        // 验证表单
        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'audit_hospital': {
                    required: true
                },
                'audit_department1': {
                    required: true
                },
                'audit_department2': {
                    required: true
                }

            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'audit_hospital': {
                    required: "医院审核必填"
                },
                'audit_department1': {
                    required: "科室1审核必填"
                },
                'audit_department2': {
                    required: "科室2审核必填"
                },

            }

        });

        function formSubmit(){
            var submit_data = {
                'doctor_id': $("input[name=doctor_id]").val(),
                'da_id': $("input[name=da_id]").val(),
                'de_id': $("input[name=de_id]").val(),
                'hospital': $("input[name=audit_hospital]").val(),
                'department1': $("input[name=audit_department1]").val(),
                'department2': $("input[name=audit_department2]").val(),
                // 'phone': $("input[name=audit_department_phone]").val(),

                'front': $("input[name=qualification_front]").val(),
                'back': $("input[name=qualification_back]").val(),
                'status': $("input[name='status']:checked").val(),
                'feedback': $("#feedback").val()
            };

            var url = appConfig.adminPath + 'Doctor/check';

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
                        message: '操作成功',
                        onshow: function(dialogItself){
                            setTimeout(function(){

                                location.reload();
                            }, 2000);

                        },
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }],
                        // onhide: function(dialogRef){
                        //     $.loader(true);
                        //     location.href = appConfig.adminPath + 'Tips/index';
                        // }
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