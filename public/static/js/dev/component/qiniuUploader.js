define(function (){
    return function (elementImg, elementInput, isMulti){

        var uploader = Qiniu.uploader({
            runtimes: 'html5,flash,html4',      // 上传模式,依次退化
            browse_button: 'pickfile',         // 上传选择的点选按钮，**必需**
            uptoken_url: appConfig.publicPath + '/index.php/api/Index/uptoken',  //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
            get_new_uptoken: false,             // 设置上传文件的时候是否每次都重新获取新的 uptoken
            unique_names: true,              // 默认 false，key 为文件名。若开启该选项，JS-SDK 会为每个文件自动生成key（文件名）
            // save_key: true,                  // 默认 false。若在服务端生成 uptoken 的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
            domain: appConfig.qiniuDomain,     // bucket 域名，下载资源时用到，如：'http://xxx.bkt.clouddn.com/' **必需**
            container: 'container',             // 上传区域 DOM ID，默认是 browser_button 的父元素，
            max_file_size: '4mb',             // 最大文件体积限制
            flash_swf_url: appConfig.publicPath + '/static/component/plupload/js/Moxie.swf',  //引入 flash,相对路径
            max_retries: 3,                     // 上传失败最大重试次数
            filters: {
                mime_types: [
                    //只允许上传图片文件 （注意，extensions中，逗号后面不要加空格）
                    { title: "图片文件", extensions: "jpg,gif,png,bmp,jpeg" }
                ]
            },
            dragdrop: true,                     // 开启可拖曳上传
            drop_element: 'container',          // 拖曳上传区域元素的 ID，拖曳文件或文件夹后可触发上传
            chunk_size: '4mb',                  // 分块上传时，每块的体积
            auto_start: true,                   // 选择文件后自动上传，若关闭需要自己绑定事件触发上传,
            multi_selection: isMulti,             // 设置一次只能选择一个文件
            init: {
                'FilesAdded': function(up, files) {
                    plupload.each(files, function(file) {
                        // 文件添加进队列后,处理相关的事情
                    });
                },
                'BeforeUpload': function(up, file) {
                    // 每个文件上传前,处理相关的事情
                    $.loader(true);
                },
                'UploadProgress': function(up, file) {
                    // 每个文件上传时,处理相关的事情
                    // console.log(file.percent);
                },
                'FileUploaded': function(up, file, info) {
                    // 每个文件上传成功后,处理相关的事情
                    // 其中 info 是文件上传成功后，服务端返回的json，形式如
                    // {
                    //    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                    //    "key": "gogopher.jpg"
                    //  }
                    // 参考http://developer.qiniu.com/docs/v6/api/overview/up/response/simple-response.html
                    var domain = up.getOption('domain');
                    var res = $.parseJSON(info);
                    var sourceLink = domain + res.key; //获取上传成功后的文件的Url
                    elementImg.attr("src", sourceLink);
                    elementInput.val(sourceLink);
                    $.loader(false);
                },
                'Error': function(up, err, errTip) {
                    //上传出错时,处理相关的事情
                    $.loader(false);
                    BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: '图片上传失败',
                        message: errTip,
                        buttons: [{
                            label: '关闭',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }]
                    });
                },
                'UploadComplete': function() {
                    //队列文件处理完毕后,处理相关的事情
                }
            }
        });

        return;
    };
});