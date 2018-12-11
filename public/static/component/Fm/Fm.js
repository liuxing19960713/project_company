//工具类
class Tool {
    //点击事件 显示/隐藏
    PToggle(but,dom,time,type) {
        if(type) {
            but.click(function() {
                dom.fadeToggle(time);
            });
        } else {
            but.click(function() {
                dom.toggle(time);
            });
        }
    }   
    //点击事件 隐藏/效果
    PHide(but,dom,time,type) {
        if(type) {
            but.click(function() {
                dom.fadeOut(time);
            });
        } else {
            but.click(function() {
                dom.hide(time);
            });
        }
    }
    //点击事件 显示/效果
    PShow(but,dom,time,type) {
        if(type) {
            but.click(function() {
                dom.fadeIn(time);
            });
        } else {
            but.click(function() {
                dom.show(time);
            });
        }
    }
    //获取指定url参数
    GetData(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");  
        //console.log(window.location.search.substr(1));
        var r = window.location.search.substr(1).match(reg); //search,查询？后面的参数，并匹配正则
        //console.log(r);
        if(r != null) {
            return unescape(r[2]);
        }  else {
            return null;  
        }
    }
    //判断变量是否为空
    Empty(data) {
        return (data == "" || data == undefined || data == null)? true:false;
    }
    //数组类型数据排序方法
    sortByKey(array,key) {
        //参数：arry为目标数组，key为数组中需排序的项
        return array.sort(function(a,b){
            var x = a[key];
            var y = b[key];
            return ((x<y)?-1:((x>y)?1:0));
        });
    }
    //判断移动端设备系统
    //判断是否IOS设备
    CheckIsAppleDevice() {
        var u = navigator.userAgent, app = navigator.appVersion;
        var ios = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        var iPad = u.indexOf('iPad') > -1;
        var iPhone = u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1;
        if (ios || iPad || iPhone) {
            return true;
        } else {
            return false;
        }
    }
    //判断是否Android设备
    CheckIsAndroidDevice() {
        var u = navigator.userAgent;
        if ( u.indexOf('Android') > -1 || u.indexOf('Adr') > -1 ) {
            return true;
        } else {
            return false;
        }
    }
    //配置移动端自适应单位Rem
    Rem() {
        //	设置页面总列数
        var colCount = 20;
        //  动态设置列宽  
        var colWidth =  document.documentElement.clientWidth / colCount;
        //  确定html基本的font-size  
        document.querySelector('html').style.fontSize = colWidth + 'px';  
    }
}
//富文本编辑器类
class FmDitor {
    Initialize(id,config) {
        var T = new Tool;
        $(id).attr('contenteditable',"true");
        if(!T.Empty(config)) {
            //设置宽度、高度
            if(!T.Empty(config['Width'])){$(id).css('width',config['Width'])}
            if(!T.Empty(config['Height'])){$(id).css('height',config['Height'])}
        }
    }
}

// 弹出层类
class Popups {
    // 遮罩层
    PopCover(element) {
        var PopCover = '<div id="PopCover">' + element + '</div>';
        $('body').append(PopCover);
        return PopCover;
    }
    // 选择框
    Confirm(time,value,tab1,tab2,callfun1,callfun2) {
        var T = new Tool;
        var ConfirmTab1,ConfirmTab2;
        (!tab1) ? ConfirmTab1 = "是" : ConfirmTab1 = tab1;
        (!tab2) ? ConfirmTab2 = "否" : ConfirmTab2 = tab2;
        var ConfirmTab = '<button class="PopCoverBtn ConfirmTab1">' + ConfirmTab1 + '</button>' + '<button class="PopCoverBtn ConfirmTab2">' + ConfirmTab2 + '</button>';
        var Confirm = '<div id="Confirm">' + '<i class="fa fa-times D-btn"></i>' + value + ConfirmTab + '</div>';
        var PopCover = this.PopCover(Confirm);
        var PopCoverId = $('#PopCover');
        //显示元素
        PopCoverId.fadeIn(time);
        T.PHide($('.D-btn,.PopCoverBtn'),PopCoverId,time,1);
        //选项点击事件以及清除元素
        $('.D-btn').click(function(){
            setTimeout("$('#PopCover').remove()",500);
        })
        $('.ConfirmTab1').click(function(){
            setTimeout("$('#PopCover').remove()",500);
            if(callfun1){
                callfun1();
            }
        })
        $('.ConfirmTab2').click(function(){
            setTimeout("$('#PopCover').remove()",500);
            if(callfun2){
                callfun2();
            }
        })
    }
    
    //手动清除Dom
    DeletePopups(time) {
        $('#PopCover').fadeOut(time);
        $('#PopCover').remove();
    }

    //图片放大效果
    ImgMag(dom,time) {
        var T = new Tool;
        var ImgUrl = $(dom).attr("src");
        var ImgDom = '<img class="ImgMag2" src="'+ImgUrl+'" alt="图片放大">';
        var PopCover = this.PopCover(ImgDom);
        $('#PopCover').fadeIn(500);
        $('#PopCover').click(function(){
            $('#PopCover').fadeOut(500);
            setTimeout("$('#PopCover').remove()",500);
        })
    }
}


