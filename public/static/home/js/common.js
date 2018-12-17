(function($){
$.fn.priceFont = function(cls){
	return this.each(function(){
		var _this = $(this), text = _this.html();
		if(!text.length || (text.indexOf('￥')==-1 && text.indexOf('.')==-1))return true;
		if(text.indexOf('￥')>-1){
			var ar = text.split('￥'), prefix = ar[0]+'￥', arr = ar[1].split('.'), integer = arr[0], decimal = '';
			if(arr.length==2)decimal = '.'+arr[1];
			text = prefix+'<font class="'+cls+'">'+integer+'</font>'+decimal;
		}else{
			var arr = text.split('.'), integer = arr[0], decimal = '';
			if(arr.length==2)decimal = '.'+arr[1];
			text = '<font class="'+cls+'">'+integer+'</font>'+decimal;
		};
		_this.html(text);
	});
};
$.fn.checklogin = function(){
	return this.click(function(){return $.checklogin($(this).attr('href'))});
};
$.fn.checkHistoryBack = function(){
	return this.each(function(){
		var _this = $(this);
		if(!_this.length || _this.attr('href').indexOf('history.back')==-1)return true;
		_this.click(function(){
			var referer = $.referer();
			if(!referer.length || referer.indexOf('?app=passport')>-1){
				location.href = '/wap.php';
				return false;
			}
		});
	});
};
$.extend({
	checklogin : function(url){
		if($('#member_id').length && (!$('#member_id').val().length || $('#member_id').val()*1==0)){
			location.href = '/wap.php?tpl=login&url=' + (typeof(url)=='undefined' ? location.href.urlencode() : url.urlencode());
			return false;
		}
		return true;
	}
});
})(jQuery);

//绑定faceView
function bindFaceView(faceView, scrollEle){
	if(typeof(faceView)=='undefined' || faceView==null || faceView=='')faceView = '.faceView:last';
	faceView = $(faceView);
	setTimeout(function(){faceView.addClass('faceView-x')}, 10);
	setTitleView();
	var height = $.window().height;
	faceView.find('.navBar .left').click(function(){removeFaceView(faceView);return false});
	if(faceView.outerHeight(true)<height)faceView.height(height);
	if(typeof(scrollEle)!='undefined')scrollFaceView(scrollEle);
	return faceView;
}
//移除faceView
function removeFaceView(faceView){
	if(typeof(faceView)=='undefined' || faceView==null || faceView=='')faceView = '.faceView:last';
	faceView = $(faceView);
	faceView.removeClass('faceView-x');
	setTimeout(function(){faceView.remove()}, 400);
}
//移除所有faceView
function removeAllFaceView(){
	$('.faceView').each(function(){
		var faceView = $(this).removeClass('faceView-x');
		setTimeout(function(){faceView.remove()}, 400);
	});
}
//设置faceView内部指定容器可滚动
function scrollFaceView(ele){
	ele = $(ele), padding = ele.padding();
	ele.addClass('overflow-scroll').css({overflow:'auto', height:$.window().height-padding.top-padding.bottom});
}

function setAds(lis){
	$(lis).each(function(){
		if(!!$(this).data('setAds'))return true;
		var _this = $(this).data('setAds', true), type = _this.attr('ad_type'), content = _this.attr('ad_content'), pic = _this.attr('pic');
		switch (type) {
			case 'html5':
				_this.html('<a href="'+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'recharge':
				_this.html('<a href="/wap.php?app=recharge&act=jiesuan&id='+content+'" style="background-image:url('+pic+');"></a>');
				_this.find('a').checklogin();
				break;
			case 'type':
			case 'subtype':
				_this.html('<a href="/wap.php?app=goods&act=index&category_id='+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'brand':
				_this.html('<a href="/wap.php?app=goods&act=index&brand_id='+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'country':
				_this.html('<a href="/wap.php?app=goods&act=index&country_id='+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'goods':
				_this.html('<a href="/wap.php?app=goods&act=detail&goods_id='+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'shop':
				_this.html('<a href="/wap.php?app=shop&act=detail&shop_id='+content+'" style="background-image:url('+pic+');"></a>');
				break;
			case 'coupon':
				_this.html('<a href="javascript:void(0)" mid="'+content+'" style="background-image:url('+pic+');"></a>');
				_this.find('a').click(function(){
					if(!$.checklogin())return false;
					$.getJSON('/api.php?app=coupon&act=ling', {coupon_id:$(this).attr('mid')}, function(json){
						if(json.error!=0){$.overloadError(json.msg);return}
						$.overloadSuccess('优惠券领取成功');
					});
				});
				break;
		}
	}).find('a').loadbackground();
}

function setTitleView(){
	$('.navBar .titleView').each(function(){
		$(this).css('margin-left', -$(this).width()/2);
	});
}

$(function(){
	setTitleView();
	$('.navBar a.left').checkHistoryBack();
	$('form').submit(function(){$.overload(null)});
	if($.browser.wx){
		$('a[weixin]').click(function(){
			$.overlay('', 0, function(){
				$('.position-overlay').css('background', 'none').html('<div class="wx-share"></div>');
			});
			return false;
		});
	}
});