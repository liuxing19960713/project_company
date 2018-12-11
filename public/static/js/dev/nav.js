// 导航栏事件
(function(){
    // 退出登录按钮事件
    $("#btn_logout").click(function(){
        $.loader(true);
        $.ajax({
            url: appConfig.adminPath + 'Site/logout',
            success: function(data){
                $.loader(false);
                BootstrapDialog.show({
                    title: '提示',
                    message: '退出登录',
                    buttons: [{
                        label: '关闭',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    }],
                    onhide: function(dialogRef){
                        $.loader(true);
                        location.href = appConfig.adminPath + 'Site/login';
                    }
                });
            },
            error: function (raw) {
                $.loader(false);
                showErrorDialog(raw);
            }
        });
    });
})();


