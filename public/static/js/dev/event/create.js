define(function (){
    return function(){

        // 验证表单

        $("#form").validate({
            submitHandler: function(form){
                formSubmit();
            },
            ignore: "",
            rules: {
                'doctor_id': {
                    required: true,
                    digits: true
                },
                'title': {
                    required: true
                },
                'video_url': {
                    required: true
                },
                'join_count': {
                    required: true
                }
            },
            errorPlacement: function(error, element) {
                $(error).addClass('alert alert-danger');
                $( element ).after( error );
            },
            errorElement: "div",
            messages: {
                'doctor_id': {
                    required: "医生ID必填",
                    digits:"必须为整数"
                },
                'title': {
                    required: "标题必填",
                },
                'video_url': {
                    required: "视频地址必填",
                },
                'join_count': {
                    required: "参与人数必填",
                }
            }

        });

        function formSubmit(){
            var submit_data = {
                'doctor_id': $("input[name=doctor_id]").val(),
                'title': $("input[name=title]").val(),
                'video_url': $("input[name=video_url]").val(),
                'event_status': $("input[name=event_status]:checked").val(),
                'start_time': $("input[name=start_time]").val(),
                'end_time': $("input[name=end_time]").val(),
                'join_count': $("input[name=join_count]").val(),
            };

            var url = appConfig.adminPath + 'Event/add';

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
                        onhide: function(dialogRef){
                            $.loader(true);
                            location.href = appConfig.adminPath + 'event/index';
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