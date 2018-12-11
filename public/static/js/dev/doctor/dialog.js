define(function (){
    return function (){
        // 显示用户信息dialog
        $(".js-dialog-doctor").on('click', function () {
            var userId = $(this).data('id');
            var messageHtml = $('<div></div>');
            $.ajax({
                url: appConfig.adminPath + 'Doctor/getUserInfo',
                data: { 'doctor_id': userId },
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var htmlTmp = '<div class="media">' +
                        '<div class="media-left"><a href="#"><img class="media-object" style="max-height: 164px;max-width: 164px;" src="' + data.result.avatar + '" alt="..."></a></div>' +
                        '<div class="media-body"><h4 class="media-heading">' + data.result.nick_name + '</h4>' +
                        '<ul><li>性别：' + data.result.gender + '</li>' +
                        '<li>职称：' + data.result.title + '</li>' +
                        '<li>附属医院：' + data.result.hospital + '</li>' +
                        '<li>擅长疾病：' + data.result.good_at + '</li>' +
                        '<li>年龄：' + data.result.age + '岁</li>' +
                        '<li>地区：' + data.result.area + '</li>' +
                        '<li>手机号：' + data.result.mobile + '</li>' +
                        '<li>账户余额：' + data.result.money + '</li>' +
                        '<li>注册时间：' + data.result.create_time + '</li></ul></div></div>';
                    messageHtml.append(htmlTmp);
                },
                error: function (raw) {
                    // showErrorDialog(raw);
                    messageHtml.append('获取医生信息失败');
                }
            });
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '医生信息',
                message: messageHtml,
                buttons: [{
                    label: '查看更多',
                    action: function (dialog) {
                        location.href = appConfig.adminPath + 'Doctor/details?id=' + userId;
                        dialog.close();
                    }
                }, {
                    label: '关闭',
                    action: function (dialog) {
                        dialog.close();
                    }
                }]
            });
        });

        // 删除按钮事件
        $(".js-feed").on('click', function(){
            var $delBtn = $(this);
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                message: '是否删除编号为' + $(this).data('id') + '的医生反馈信息？',
                buttons: [{
                    label: '删除',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'post_id': $delBtn.data('id')
                        };

                        $.ajax({
                            url: appConfig.adminPath + 'Feedback/destroy',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_SUCCESS,
                                    title: '医生反馈信息',
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




        return;
    };
});