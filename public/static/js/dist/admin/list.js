define(function(){return function(){$(".js-del").on("click",function(){var o=$(this);BootstrapDialog.show({type:BootstrapDialog.TYPE_WARNING,message:"是否删除账户为"+$(this).data("account")+"的管理员？",buttons:[{label:"删除",action:function(a){a.close(),$.loader(!0);var t={admin_id:o.data("id")};$.ajax({url:appConfig.adminPath+"Admin/destroy",data:t,dataType:"json",success:function(o){$.loader(!1),o.success?BootstrapDialog.show({type:BootstrapDialog.TYPE_SUCCESS,message:"删除成功",buttons:[{label:"关闭",action:function(o){$.loader(!0),location.reload()}}],onhide:function(){$.loader(!0),location.reload()}}):BootstrapDialog.show({message:o.errmsg,buttons:[{label:"关闭",action:function(o){o.close()}}]})}})}},{label:"取消",action:function(o){o.close()}}]})}),$("#pager").pagination({dataCount:$("input[name=admin_list_count]").val()})}});
//# sourceMappingURL=list.js.map