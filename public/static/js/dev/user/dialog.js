define(function (){
    return function (){
        // 显示用户信息dialog
        $(".js-dialog-user").on('click', function () {
            var userId = $(this).data('id');
            var messageHtml = $('<div></div>');
            $.ajax({
                url: appConfig.adminPath + 'User/getUserInfo',
                data: { 'user_id': userId },
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var htmlTmp = '<div class="media">' +
                        '<div class="media-left"><a href="#"><img class="media-object" style="max-height: 164px;max-width: 164px;" src="' + data.result.avatar + '" alt="..."></a></div>' +
                        '<div class="media-body"><h4 class="media-heading">' + data.result.nick_name + '</h4>' +
                        '<ul><li>性别：' + data.result.gender + '</li>' +
                        '<li>地区：' + data.result.area + '</li>' +
                        '<li>手机号：' + data.result.mobile + '</li>' +
                        '<li>邮箱：' + data.result.email + '</li>' +
                        '<li>注册时间：' + data.result.create_time + '</li></ul></div></div>';
                    messageHtml.append(htmlTmp);
                },
                error: function (raw) {
                    // showErrorDialog(raw);
                    messageHtml.append('获取用户信息失败');
                }
            });
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '用户信息',
                message: messageHtml,
                buttons: [{
                    label: '查看更多',
                    action: function (dialog) {
                        location.href = appConfig.adminPath + 'User/details?id=' + userId;
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


        return;
    };
});