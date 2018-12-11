(function(){
    $('[data-toggle="tooltip"]').tooltip();
})();
var BootstrapDialog = require('bootstrap-dialog');
var showErrorDialog = function (raw) {
    var msg = raw.responseJSON ? raw.responseJSON.message : "内部错误T_T";
    BootstrapDialog.show({
        type: BootstrapDialog.TYPE_DANGER,
        title: '出错啦',
        message: msg,
        buttons: [{
            label: '关闭',
            action: function(dialogItself){
                dialogItself.close();
            }
        }]
    });
};