(function($){

$.fn.pjax = function(options){
	options = $.extend({
		target : 'body', //要替换内容的容器
		change : null, //改变pushState时的href,返回string有效
		success : null //容器设置内容后执行
	}, options);
	return this.each(function(){
		var _this = $(this), action = _this.is('form')?'submit':'click', href = '', title = '';
		if(!!_this.attr('data-pjax-skip'))return true;
		if(_this.is('form')){
			href = _this.attr('action');
			title = _this.attr('title');
		}else{
			href = _this.attr('href');
			title = _this.attr('title')||_this.text();
		};
		_this.on(action, function(e){
			$.pjax($.extend(options, {href:href, title:title}));
			e.preventDefault();
			return false;
		});
	});
};

$.extend({
	pjax : function(options){
		options = $.extend({
			href : '', //静态跳转的网址
			title : '', //浏览器标题
			target : 'body', //放置html代码的容器
			cache : 0, //缓存html代码,单位秒
			skipHref : false, //跳过不改变网址
			changeHref : null, //设置跳转网址前执行,修改跳转网址,必须返回字符串才有效,接受一个参数:options.href
			changeHtml : null, //清除html,head,body等标签后执行,修改html代码,必须返回字符串才有效,接受一个参数:获取到的经过清除的html代码
			setHtml : null, //设置html到容器前执行,必须返回字符串才有效,接受一个参数:最终设置的html代码
			success : null, //设置html到容器后执行
			popstate : null //网页后退后执行
		}, options);
		var pjaxSupport = window.history && window.history.pushState && window.history.replaceState &&
			!navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/);
		var href = options.href, title = options.title;
		if(!href.length || href===location.href || !options.target.length || !$(options.target).length)return true;
		if(!pjaxSupport){
			location.href = href;
			return;
		};
		var state = {
			target : options.target,
			html : $(options.target).html(),
			title : document.title,
			bodyclass : $('body').attr('class'),
			bodystyle : $('body').attr('style'),
			url : location.href
		};
		if(options.cache){
			var html = $.cache.cache(href);
			if(html){
				success(html);
				return;
			}
		};
		$.ajax({
			url : href,
			//dataType : 'html',
			cache : false,
			beforeSend : function(xhr){
				$(options.target).trigger('pjax.start', [ xhr, options ]);
				xhr && xhr.setRequestHeader('X-PJAX', true);
			},
			success : function(result){
				if(options.cache)$.cache.cache(href, result, options.cache);
				success(result);
			},
			error : function(){
				location.href = href;
			},
			complete : function(xhr){
				$(options.target).trigger('pjax.end', [ xhr, options ]);
			}
		});
		function success(result){
			if($.isFunction(options.popstate))$(options.target).data('pjax.popstate', options.popstate);
			if($.isJsonString(result)){
				if($.isFunction(options.success))options.success.call($(options.target).data('pjax.success', options.success), $.jsonValue(result));
				return;
			}
			if(!options.skipHref){
				if(!title.length){
					var matches = result.match(/<title>(.*?)<\/title>/i);
					if(matches)title = matches[1];
				};
				if(title.length)document.title = title;
				if($.isFunction(options.changeHref)){
					var newHref = options.changeHref(href);
					if(typeof newHref=='string')href = newHref;
				};
				window.history.replaceState(state, '', '');
				window.history.pushState(state, title, href);
			};
			$('body').removeAttr('class').removeAttr('style');
			if(!!$('body').data('class'))$('body').addClass($('body').data('class'));
			if(/<body/.test(result)){
				var bodyclass = result.match(/<body.*?( class="([^"]+)")?[^>]*>/i), bodystyle = result.match(/<body.*?( style="([^"]+)")?[^>]*>/i);
				if(bodyclass && typeof(bodyclass[2])!='undefined')$('body').addClass(bodyclass[2]);
				if(bodystyle && typeof(bodystyle[2])!='undefined')$('body').attr('style', bodystyle[2]);
			};
			result = result.replace(/<!doctype[^>]*>/ig, '').replace(/<\/?html[^>]*>/ig, '').replace(/<head>[\s\S]+?<\/head>/ig, '')
				.replace(/<\/?body[^>]*>/ig, '').replace(/<title>.*?<\/title>/ig, '');
			if($.isFunction(options.changeHtml)){
				var newResult = options.changeHtml(result);
				if(typeof newResult=='string')result = newResult;
			}
			if(/<script.*?( src="([^"]+)")[^>]*><\/script>/.test(result)){
				var script = result.match(/<script.*?( src="([^"]+)")[^>]*><\/script>/ig), scripts = [];
				$.each(script, function(k, v){
					var s = v.match(/<script.*?( src="([^"]+)")[^>]*><\/script>/i), exist = false;
					$('script[src]').each(function(){
						if(this.src===s[2]){
							exist = true;
							return false;
						}
					});
					result = result.replace(v, '');
					if(exist)return true;
					scripts.push(s[2]);
				});
				getScript(scripts, function(){
					container(result);
				});
			}else{
				container(result);
			}
		}
		function container(html){
			var target = $(options.target);
			target.find('*').remove();
			if($.isFunction(options.setHtml)){
				var newHtml = options.setHtml(html);
				if(typeof newHtml=='string')html = newHtml;
			}
			target.empty().html('').html(html);
			if($.isFunction(options.success))options.success.call(target.data('pjax.success', options.success), html);
		}
		function getScript(scripts, completion){
			$.getScript(scripts[0], function(){
				scripts.shift();
				if(!scripts.length){
					if($.isFunction(completion))completion();
				}else{
					getScript(scripts, completion);
				}
			});
		}
	}
});

window.onpopstate = function(e){
	if(e.state){
		var state = e.state;
		if(state.target && $(state.target).length){
			if(state.title.length)document.title = state.title;
			$('body').removeAttr('class').removeAttr('style');
			if(state.bodyclass)$('body').addClass(state.bodyclass);
			if(state.bodystyle)$('body').attr('style', state.bodystyle);
			var target = $(state.target);
			target.find('*').remove();
			target.empty().html('').html(state.html);
			if($.isFunction(target.data('pjax.success')))target.data('pjax.success').call(target);
			if($.isFunction(target.data('pjax.popstate')))target.data('pjax.popstate').call(target);
			$(document).data('popstate', true);
			setTimeout(function(){$(document).removeData('popstate')}, 1000);
		}
	}
};
	
})(jQuery);