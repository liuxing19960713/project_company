define(function (){
    return function (){

        // 删除按钮事件
        $(".js-del").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除编号为' + $(this).data('id') + '的活动专区？',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'post_id': $delBtn.data('id')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'Event/destroy',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    message: '删除成功',
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

        // 加载分页
        $("#pager").pagination({'dataCount' : $("input[name=count]").val()});

        return;
    };
});