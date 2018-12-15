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
                'keywords': {
                    required: true,
                  
                },
                'ptitle': {
                    required: true,
                  
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
                'keywords': {
                    required: "关键字必填",
                   

                },
                
                 'ptitle': {
                    required: "浏览器标题必填",
                   

                },
                'content': {
                    required: "帖子内容不能为空",
                }
            }
        });

        function formSubmit(){
            var label_id = '';
            $('#tags input:checked').each(function(){
               // label_id = label_id + $(this).val()+',';
               label_id += $(this).val()+',';
            })
            var submit_data = {
                'id': $("input[name=id]").val(),
                 'title': $("input[name=title]").val(),

                'pic': $("input[name=pic]").val(),

                'iscom': $("input[name=iscom]").val(),

                'shown': $("input[name=shown]").val(),
                'ptitle' :$("input[name=ptitle]").val(),
                'keywords': $("input[name=keywords]").val(),
                'label_id' : label_id,
                'cate': $("select[name=cate]").val(),
                'content': $("#content").val()
            };

            var url = appConfig.adminPath + 'Article/update';
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
                            location.href = appConfig.adminPath + 'Article/index';
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