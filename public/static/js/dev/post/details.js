define(function (){
    return function (){

        // 删除按钮事件
        $(".js-del").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除这条评论！！',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'id': $delBtn.data('id')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'Post/delete',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    message: '删除成功',
                                    onshow: function(){
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
                                    onhide: function () {
                                        $.loader(true);
                                        location.reload();
                                    }
                                });
                            },
                            error: function (raw) {
                                $.loader(false);
                                showErrorDialog(raw);
                            }
                        });
                    }
                }, {
                    label: '取消',
                    action: function(dialog) {
                        dialog.close();
                    }
                }]
            });
        });

        // 加载分页
        $("#pager").pagination({'dataCount' : $("input[name=post_list_count]").val()});

        return;
    };
});