define(function (){
    return function(contentIdName){

        function printLog(title, info) {
            window.console && console.log(title, info);
        }

        // 初始化七牛上传
        function uploadInit() {
            var editor = this;
            var btnId = editor.customUploadBtnId;
            var containerId = editor.customUploadContainerId;
            // 创建上传对象

            var uploader = Qiniu.uploader({
                runtimes: 'html5,flash,html4',    //上传模式,依次退化
                browse_button: btnId,       //上传选择的点选按钮，**必需**
                uptoken_url: appConfig.publicPath + '/index.php/api/Index/uptoken',  //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
                unique_names: true, // 默认 false，key为文件名。若开启该选项，SDK会为每个文件自动生成key（文件名）
                domain: appConfig.qiniuDomain,  //bucket 域名，下载资源时用到，**必需**
                container: containerId,           //上传区域DOM ID，默认是browser_button的父元素，
                max_file_size: '4mb',           //最大文件体积限制
                flash_swf_url: appConfig.publicPath + '/static/component/plupload/js/Moxie.swf',  //引入flash,相对路径
                filters: {
                    mime_types: [
                        //只允许上传图片文件 （注意，extensions中，逗号后面不要加空格）
                        { title: "图片文件", extensions: "jpg,gif,png,bmp" }
                    ]
                },
                max_retries: 3,                   //上传失败最大重试次数
                dragdrop: true,                   //开启可拖曳上传
                drop_element: 'editor-container',        //拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
                chunk_size: '4mb',                //分块上传时，每片的体积
                auto_start: true,                 //选择文件后自动上传，若关闭需要自己绑定事件触发上传
                init: {
                    'FilesAdded': function(up, files) {
                        plupload.each(files, function(file) {
                            // 文件添加进队列后,处理相关的事情
                        });
                    },
                    'BeforeUpload': function(up, file) {
                        // 每个文件上传前,处理相关的事情
                    },
                    'UploadProgress': function(up, file) {
                        // 显示进度条
                        editor.showUploadProgress(file.percent);
                    },
                    'FileUploaded': function(up, file, info) {
                        var domain = up.getOption('domain');
                        var res = $.parseJSON(info);
                        var sourceLink = domain + res.key; //获取上传成功后的文件的Url
                        // 插入图片到editor
                        editor.command(null, 'insertHtml', '<img src="' + sourceLink + '" style="max-width:100%;"/>')
                    },
                    'Error': function(up, err, errTip) {
                        //上传出错时,处理相关的事情
                        BootstrapDialog.show({
                            type: BootstrapDialog.TYPE_DANGER,
                            title: '图片上传失败',
                            message: errTip,
                        });
                    },
                    'UploadComplete': function() {
                        //队列文件处理完毕后,处理相关的事情
                        // 隐藏进度条
                        editor.hideUploadProgress();
                    }
                }
            });
        }

        wangEditor.config.printLog = false; // 关闭打印log

        var editor = new wangEditor(contentIdName);

        // 关闭地图、表情菜单
        editor.config.menus = $.map(wangEditor.config.menus, function(item, key) {
            if (item === 'location') {
                return null;
            }
            if (item === 'emotion') {
                return null;
            }
            if (item === 'insertcode') {
                return null;
            }
                return item;
            });

        editor.config.printLog = false;
        editor.config.customUpload = true;
        editor.config.customUploadInit = uploadInit;

        editor.create();
    };
});