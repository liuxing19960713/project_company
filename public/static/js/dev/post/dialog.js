define(function (){
    return function (){
        // 显示用户信息dialog
        $(".js-dialog-post").on('click', function () {
            var postId = $(this).data('id');
            var messageHtml = $('<div></div>');
            $.ajax({
                url: appConfig.adminPath + 'Post/getPostInfo',
                data: { 'post_id': postId },
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var htmlTmp = '<div class="media">' +
                        '<div class="media-body">' +
                        '<ul><li>标题：' + data.result.title +'</li>' +
                        '<li>发布时间：' + data.result.create_time +'</li>' +
                        '<li><h>帖子内容：</h>' + data.result.content + '</li></ul></div></div>';
                    messageHtml.append(htmlTmp);
                },
                error: function (raw) {
                    // showErrorDialog(raw);
                    messageHtml.append('获取帖子信息失败');
                }
            });
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '帖子详情信息',
                message: messageHtml,
                buttons: [{
                    label: '查看更多',
                    action: function (dialog) {
                        location.href = appConfig.adminPath + 'Post/edit?id=' + postId;
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

        $(".post_edit").on('click', function(){

            var postId = $(this).data('id');
            var tid = $(this).data('tid');
            var status = $(this).data('status');
            var waitSelected = (status == '待处理 ') ? 'selected' : '';
            var okSelected = (status == '已处理') ? 'selected' : '';
            var messageHtml =
                '举报列表ID: <input type="text" readonly class="form-control" value="' + postId +'"><br>' +
                '评论列表ID: <input type="text" readonly class="form-control" value="' + tid +'"><br>' +
                '请选择状态: <select name="status" class="form-control" id="status">' +
                '<option value="待处理" ' + waitSelected + '>待处理</option>' +
                '<option value="已处理" ' + okSelected + '>已处理</option></select><br>' +
                '<span style="color:red;">注意</span>: 请选择已处理选项;当点击确定时则可以删除举报的内容<br>';
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '审核处理帖子举报内容',
                message: messageHtml,
                buttons: [{
                    label: '确定',
                    action: function(dialog) {
                        dialog.close();
                        $.loader(true);
                        var submit_data = {
                            'report_id': postId,
                            'report_type_id': tid,
                            'status': $("#status").find("option:selected").text()
                        };
                        $.ajax({
                            url: appConfig.adminPath + 'Report/review',
                            data: submit_data,
                            dataType: 'json',
                            success: function (data) {
                                $.loader(false);
                                BootstrapDialog.show({
                                    type: BootstrapDialog.TYPE_INFO,
                                    message: '审核成功',
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