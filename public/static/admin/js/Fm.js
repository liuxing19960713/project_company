//工具类
//点击事件 显示/隐藏
function PToggle(but,dom,time,type) {
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
function PHide(but,dom,time,type) {
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
function PShow(but,dom,time,type) {
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
function GetData(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");  
    //console.log(window.location.search.substr(1));
    var r = window.location.search.substr(1).match(reg); //search,查询？后面的参数，并匹配正则
    //console.log(r);
    if(r != null) {
        return unescape(r[2]);
    }  else {
        return null;  
    }
}


