define(function (){
    return function (){

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
                'doctor_id': $("input[name=doctor_id]").val(),
                'nick_name': $("input[name=nick_name]").val(),
                //'mobile': $("input[name=mobile]").val(),
                //'email': $("input[name=email]").val(),
                'id_card': $("input[name=id_card]").val(),
                'title': $("input[name=title]").val(),
                'gender': $("input[name=gender]:checked").val(),

                'age': $("input[name=age]").val(),
                'province': $("input[name=province]").val(),
                'city': $("input[name=city]").val(),
                'good_at': $("#good_at").val(),
                'intro1': $("#intro1").val(),
                'intro2': $("#intro2").val(),
                'intro3': $("#intro3").val(),

                'image': $("input[name=is_open_image]:checked").val(),
                'image_price': $("input[name=image_price]").val(),

                'phone': $("input[name=is_open_phone]:checked").val(),
                'phone_price': $("input[name=phone_price]").val(),

                'video': $("input[name=is_open_video]:checked").val(),
                'video_price': $("input[name=video_price]").val(),

                'private': $("input[name=is_open_private]:checked").val(),
                'private_price': $("input[name=private_price]").val(),

                'price_percentage': $("input[name=price_percentage]").val(),
                'ex_price': $("input[name=ex_price]").val(),
                'img_url': $("input[name=img_url]").val(),
				
                'de_id': $("input[name=de_id]").val(),
                'hospital': $("input[name=hospital]").val(),
                'department1': $("input[name=department1]").val(),
                'department2': $("input[name=department2]").val(),
                'department_phone': $("input[name=department_phone]").val(),
                'is_default': $("input[name=is_default]:checked").val(),
                'is_audited': $("input[name=is_audited]:checked").val()


            };

            var url = appConfig.adminPath + 'Doctor/doctorEdit';

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
                            location.href = appConfig.adminPath + 'Doctor/index';
                        }
                    });
                },
                error: function (raw) {
                    $.loader(false);
                    showErrorDialog(raw);
                }
            });
        }



        // 显示缩略图
        $(".js-thumb").on('click', function(){
            var $textAndPic = $('<div style="margin: auto;text-align: center;"></div>');
            $textAndPic.append('<img style="width: 70%;height:60%;" src="' + $(this).data('thumb') + '" />');
            BootstrapDialog.show({
                size: BootstrapDialog.SIZE_WIDE,
                type: BootstrapDialog.TYPE_INFO,
                title: '查看医生图片',
                message: $textAndPic,
            });
        });

        return;
    };
});