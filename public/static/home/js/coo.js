/*
Developed by @jsong v9.9.20170308
*/
var ua = navigator.userAgent.toLowerCase();
(function($){

//标签验证
//表单有属于自己的coo.error错误提示字符串(第一个验证不通过的错误提示)
//表单有属于自己的coo.element错误元件(第一个验证不通过的元件)
//元件有属于自己的coo.label错误提示容器
//元件有属于自己的coo.error错误提示字符串
$.fn.coo = function(callback){
	if(!this.length)return;
	var the = this.eq(0), _form = null, objBox = {}, me = false,
	prefix = 'coo', debug = false, lay = false, delay = 0, disBtn = true, dialog = false, noLabel = false, css = null, pos = null, addElements = [], addRegs = [],
	autosave = null, ajaxSubmit = null, meBefore = null, meAfter = null, argBefore = null, argAfter = null, reset = null, before = null, after = null;
	if(!$.isFunction(callback)){
		if($.isPlainObject(callback)){
			if(typeof callback.prefix!='undefined')prefix = callback.prefix; //自定义验证标签前缀
			if(typeof callback.debug!='undefined')debug = callback.debug; //调试模式(不提交表单)
			if(typeof callback.lay!='undefined')lay = callback.lay; //提交后表单区域添加遮罩
			if(typeof callback.delay!='undefined')delay = callback.delay; //延迟提交
			if(typeof callback.disBtn!='undefined')disBtn = callback.disBtn; //提交后禁用提交按钮
			if(typeof callback.dialog!='undefined')dialog = callback.dialog; //所有提示信息使用overloadError
			if(typeof callback.noLabel!='undefined')noLabel = callback.noLabel; //不显示label提示信息
			if(typeof callback.css!='undefined')css = callback.css; //验证不通过添加的提示样式名称
			if(typeof callback.pos!='undefined')pos = callback.pos; //所有提示信息默认位置
			if(typeof callback.addElements!='undefined')addElements = callback.addElements; //扩展需验证的元件(非使用coo前缀类名, 数组长度与addRegs对应)
			if(typeof callback.addRegs!='undefined')addRegs = callback.addRegs; //扩展需验证的元件对应的验证正则(RegExp格式, 数组长度与addElements对应)
			if(typeof callback.autosave!='undefined')autosave = callback.autosave; //自动保存表单控件的数据
			if(typeof callback.ajaxSubmit!='undefined')ajaxSubmit = callback.ajaxSubmit; //使用AJAX提交后执行(设置后表单自动转为AJAX提交)
			if(typeof callback.meBefore!='undefined')meBefore = callback.meBefore; //指定某控件改变值前执行
			if(typeof callback.meAfter!='undefined')meAfter = callback.meAfter; //指定某控件改变值且验证后执行
			if(typeof callback.argBefore!='undefined')argBefore = callback.argBefore; //控件改变值前执行
			if(typeof callback.argAfter!='undefined')argAfter = callback.argAfter; //控件改变值且验证后执行
			if(typeof callback.reset!='undefined')reset = callback.reset; //重置后执行
			if(typeof callback.before!='undefined')before = callback.before; //提交且验证前执行
			if(typeof callback.after!='undefined')after = callback.after; //提交且验证后执行
			if(typeof callback.callback!='undefined')callback = callback.callback; //所有验证通过后执行的回调函数
		}else if(typeof callback=='string' || typeof callback=='undefined'){
			if(!the.is('form'))me = true;
		}
	}
	//field:被验证对象, mk:验证标识, myself:控件直接验证用[true|false], expand:扩展需验证的expr用[true|false]
	function validateField(field, mk, myself, expand){
		var f = $(field), labelID, errpre = '', fail = false, position = '', val = typeof(field)=='string'?field:f.val(), isBox = false, isMultiple = false, result,
		tip = '', tipcss = 'tip', tiparea = [], error = '', errorcss = 'error', errorarea = [], success = '', successcss = 'success', successarea = [],
		objType = f[0].tagName.toLowerCase(), objMark = f.attr('id'), hasID = true;
		if(!!!objMark){objMark = f.attr('name');hasID = false}
		if(objType=='input')objType = f.attr('type');
		if(objType=='select' && f.selected().length)val = f.selected().val();
		if(objType=='checkbox' || objType=='radio'){objMark = f.attr('name');isBox = true}
		if((objType=='checkbox' || objType=='radio') && !!!objMark){objMark = f.attr('id')}
		if(objType=='select' && f.attr('multiple'))isMultiple = true;
		if(!!!objMark)objMark = objType + '_' + objMark;
		if(typeof(mk)=='string')mk = [mk];
		if(val==null)val = '';
		val = val+'';
		labelID = 'coo_' + objMark.replace(/\[/,'\\[').replace(/\]/,'\\]');
		for(var i=0; i<mk.length; i++){
			if(isBox && !!f.data('box.break'))break;
			var meBeforeData = f.attr('meBefore')||meBefore;
			if(!!meBeforeData && typeof(meBeforeData)=='string'){
				var func;
				eval('func = ' + meBeforeData);
				meBeforeData = func;
			}
			if($.isPlainObject(meBeforeData)){
				if(f.is(meBeforeData.el) && $.isFunction(meBeforeData.fn)){
					meBeforeData.fn.call(f);
				}
			};
			var argBeforeData = f.attr('argBefore')||argBefore;
			if(!!argBeforeData && typeof(argBeforeData)=='string'){
				var func;
				eval('func = ' + argBeforeData);
				argBeforeData = func;
			}
			if($.isFunction(argBeforeData)){
				argBeforeData.call(f);
			};
			var tpFlag = mk[i];
			if(tpFlag.charAt(0) == '!'){
				if(!val.length){
					fail = false;
					if(!!f.attr('error-area'))errorarea = $(f.attr('error-area'));
					if(errorarea.length){
						errorarea.html(errorarea.data('html'));
					};
					$('#' + labelID).remove();
					continue;
				};
				tpFlag = tpFlag.substring(1);
			};
			if(expand){
				if(!isBox){
					if(!new RegExp(tpFlag).test(val)){
						fail = true;
						error = '此项必填';
						if(objType=='file')error = '请选择文件';
						if(objType=='select')error = '请选择';
					}else{
						if(objType=='select'){
							var length = 1, min = false, max = false;
							if(!!f.attr('need')){length = f.attr('need');min = true}
							if(!!f.attr('min')){length = f.attr('min');min = true}
							if(!!f.attr('max')){length = f.attr('max');max = true}
							if(min){
								if(f.selected().length<length){
									fail = true;
									error = '最少选' + length + '项';
								}
							}
							if(max){
								if(f.selected().length>length){
									fail = true;
									error = '最多选' + length + '项';
								}
							}
						}
					}
				}else{
					var box = [];
					if(!!f.attr('name'))box = $('input[name="'+f.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
					if(!box.length && !!f.attr('id'))box = $('input[id="'+f.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
					box.each(function(){
						if(!!!$(this).data('click')){
							$(this).data('click', true);
							$(this).onclick(function(){
								$(_form).removeData('formstatus').removeData('submitting');
								box.removeData('box.break');
								validateField(this, mk, false, true);
							});
						}
						if(!!f.attr('need') && !!!f.data('coo.min.length'))box.data('coo.min.length', f.attr('need'));
						if(!!f.attr('min') && !!!f.data('coo.min.length'))box.data('coo.min.length', f.attr('min'));
						if(!!f.attr('max') && !!!f.data('coo.max.length'))box.data('coo.max.length', f.attr('max'));
						if(!!f.attr('need') || !!f.attr('min'))box.data('coo.min', true);
						if(!!f.attr('max'))box.data('coo.max', true);
					});
					var length = 1, min = false, max = false;
					if(!!f.data('coo.min'))min = f.data('coo.min');
					if(!!f.data('coo.max'))max = f.data('coo.max');
					if(!!!f.attr('coo-min') && !!!f.data('coo.max'))min = true;
					if(min){
						if(!!f.data('coo.min.length'))length = f.data('coo.min.length');
						if(box.filter(':checked').length<length){
							box.data('box.break', true);
							fail = true;
							error = '最少选' + length + '项';
						}else{
							box.removeData('box.break');
						}
					}
					if(max){
						if(!!f.data('coo.max.length'))length = f.data('coo.max.length');
						if(box.filter(':checked').length>length){
							box.data('box.break', true);
							fail = true;
							error = '最多选' + length + '项';
						}else{
							box.removeData('box.break');
						}
					}
				}
			}else{
				switch(tpFlag){
					case 'need':
						if(!isBox){
							f.data('coo-need', true);
							if(val.replace(/[\s|\n|\r]+/g, '') == ''){
								fail = true;
								error = '此项必填';
								if(objType=='file')error = '请选择文件';
								if(objType=='select')error = '请选择';
							}else{
								if(objType=='select'){
									var length = 1, min = false, max = false;
									if(!!f.attr('need')){length = f.attr('need');min = true}
									if(!!f.attr('min')){length = f.attr('min');min = true}
									if(!!f.attr('max')){length = f.attr('max');max = true}
									if(min){
										if(f.selected().length<length){
											fail = true;
											error = '最少选' + length + '项';
										}
									}
									if(max){
										if(f.selected().length>length){
											fail = true;
											error = '最多选' + length + '项';
										}
									}
								}
							}
						}else{
							var box = [];
							if(!!f.attr('name'))box = $('input[name="'+f.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
							if(!box.length && !!f.attr('id'))box = $('input[id="'+f.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
							box.data('coo-need', true).each(function(){
								if(!!!$(this).data('click')){
									$(this).data('click', true);
									$(this).onclick(function(){
										$(_form).removeData('formstatus').removeData('submitting');
										box.removeData('box.break');
										validateField(this, mk);
									});
								}
								if(!!f.attr('need') && !!!f.data('coo.min.length'))box.data('coo.min.length', f.attr('need'));
								if(!!f.attr('min') && !!!f.data('coo.min.length'))box.data('coo.min.length', f.attr('min'));
								if(!!f.attr('max') && !!!f.data('coo.max.length'))box.data('coo.max.length', f.attr('max'));
								if(!!f.attr('need') || !!f.attr('min'))box.data('coo.min', true);
								if(!!f.attr('max'))box.data('coo.max', true);
							});
							var length = 1, min = false, max = false;
							if(!!f.data('coo.min'))min = f.data('coo.min');
							if(!!f.data('coo.max'))max = f.data('coo.max');
							if(!!!f.attr('coo-min') && !!!f.data('coo.max'))min = true;
							if(min){
								if(!!f.data('coo.min.length'))length = f.data('coo.min.length');
								if(box.filter(':checked').length<length){
									box.data('box.break', true);
									fail = true;
									error = '最少选' + length + '项';
								}else{
									box.removeData('box.break');
								}
							}
							if(max){
								if(!!f.data('coo.max.length'))length = f.data('coo.max.length');
								if(box.filter(':checked').length>length){
									box.data('box.break', true);
									fail = true;
									error = '最多选' + length + '项';
								}else{
									box.removeData('box.break');
								}
							}
						}
						break;
					case 'username':
						f.data('coo-username', true);
						if(!/^[a-zA-Z]\w{5,15}$/.test(val)){
							fail = true;
							error = '字母开头,6-16位,只能字母、数字与下划线';
						}
						break;
					case 'usernamecn':
						f.data('coo-usernamecn', true);
						if(!/^([\u4e00-\u9fa5]|\w){3,15}$/.test(val)){
							fail = true;
							error = '3-15位包括A-Z、a-z、汉字、不含特殊字符';
						}
						break;
					case 'password':
						f.data('coo-password', true);
						if(!/^\S{6,16}$/.test(val)){
							fail = true;
							error = '6-16位,不含空格,区分大小写';
						}
						break;
					case 'num':
					case 'number':
						f.data('coo-num', true);
						if(!/^(-?[1-9]\d*|0)$/.test(val)){
							fail = true;
							error = '请输入数字';
						}
						break;
					case 'char':
						f.data('coo-char', true);
						if(!/^[a-zA-Z]+$/.test(val)){
							fail = true;
							error = '请输入字母';
						}
						break;
					case 'en':
					case 'english':
						f.data('coo-en', true);
						if(!/^[\w\-]+$/.test(val)){
							fail = true;
							error = '只能字母、数字与下划线';
						}
						break;
					case 'cn':
					case 'chinese':
						f.data('coo-cn', true);
						if(!/^[\u4e00-\u9fa5]+$/.test(val)){
							fail = true;
							error = '请输入中文';
						}
						break;
					case 'double':
						f.data('coo-double', true);
						if(!/^[^\x00-\xff]+$/.test(val)){
							fail = true;
							error = '只能中文、全角字符';
						}
						break;
					case 'money':
						f.data('coo-money', true);
						if(!/^\d+(\.\d{1,2})?$/.test(val)){
							fail = true;
							error = '请输入正确金额';
						}
						break;
					case 'idcard':
						f.data('coo-idcard', true);
						if(!/(^\d{15}$)|(^\d{17}([0-9]|x|X)$)/.test(val)){
							fail = true;
							error = '请输入身份证号';
						}
						break;
					case 'idcardhard':
						f.data('coo-idcardhard', true);
						var Wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1], //加权因子
							ValideCode = [1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2]; //身份证验证位值,10代表X
						function idCardValidate(idCard){
							if(idCard.length == 15){
								return is15IdCard(idCard); //进行15位身份证的验证
							}else if(idCard.length == 18){
								return is18IdCard(idCard) && isTrue18IdCard(idCard.split('')); //进行18位身份证的基本验证和第18位的验证
							}else{
								return false;
							}
						}
						function isTrue18IdCard(idCard){
							var sum = 0;
							if(idCard[17].toLowerCase() == 'x')idCard[17] = 10; //将最后位为x的验证码替换为10方便后续操作
							for(var i=0; i<17; i++)sum += Wi[i] * idCard[i]; //加权求和
							var valCodePosition = sum % 11; //得到验证码所位置
							if(idCard[17] == ValideCode[valCodePosition]){
								return true;
							}else{
								return false;
							}
						}
						function is18IdCard(idCard){
							var year =  idCard.substring(6, 10), month = idCard.substring(10, 12), day = idCard.substring(12, 14),
								date = new Date(year, parseInt(month)-1, parseInt(day));
							if(date.getFullYear()!=parseInt(year) || date.getMonth()!=parseInt(month)-1 || date.getDate()!=parseInt(day)){
								return false;
							}else{
								return true;
							}
						}
						function is15IdCard(idCard){
							var year =  idCard.substring(6, 8), month = idCard.substring(8, 10), day = idCard.substring(10, 12),
								date = new Date(year, parseInt(month)-1, parseInt(day));
							if(date.getYear()!=parseInt(year) || date.getMonth()!=parseInt(month)-1 || date.getDate()!=parseInt(day)){
								return false;
							}else{
								return true;
							}
						}
						if(!idCardValidate(val)){
							fail = true;
							error = '请输入身份证号';
						}
						break;
					case 'zip':
						f.data('coo-zip', true);
						if(!/^[1-9]\d{5}(?!\d)$/.test(val)){
							fail = true;
							error = '请输入邮编';
						}
						break;
					case 'tel':
						f.data('coo-tel', true);
						if(!/^((\d{3,4}-)?\d{8}(-\d+)?|(\(\d{3,4}\))?\d{8}(-\d+)?)$/.test(val)){
							fail = true;
							error = '请输入固话号码';
						}
						break;
					case 'telac':
						f.data('coo-telac', true);
						if(!/^(\d{3,4}-\d{8}(-\d+)?|\(\d{3,4}\)\d{8}(-\d+)?)$/.test(val)){
							fail = true;
							error = '请输入固话号码(包括区号)';
						}
						break;
					case 'mobile':
						f.data('coo-mobile', true);
						if(!/^(\+?86)?1[3-8]\d{9}$/.test(val)){
							fail = true;
							error = '请输入手机号码';
						}
						break;
					case 'phone':
						f.data('coo-phone', true);
						if(!/^((\+?86)?1[3-8]\d{9}|(\d{3,4}-)?\d{8}(-\d+)?|(\(\d{3,4}\))?\d{8}(-\d+)?)$/.test(val)){
							fail = true;
							error = '请输入联系电话';
						}
						break;
					case 'phoneac':
						f.data('coo-phoneac', true);
						if(!/^((\+?86)?1[3-8]\d{9}|\d{3,4}-\d{8}(-\d+)?|\(\d{3,4}\)\d{8}(-\d+)?)$/.test(val)){
							fail = true;
							error = '请输入联系电话(固话需区号)';
						}
						break;
					case 'email':
						f.data('coo-email', true);
						if(!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(val)){
							fail = true;
							error = '请输入正确邮箱';
						}
						break;
					case 'url':
						f.data('coo-url', true);
						if(!/^((http|https|ftp):\/\/)?[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/.test(val)){
							fail = true;
							error = '请输入正确网址';
						}
						break;
					case 'qq':
						f.data('coo-qq', true);
						if(!/^[1-9][0-9]{4,}$/.test(val)){
							fail = true;
							error = '请输入正确QQ';
						}
						break;
					case 'ip':
						f.data('coo-ip', true);
						if(!/^((([1-9]\d?)|(1\d{2})|(2[0-4]\d)|(25[0-5]))\.){3}(([1-9]\d?)|(1\d{2})|(2[0-4]\d)|(25[0-5]))$/.test(val)){
							fail = true;
							error = '请输入正确IP';
						}
						break;
					case 'reg':
						f.data('coo-reg', true);
						var reg = f.attr('reg');
						if(!!!reg)continue;
						if(!new RegExp(reg).test(val)){
							fail = true;
							error = '格式错误';
						}
						break;
					case 'color':
						f.data('coo-color', true);
						if(!f.data('color')){
							f.data('color', true);
							var options = {}, opt = $(this).attr('options');
							eval("options = "+opt);
							if($.isPlainObject(options))f.colorpicker(options);
						}
						if(!/^#[a-fA-F0-9]{6}$/.test(val)){
							fail = true;
							error = '请选择颜色';
						}
						break;
					case 'date':
						f.data('coo-date', true);
						var re = /^(?:(?!0000)[0-9]{4}[\/-](?:(?:0?[1-9]|1[0-2])[\/-](?:0?[1-9]|1[0-9]|2[0-8])|(?:0?[13-9]|1[0-2])[\/-](?:29|30)|(?:0?[13578]|1[02])[\/-]31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)[\/-]0?2[\/-]29)$/;
						//http://www.cnblogs.com/love2wllw/archive/2010/05/30/1747534.html
						if(f.data('options.just')){
							re = f.data('options.just');
						}
						if(!f.data('date')){
							f.data('date', true);
							var options = {}, opt = f.attr('options');
							if(opt)eval("options = "+opt);
							if(!!f.attr('just') && (f.attr('just')=='year' || f.attr('just')=='month'))options = $.extend(options, {just:f.attr('just')});
							options = $.extend({
								callback : function(){
									if(validateField(field, mk)){
										for(var i=0; i<mk.length; i++){
											if(mk[i].charAt(0)=='@'){
												var tm = mk[i];
												tm = tm.substring(1);
												var sinbo = $(tm, _form);
												if(!sinbo.length)return;
												sinbo.simclick();
												break;
											}
										}
									}
								}
							}, options);
							var format = 'yyyy-m-d';
							if(options.just=='year'){
								format = 'yyyy';
								re = /^(19|20)\d{2}$/;
							}else if(options.just=='month'){
								format = 'yyyy-m';
								re = /^(19|20)\d{2}-(((0?[1-9])|10|11|12)|([1-9]|10|11|12))$/;
							};
							options = $.extend(options, {format:format});
							f.datepicker(options);
						}
						if(!re.test(val)){
							fail = true;
							error = '请选择日期';
						}
						break;
					default:
						var mark = '';
						if(tpFlag.substring(0,4)=='need' || tpFlag.substring(0,3)=='min' || tpFlag.substring(0,3)=='max'){
							var min = false, max = false;
							if(tpFlag.substring(0,4)=='need' || tpFlag.substring(0,3)=='min')min = true;
							if(tpFlag.substring(0,3)=='max')max = true;
							mark = tpFlag.substring(3);
							if(tpFlag.substring(0,4)=='need')mark = tpFlag.substring(4);
							if(/^\d+$/.test(mark)){
								if(!isBox){
									if(objType=='select'){
										if(min){
											if(f.selected().length<mark){
												fail = true;
												error = '最少选' + mark + '项';
											}
										}
										if(max){
											if(f.selected().length>mark){
												fail = true;
												error = '最多选' + mark + '项';
											}
										}
									}
								}else{
									var box = [];
									if(!!f.attr('name'))box = $('input[name="'+f.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
									if(!box.length && !!f.attr('id'))box = $('input[id="'+f.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
									box.each(function(){
										var b = $(this);
										if(!!!b.data('click')){
											b.data('click', true);
											b.onclick(function(){
												$(_form).removeData('formstatus').removeData('submitting');
												box.removeData('box.break');
												validateField(this, mk);
											});
										}
										if(min && !!!b.data('coo.min.length'))box.data('coo.min.length', mark);
										if(max && !!!b.data('coo.max.length'))box.data('coo.max.length', mark);
									});
									if(min){
										mark = f.data('coo.min.length');
										if(box.filter(':checked').length<mark){
											box.data('box.break', true);
											fail = true;
											error = '最少选' + mark + '项';
										}else{
											box.removeData('box.break');
										}
									}
									if(max){
										mark = f.data('coo.max.length');
										if(box.filter(':checked').length>mark){
											box.data('box.break', true);
											fail = true;
											error = '最多选' + mark + '项';
										}else{
											box.removeData('box.break');
										}
									}
								}
							}
							break;
						}
						if(tpFlag.substring(0,5)=='float'){
							mark = tpFlag.substring(5);
							if(mark=='' || /^\d+$/.test(mark)){
								if(mark=='')mark = '2';
								if(!new RegExp('^((-?(([1-9]\\d*(\\.\\d{1,'+mark+'})?)|(0\\.\\d{1,'+mark+'})))|0)$').test(val) || /^-?0+(\.0+)$/.test(val)){
									fail = true;
									error = '请输入数字(最多' + mark + '位小数)';
								}
							}
							break;
						};
						var mc = tpFlag.charAt(0);
						mark = tpFlag.substring(1);
						if($.inArray(tpFlag.substr(0, 2), ['<=', '>='])>-1){
							mc = tpFlag.substr(0, 2);
							mark = tpFlag.substring(2);
						};
						switch(mc){
							case '=':
								if(mark=='')continue;
								if(!/^-?\d+(\.\d+)?$/.test(mark)){ //两个值必须相等
									var sinbo = $(mark, _form), sinboMark = sinbo.attr('id')||sinbo.attr('name');
									if(!sinbo.length)continue;
									if(sinboMark=='' || sinboMark==objMark)continue;
									if(!!!sinbo.data(objMark)){
										sinbo.data(objMark, true);
										sinbo.on('keyup blur', function(){
											validateField(field, mk);
										});
									}
									if(val != sinbo.val()){
										fail = true;
										error = '两项不一致';
									}
								}else{ //必须等于n
									if(val == '' || !/^-?\d+(\.\d+)?$/.test(val) || val*1!=mark*1){
										fail = true;
										error = '必须等于' + mark;
									}
								}
								break;
							case '%':
								if(mark=='')continue;
								if(!/^-?\d+$/.test(mark)){ //二选一
									var sinbo = $(mark, _form);
									if(!sinbo.length)continue;
									if(!!!sinbo.data(objMark)){
										sinbo.data(objMark, true);
										sinbo.on('keyup blur', function(){
											validateField(field, mk);
										});
									}
									if(val == '' && sinbo.val() == ''){
										fail = true;
										error = '至少填写一项';
									}
								}else{ //必须为n的整倍数
									if(val == '' || !/^-?\d+$/.test(val) || (val*1 % mark*1)!=0){
										fail = true;
										error = '必须为' + mark + '的整倍数';
									}
								}
								break;
							case '^': //其中之一填写后另一个也必须填写
								if(mark=='')continue;
								var sinbo = $(mark, _form);
								if(!sinbo.length)continue;
								if(!!!sinbo.data(objMark)){
									sinbo.data(objMark, true);
									sinbo.on('keyup blur', function(){
										validateField(field, mk);
									});
								}
								if((val!='' && sinbo.val()=='') || (val=='' && sinbo.val()!='')){
									fail = true;
									error = '两项都必须填写';
								}
								break;
							case '>': //大于
							case '>=': //大于或等于
								if(mark=='')continue;
								if(!/^-?\d+(\.\d+)?$/.test(mark)){
									var sinbo = $(mark, _form);
									if(!sinbo.length)continue;
									if(!!f.data('coo-date')){
										var tims = sinbo.val().split('-'), timv = val.split('-');
										if(val=='' || sinbo.val()=='' || tims.length!=timv.length || sinbo.val().time()<val.time()){
											fail = true;
											error = '日期不能比' + (f.attr('#')||mark) + '早';
										}
									}else{
										if(!!!sinbo.data(objMark)){
											sinbo.data(objMark, true);
											sinbo.on('keyup blur', function(){
												validateField(field, mk);
											});
										}
										if(val=='' || sinbo.val()=='' || isNaN(val) || isNaN(sinbo.val())){
											fail = true;
											error = '数值为空';
										}else if(mc=='>' && val*1<=sinbo.val()*1){
											fail = true;
											error = '必须大于' + (f.attr('#')||mark) + '的值';
										}else if(mc=='>=' && val*1<sinbo.val()*1){
											fail = true;
											error = '必须大于或等于' + (f.attr('#')||mark) + '的值';
										}
									}
								}else{
									if(val=='' || isNaN(val) || !/^-?\d+(\.\d+)?$/.test(val)){
										fail = true;
										error = '数值为空';
									}else if(mc=='>' && val*1<=mark*1){
										fail = true;
										error = '必须大于' + mark;
									}else if(mc=='>=' && val*1<mark*1){
										fail = true;
										error = '必须大于或等于' + mark;
									}
								}
								break;
							case '<': //小于
							case '<=': //小于或等于
								if(mark=='')continue;
								if(!/^-?\d+(\.\d+)?$/.test(mark)){
									var sinbo = $(mark, _form);
									if(!sinbo.length)continue;
									if(!!f.data('coo-date')){
										var tims = sinbo.val().split('-'), timv = val.split('-');
										if(val=='' || sinbo.val()=='' || tims.length!=timv.length || sinbo.val().time()>val.time()){
											fail = true;
											error = '日期不能比' + (f.attr('#')||mark) + '晚';
										}
									}else{
										if(!!!sinbo.data(objMark)){
											sinbo.data(objMark, true);
											sinbo.on('keyup blur', function(){
												validateField(field, mk);
											});
										}
										if(val=='' || sinbo.val()=='' || isNaN(val) || isNaN(sinbo.val())){
											fail = true;
											error = '数值为空';
										}else if(mc=='<' && val*1>=sinbo.val()*1){
											fail = true;
											error = '必须小于' + (f.attr('#')||mark) + '的值';
										}else if(mc=='<=' && val*1>sinbo.val()*1){
											fail = true;
											error = '必须小于或等于' + (f.attr('#')||mark) + '的值';
										}
									}
								}else{
									if(val=='' || isNaN(val) || !/^-?\d+(\.\d+)?$/.test(val)){
										fail = true;
										error = '数值为空';
									}else if(mc=='<' && val*1>=mark*1){
										fail = true;
										error = '必须小于' + mark;
									}else if(mc=='<=' && val*1>mark*1){
										fail = true;
										error = '必须小于或等于' + mark;
									}
								}
								break;
							case 'l': //长度必须等于n位
								if(isNaN(mark))continue;
								if(val.lengths() != mark*1){
									fail = true;
									error = '必须填写' + mark + '个字';
								}
								break;
							case 'm': //长度必须少于或等于n位
								if(isNaN(mark))continue;
								if(val.lengths() > mark*1){
									fail = true;
									error = '必须少于或等于' + mark + '个字';
								}
								break;
							case 'n': //长度必须多于或等于n位
								if(isNaN(mark))continue;
								if(val.lengths() < mark*1){
									fail = true;
									error = '必须多于或等于' + mark + '个字';
								}
								break;
							case 'e': //允许选择的文件类型(支持正则)(一般file用)
								if(mark=='')continue;
								if(!new RegExp('^.+\\.('+mark+')$', 'i').test(val)){
									fail = true;
									error = '只可选择 ' + mark.replace(/\|/g, ', ') + ' 格式';
								}
								break;
						}
						break;
				}
			};
			var meAfterData = f.attr('meAfter') || meAfter;
			if(!!meAfterData && typeof(meAfterData)=='string'){
				var func;
				eval('func = ' + meAfterData);
				meAfterData = func;
			}
			if($.isPlainObject(meAfterData)){
				if(f.is(meAfterData.el) && $.isFunction(meAfterData.fn)){
					result = meAfterData.fn.call(f, fail);
					if(typeof(result) == 'boolean' && !result){
						fail = true;
						error = meAfterData.tips || f.attr('tips') || '格式错误';
					}else{
						fail = false;
						error = '';
					}
				}
			};
			var argAfterData = f.attr('argAfter') || argAfter;
			if(!!argAfterData && typeof(argAfterData)=='string'){
				var func;
				eval('func = ' + argAfterData);
				argAfterData = func;
			}
			if($.isFunction(argAfterData)){
				result = argAfterData.call(f, fail);
				if(typeof(result) == 'boolean' && !result){
					fail = true;
					error = f.attr('tips') || '格式错误';
				}else{
					fail = false;
					error = '';
				}
			};
			errpre = tpFlag+'-';
			if(fail){
				if(_form && !!!$(_form).data('coo.error'))$(_form).data({'coo.error':error, 'coo.element':f});
				f.removeData('success').data('coo.error', error).parents('body').find('#' + labelID).remove();
				if(!!f.attr('tip-area'))tiparea = f.parents('body').find(f.attr('tip-area'));
				if(tiparea.length){
					if(tiparea.data('html'))tiparea.html(tiparea.data('html'));
					else tiparea.html('');
				}
				if(!!f.attr('success-area'))successarea = f.parents('body').find(f.attr('success-area'));
				if(successarea.length){
					if(successarea.data('html'))successarea.html(successarea.data('html'));
					else successarea.html('');
				}
				if(myself){f.focus();return !fail}
				var c = css;
				if(!!f.attr('css'))c = f.attr('css');
				if(c)f.addClass(c);
				if(!!!f.attr('noLabel') && !noLabel){
					if(!!f.attr('error'))error = f.attr('error');
					if(!!f.attr('error-css'))errorcss = f.attr('error-css');
					if(!!f.attr('error-area'))errorarea = f.parents('body').find(f.attr('error-area'));
					if(!!f.attr(errpre+'error'))error = f.attr(errpre+'error');
					if(!!f.attr(errpre+'error-css'))errorcss = f.attr(errpre+'error-css');
					if(!!f.attr(errpre+'error-area'))errorarea = f.parents('body').find(f.attr(errpre+'error-area'));
					if(pos)position = pos;
					if(_form && !!$(_form).attr('pos'))position = $(_form).attr('pos');
					if(!!f.attr('pos'))position = f.attr('pos');
					var htmlFor = hasID ? 'for="'+f.attr('id')+'"' : '',
					poscls = (!!position && !/^(\-?\d+) (\-?\d+)$/.test(position)) ? position+'label' : 'rightlabel',
					labelhtml = '<label id="' + labelID.replace(/\\\[/,'\[').replace(/\\\]/,'\]') + '" class="' + errorcss + ' ' + poscls + ' '+prefix+'label ' + (objMark+'_label') + '" ' + htmlFor + '><span>' + error + '</span></label>',
					label = $(labelhtml);
					if(dialog){
						$.overloadError(error);
					}else{
						if(errorarea.length){
							if(!!!errorarea.data('html'))errorarea.data('html',errorarea.html());
							errorarea.html(labelhtml);
						}else{
							if(position==''){
								if(isBox){
									f.parent().append(label);
								}else{
									f.after(label);
								}
							}else{
								setPosition(position, f, label);
							}
						}
					};
					f.data('coo.label', label);
				}
				break;
			}else{
				f.removeData('success').removeData('coo.error');
				if(myself)return !fail;
				f.data('success', true);
				f.parents('body').find('#' + labelID).remove();
				var fn = f.attr('fn'), c = css;
				if(!!f.attr('css'))c = f.attr('css');
				if(c)f.removeClass(c);
				if(!!fn){
					var func;
					eval("func = "+fn);
					if($.isFunction(func))func(f[0]);
				}
				if(!!f.attr('tip-area'))tiparea = f.parents('body').find(f.attr('tip-area'));
				if(tiparea.length){
					if(tiparea.data('html'))tiparea.html(tiparea.data('html'));
					else tiparea.html('');
				}
				if(!!f.attr('error-area'))errorarea = f.parents('body').find(f.attr('error-area'));
				if(!!f.attr(errpre+'error-area'))errorarea = f.parents('body').find(f.attr(errpre+'error-area'));
				if(errorarea.length){
					if(errorarea.data('html'))errorarea.html(errorarea.data('html'));
					else errorarea.html('');
				}
				if(!!f.attr('success')){
					if(!!!f.attr('noLabel') && !noLabel){
						success = f.attr('success');
						if(!!f.attr('success-css'))successcss = f.attr('success-css');
						if(!!f.attr('success-area'))successarea = f.parents('body').find(f.attr('success-area'));
						if(pos)position = pos;
						if(_form && !!$(_form).attr('pos'))position = $(_form).attr('pos');
						if(!!f.attr('pos'))position = f.attr('pos');
						var htmlFor = hasID ? 'for="'+(isBox?$('[name="'+f.attr('name')+'"]').attr('id'):f.attr('id'))+'"' : '',
						poscls = (!!position && !/^(\-?\d+) (\-?\d+)$/.test(position)) ? position+'label' : 'rightlabel',
						labelhtml = '<label id="' + labelID.replace(/\\\[/,'\[').replace(/\\\]/,'\]') + '" class="' + successcss + ' ' + poscls + ' '+prefix+'label ' + (objMark+'_label') + '" ' + htmlFor + '><span>' + success + '</span></label>',
						label = $(labelhtml);
						if(successarea.length){
							if(!!!successarea.data('html'))successarea.data('html',successarea.html());
							successarea.html(labelhtml);
						}else{
							if(position==''){
								if(isBox){
									f.parent().append(label);
								}else{
									f.after(label);
								}
							}else{
								setPosition(position, f, label);
							}
						}
					}
				};
				f.removeData('coo.label');
			}
		};
		return !fail;
	}
	function setPosition(position, f, label){
		switch(position){
			case 'self':
				f.before(label.hide());
				var left = f.position().left, top = f.position().top;
				if(f.is(':hidden')){
					if(f.is('input:hidden')){
						var prev = f.prev() || f.parent();
						left = prev.position().left;
						top = prev.position().top;
					}else{
						f.css('display', 'inherit');
						left = f.position().left;
						top = f.position().top;
						f.hide();
					}
				};
				label.show().css({
					position : 'absolute',
					'z-index' : '555',
					left : left + 'px',
					top : top + 'px'
				});
				break;
			case 'top':
				f.before(label.hide());
				var left = f.position().left, top = f.position().top - label.outerHeight(false);
				if(f.is(':hidden')){
					if(f.is('input:hidden')){
						var prev = f.prev() || f.parent();
						left = prev.position().left;
						top = prev.position().top - label.outerHeight(false);
					}else{
						f.css('display', 'inherit');
						left = f.position().left;
						top = f.position().top - label.outerHeight(false);
						f.hide();
					}
				};
				label.show().css({
					position : 'absolute',
					'z-index' : '555',
					left : left + 'px',
					top : top + 'px'
				});
				break;
			case 'bottom':
				f.before(label.hide());
				var left = f.position().left, top = f.position().top + f.outerHeight(false);
				if(f.is(':hidden')){
					if(f.is('input:hidden')){
						var prev = f.prev() || f.parent();
						left = prev.position().left;
						top = prev.position().top + prev.outerHeight(false);
					}else{
						f.css('display', 'inherit');
						left = f.position().left;
						top = f.position().top + f.outerHeight(false);
						f.hide();
					}
				};
				label.show().css({
					position : 'absolute',
					'z-index' : '555',
					left : left + 'px',
					top : top + 'px'
				});
				break;
			case 'left':
				f.before(label);
				break;
			case 'right':
				f.after(label);
				break;
			case 'end':
				f.parent().append(label);
				break;
			case 'parent':
				f.parent().after(label);
				break;
			case 'absleft':
				f.after(label);
				label.css({
					position : 'absolute',
					'z-index' : '555'
				});
				var left = f.position().left - label.outerWidth(false), top = f.position().top;
				if(f.is(':hidden')){
					if(f.is('input:hidden')){
						var prev = f.prev() || f.parent();
						left = prev.position().left - label.outerWidth(false);
						top = prev.position().top;
					}else{
						f.css('display', 'inherit');
						left = f.position().left - label.outerWidth(false);
						top = f.position().top;
						f.hide();
					}
				};
				label.css({
					left : left + 'px',
					top : top + 'px'
				});
				break;
			case 'absright':
				f.after(label);
				label.css({
					position : 'absolute',
					'z-index' : '555'
				});
				var left = f.position().left + f.outerWidth(false), top = f.position().top;
				if(f.is(':hidden')){
					if(f.is('input:hidden')){
						var prev = f.prev() || f.parent();
						left = prev.position().left + prev.outerWidth(false);
						top = prev.position().top;
					}else{
						f.css('display', 'inherit');
						left = f.position().left + f.outerWidth(false);
						top = f.position().top;
						f.hide();
					}
				};
				label.css({
					left : left + 'px',
					top : top + 'px'
				});
				break;
			default:
				var mpos, expr, re = /^(\-?\d+) (\-?\d+)$/; //left top
				if(re.test(position)){
					mpos = position.match(re);
					f.after(label);
					if(typeof mpos[1]!='undefined' && typeof mpos[2]!='undefined'){
						label.css({
							position : 'absolute',
							'z-index' : '555',
							left : mpos[1] + 'px',
							top : mpos[2] + 'px'
						});
					}
				}else{
					expr = f.next(position);
					if(expr.length){
						expr.before(label);
					}else{
						f.after(label);
					}
				}
				break;
		}
	}
	function createMask(obj){
		var obj = $(obj), position = obj.position(), width = obj.width(), height = obj.height(), lay = $('<div>Submitting, please wait...</div>');
		obj.after(lay);
		lay.css({
			position : 'absolute',
			left : position.left,
			top : position.top,
			width : width,
			height : height,
			color : '#ffffff',
			font : 'bold 14px/'+height+'px tahoma',
			opacity : 0,
			'z-index' : '88',
			'text-align' : 'center',
			'background-color' : '#000000'
		});
		return lay.animate({opacity:0.5}, 300);
	}
	function addOption(ths){
		var ths = $(ths), vt, value, text, opt = ths.attr('opt').split('^'), optpos = ths.attr('optpos')||'last', eq;
		if(optpos=='last'){
			for(var i=0; i<opt.length; i++){
				vt = opt[i].split('|');
				value = vt[0];
				text = vt[1];
				ths.append('<option value="'+value+'">'+text+'</option>');
			}
		}else{
			for(var i=opt.length-1; i>=0; i--){
				vt = opt[i].split('|');
				value = vt[0];
				text = vt[1];
				if(optpos=='first'){
					ths.prepend('<option value="'+value+'">'+text+'</option>');
				}else{
					var opt = ths.find('option');
					if(optpos<opt.length){
						opt.eq(optpos).before('<option value="'+value+'">'+text+'</option>');
					}else{
						opt.last().after('<option value="'+value+'">'+text+'</option>');
					}
				}
			}
		}
	}
	function getMarkCheck(obj){
		obj = $(obj);
		if(obj.attr('disabled'))return [];
		if(!!obj.data('coo'))return obj.data('coo');
		var tmclass = [], tempid, i, lst, objType = obj.attr('type');
		if(objType == 'checkbox' || objType == 'radio'){
			var name = obj.attr('name'), hasID = false, pass = false;
			if(!!!name){name = obj.attr('id');hasID = true}
			if(!!!name)return [];
			var box = $('input[name="'+name.replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
			if(!box.length)box = $('input[id="'+name.replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _form);
			box.each(function(){
				pass = false;
				tempid = $(this).attr('class');
				if(!!!tempid || !tempid.length || tempid.indexOf(prefix+'-')==-1){pass = true;return true};
				lst = $.trim(tempid).replace(/\s+/g, ' ').split(' ');
				for(i=0; i<lst.length; i++){
					if(!lst[i].length)continue;
					if(lst[i].indexOf(prefix+'-') > -1){
						var j, ls = [], arr = lst[i].split('-');
						for(j=1; j<arr.length; j++){
							if($.inArray(arr[j-1], ['<', '<=', '>', '>=', '='])>-1){
								ls.splice(ls.length-1, 1, ls[ls.length-1]+'-'+arr[j]);
							}else{
								ls.push(arr[j]);
							}
						}
						for(j=0; j<ls.length; j++){
							if(ls[j]!='' && $.inArray(ls[j], tmclass)==-1)tmclass.push(ls[j]);
						}
					}
				}
			});
			box.each(function(){$(this).data('coo', tmclass)});
			if(pass)return [];
		}else{
			tempid = obj.attr('class');
			if(!!!tempid || !tempid.length || tempid.indexOf(prefix+'-')==-1)return [];
			lst = $.trim(tempid).replace(/\s+/g, ' ').split(' ');
			for(i=0; i<lst.length; i++){
				if(!lst[i].length)continue;
				if(lst[i].indexOf(prefix+'-') > -1){
					var j, ls = [], arr = lst[i].split('-');
					for(j=1; j<arr.length; j++){
						if($.inArray(arr[j-1], ['<', '<=', '>', '>=', '='])>-1){
							ls.splice(ls.length-1, 1, ls[ls.length-1]+'-'+arr[j]);
						}else{
							ls.push(arr[j]);
						}
					}
					for(j=0; j<ls.length; j++){
						if(ls[j]!='' && $.inArray(ls[j], tmclass)==-1)tmclass.push(ls[j]);
					}
				}
			};
			obj.data('coo', tmclass);
		}
		return tmclass;
	}
	function validateForm(obj){
		obj = $(obj);
		$('input', obj).each(function(){
			var mk = getMarkCheck(this);
			if(!mk.length)return true;
			var t, o = $(this), objType = o.attr('type');
			if(objType == 'checkbox' || objType == 'radio'){
				var box = [];
				if(!!o.attr('name'))box = $('input[name="'+o.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
				if(!box.length && !!o.attr('id'))box = $('input[id="'+o.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
				box.each(function(){
					var b = $(this);
					if(!!!b.data('click')){
						b.data('click', true);
						b.onclick(function(){
							$(_form).removeData('formstatus').removeData('submitting');
							box.removeData('box.break');
							validateField(this, mk);
						});
					}
					if(!!b.attr('need') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('need'));
					if(!!b.attr('min') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('min'));
					if(!!b.attr('max') && !!!b.data('coo.max.length'))box.data('coo.max.length', b.attr('max'));
					if(!!b.attr('need') || !!b.attr('min'))box.data('coo.min', true);
					if(!!b.attr('max'))box.data('coo.max', true);
				});
				for(t=0; t<mk.length; t++){
					var tm = mk[t];
					if(tm.charAt(0)=='v'){
						tm = tm.substring(1);
						if(o.val() == tm)o.attr('checked', 'checked').prop('checked', 'checked');
					}
				}
			}else{
				for(t=0; t<mk.length; t++){
					var tm = mk[t];
					if(tm == 'color'){
						o.data('color', true);
						var options = {}, opt = o.attr('options');
						eval("options = "+opt);
						if($.isPlainObject(options))o.colorpicker(options);
						break;
					}
					if(tm == 'date'){
						o.data('date', true);
						var options = {}, opt = o.attr('options');
						if(opt)eval("options = "+opt);
						if(!!o.attr('just')&&(o.attr('just')=='year'||o.attr('just')=='month'))options = $.extend(options, {just:o.attr('just')});
						options = $.extend({
							callback : function(){
								if(validateField(o[0], mk)){
									for(var i=0; i<mk.length; i++){
										if(mk[i].charAt(0)=='@'){
											var tm = mk[i];
											tm = tm.substring(1);
											var sinbo = $(tm, _form);
											if(!sinbo.length)return;
											sinbo.simclick();
											break;
										}
									}
								}
							}
						}, options);
						var re, format = 'yyyy-m-d';
						if(options.just=='year'){
							format = 'yyyy';
							re = /^(19|20)\d{2}$/;
							o.data('options.just', re);
						}else if(options.just=='month'){
							format = 'yyyy-m';
							re = /^(19|20)\d{2}-(((0?[1-9])|10|11|12)|([1-9]|10|11|12))$/;
							o.data('options.just', re);
						};
						options = $.extend(options, {format:format});
						o.datepicker(options);
						break;
					}
				}
			}
		});
		$('select', obj).each(function(){
			var mk = getMarkCheck(this);
			if(!mk.length)return true;
			var o = $(this), multiple = o.attr('multiple');
			for(var t=0; t<mk.length; t++){
				if(mk[t].charAt(0) == 'v'){
					if(!!!multiple)o.children('option').removeAttr('selected').removeProp('selected');
					break;
				}
				if(mk[t].charAt(0) == 't' && mk[t] != 'time'){
					if(!!!multiple)o.children('option').removeAttr('selected').removeProp('selected');
					break;
				}
			}
			for(var t=0; t<mk.length; t++){
				var tm = mk[t];
				if(tm == 'time'){
					var target = o.attr('target'), month = '', day = '';
					if(!!target){
						target = target.split(',');
						month = target[0];
						if(target.length>1)day = target[1];
					};
					$.linkdate({year:o, month:month, day:day});
					continue;
				}
				if(tm.charAt(0) == 'v'){
					tm = tm.substring(1);
					o.children('option').each(function(){
						if($(this).val() == tm)$(this).attr('selected', 'selected').prop('selected', 'selected');
					});
				}
				if(tm.charAt(0) == 't'){
					tm = tm.substring(1);
					o.children('option').each(function(){
						if($(this).text() == tm)$(this).attr('selected', 'selected').prop('selected', 'selected');
					});
				}
			}
			if(!!o.attr('opt'))addOption(this);
		});
		$('select[normal]', obj).selectstyle({
			callback:function(){obj.removeData('formstatus').removeData('submitting')}
		});
		$(':file', obj).each(function(){
			var o = $(this);
			if(!!o.attr('src') || !!o.attr('text'))o.filestyle();
			if(!!o.attr('url'))o.ajaxupload();
		});
		obj.on('keyup blur', 'input:not(:checkbox, :radio, :file, :submit, :image), textarea', function(){
			var mk = getMarkCheck(this);
			if(!mk.length)return;
			obj.removeData('formstatus').removeData('submitting');
			validateField(this, mk);
		}).on('focus', 'input:not(:checkbox, :radio, :file, :submit, :image), textarea', function(){
			var f = $(this);
			if(!!f.attr('tip')){
				if(!!!f.data('success')){
					var labelID, tip = f.attr('tip'), tipcss = 'tip', tiparea = [], objMark = f.attr('id'), position = '', hasID = true;
					if(!!!objMark){objMark = f.attr('name');hasID = false}
					if(!!!objMark)return true;
					labelID = 'coo_' + objMark.replace(/\[/,'\\[').replace(/\]/,'\\]');
					f.parents('body').find('#' + labelID).remove();
					var c = css;
					if(!!f.attr('css'))c = f.attr('css');
					if(c)f.removeClass(c);
					if(!!!f.attr('noLabel') && !nolabel){
						if(!!f.attr('tip-css'))tipcss = f.attr('tip-css');
						if(!!f.attr('tip-area'))tiparea = f.parents('body').find(f.attr('tip-area'));
						if(!!f.attr('pos'))position = f.attr('pos');
						var htmlFor = hasID ? 'for="'+f.attr('id') : '',
						poscls = (!!position && !/^(\-?\d+) (\-?\d+)$/.test(position)) ? position+'label' : 'rightlabel',
						labelhtml = '<label id="' + labelID.replace(/\\\[/,'\[').replace(/\\\]/,'\]') + '" class="' + tipcss + ' ' + poscls + ' '+prefix+'label ' + (objMark+'_label') + '" ' + htmlFor + '><span>' + tip + '</span></label>',
						label = $(labelhtml);
						if(tiparea.length){
							if(!!!tiparea.data('html'))tiparea.data('html',tiparea.html());
							tiparea.html(labelhtml);
						}else{
							if(position==''){
								f.after(label);
							}else{
								setPosition(position, f, label);
							}
						}
					}
				}
			}
		});
		$(':checkbox, :radio', obj).boxstyle({
			callback:function(){obj.removeData('formstatus').removeData('submitting')}
		});
		obj.on('click', ':checkbox, :radio', function(){
			var mk = getMarkCheck(this);
			if(!mk.length)return;
			obj.removeData('formstatus').removeData('submitting');
			var box = [];
			if(!!$(this).attr('name'))box = $('input[name="'+$(this).attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
			if(!box.length && !!$(this).attr('id'))box = $('input[id="'+$(this).attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
			if(box.length)box.removeData('box.break');
			box.each(function(){
				var b = $(this);
				if(!!!b.data('click')){
					b.data('click', true);
					b.onclick(function(){
						$(_form).removeData('formstatus').removeData('submitting');
						box.removeData('box.break');
						validateField(this, mk);
					});
				}
				if(!!b.attr('need') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('need'));
				if(!!b.attr('min') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('min'));
				if(!!b.attr('max') && !!!b.data('coo.max.length'))box.data('coo.max.length', b.attr('max'));
				if(!!b.attr('need') || !!b.attr('min'))box.data('coo.min', true);
				if(!!b.attr('max'))box.data('coo.max', true);
			});
		});
		obj.on('change', 'select, :file', function(){
			var mk = getMarkCheck(this);
			if(!mk.length)return;
			obj.removeData('formstatus').removeData('submitting');
			validateField(this, mk);
		});
		obj.on('click', ':reset', function(){
			obj.removeData('formstatus').removeData('submitting');
			obj.find('label.'+prefix+'label').remove();
			if($.isFunction(reset))reset.call(obj);
		});
		if(addElements.length>0 && addElements.length==addRegs.length){
			for(var i=0; i<addElements.length; i++){
				$(addElements[i], obj).each(function(){
					var ths = $(this), reg = addRegs[i];
					if(ths.is('input:not(:checkbox, :radio, :file, :submit, :image), textarea')){
						ths.on('keyup blur', function(){
							obj.removeData('formstatus').removeData('submitting');
							validateField(this, reg, false, true);
						});
					}else if(ths.is(':checkbox, :radio')){
						var box = [];
						if(!!ths.attr('name'))box = $('input[name="'+ths.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
						if(!box.length && !!ths.attr('id'))box = $('input[id="'+ths.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', obj);
						box.each(function(){
							var b = $(this);
							if(!!!b.data('click')){
								b.data('click', true);
								b.onclick(function(){
									obj.removeData('formstatus').removeData('submitting');
									box.removeData('box.break');
									validateField(this, reg, false, true);
								});
							}
							if(!!b.attr('need') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('need'));
							if(!!b.attr('min') && !!!b.data('coo.min.length'))box.data('coo.min.length', b.attr('min'));
							if(!!b.attr('max') && !!!b.data('coo.max.length'))box.data('coo.max.length', b.attr('max'));
							if(!!b.attr('need') || !!b.attr('min'))box.data('coo.min', true);
							if(!!b.attr('max'))box.data('coo.max', true);
						});
					}else if(ths.is('select, :file')){
						ths.on('change', function(){
							obj.removeData('formstatus').removeData('submitting');
							validateField(this, reg, false, true);
						});
					}
				});
			}
		}
		function Validate(obj){
			var o = $(obj), validationError = false, formfields = o.data('formfields'), result;
			if(o.data('formstatus') && formfields['input']==$('input',o).length && formfields['textarea']==$('textarea',o).length && formfields['select']==$('select',o).length)return false;
			if($.isFunction(before)){
				result = before(o[0]);
				if(typeof(result) == 'boolean')return result;
			}
			$('input, select, textarea', o).each(function(){
				var mk = getMarkCheck(this);
				if(!mk.length)return true;
				if(!validateField(this, mk)){
					validationError = true;
					if(!!!o.data('coo.error'))o.data('coo.error', $(this).data('coo.error'));
				}
			});
			if(addElements.length>0 && addRegs.length>0 && addElements.length==addRegs.length){
				for(var i=0; i<addElements.length; i++){
					$(addElements[i], obj).each(function(){
						if(!validateField(this, addRegs[i], false, true))validationError = true;
					});
				}
			};
			o.data('formstatus', 'valided').data('formfields', {
				'input': $('input',o).length,
				'textarea': $('textarea',o).length,
				'select': $('select',o).length
			});
			if($.isFunction(after)){
				result = after(o[0], validationError);
				if(typeof(result) == 'boolean')return result;
			}
			if(validationError)return false;
			o.removeData('formstatus').removeData('coo.error').removeData('coo.element');
			var onsubmit = o.data('onsubmit');
			if($.isFunction(onsubmit)){
				result = onsubmit(o[0]);
				if(!!o.data('boolean')){
					if(typeof(result) == 'undefined')return true;
					if(typeof(result) == 'boolean')return result;
				}else{
					if(typeof(result) == 'boolean')return result;
				}
			}
			if($.isFunction(callback)){
				result = callback(o[0]);
				if(typeof(result) == 'boolean')return result;
			}
			return true;
		}
		if(obj.is('form')){
			var laymask = lay, onsubmit = obj.attr('onsubmit');
			if(!!onsubmit && typeof(onsubmit)=='string'){
				var submitfunc;
				eval("submitfunc = function(_ths){"+onsubmit.replace(/this/g,'_ths')+"}");
				if(onsubmit.indexOf('return ')!=-1)obj.data('boolean', true);
				obj.data('onsubmit', submitfunc);
			};
			obj.removeAttr('onsubmit').get(0).onsubmit = null;
			obj.submit(function(){
				if(!!obj.data('submitting'))return false;
				if(Validate(obj)==false)return false;
				obj.removeData('coo.error').removeData('coo.element');
				obj.data('submitting', true);
				if(disBtn && obj.attr('target')!='_blank')obj.find(':submit, :image').attr('disabled', 'disabled');
				if(delay || $.isFunction(ajaxSubmit)){
					if(!$.isFunction(ajaxSubmit)){
						setTimeout(function(){obj[0].submit()}, delay);
					}else{
						var ajaxlay = createMask(obj[0]);
						if(!delay)delay = 0;
						setTimeout(function(){
							obj.ajaxsubmit(function(html){
								ajaxlay.animate({opacity:0}, 300, function(){ajaxlay.remove()});
								ajaxSubmit(html);
								obj.removeData('formstatus').removeData('submitting');
								if(disBtn)obj.find(':submit, :image').removeAttr('disabled');
							});
						}, delay);
					}
					return false;
				}
				if(laymask)createMask(obj[0]);
				if(autosave!=null){
					var formid = obj.attr('id');
					if(!!!formid)formid = obj.attr('name');
					if(!!!formid)formid = obj.attr('class').replace(/\s/g,'');
					$.cookie('autosave_'+formid, null);
					if($.isArray(autosave.aloneEl) && autosave.aloneEl.length){
						for(var i=0; i<autosave.aloneEl.length; i++)$.cookie('autosave_'+formid+'_'+autosave.aloneEl[i][1]);
					}
				}
				if(debug || !!obj.attr('debug'))return false;
			});
		}else{
			if(Validate(obj[0])==false)return false;
			obj.removeData('coo.error');
			return true;
		}
	};
	return (function(){
		if(autosave!=null)the.autosave(autosave);
		if(!me){
			if(the.is('form')){
				if(!!the.data('jquery.coo'))return true;
				the.data('jquery.coo', true);
			};
			_form = the[0];
			validateForm(_form);
			return the;
		}else{
			if(!!!callback)callback = 'need';
			return validateField(the, callback.replace(/^coo-/, ''), true);
		}
	})();
};

//AJAX提交表单
$.fn.ajaxsubmit = function(options){
	if(!$.isFunction(options))options = $.extend({
		contentType : 'application/x-www-form-urlencoded', //发送信息至服务器时内容编码类型
		dataType : 'html', //预期服务器返回的数据类型
		before : null, //提交前执行,返回false即终止提交
		success : null, //请求成功后的回调函数
		error : null, //请求失败时调用此函数
		complete : null //请求完成后回调函数(请求成功或失败之后均调用)
	}, options);
	return this.each(function(){
		if(!!$(this).data('ajaxsubmit'))return true;
		var _this = $(this), action = _this.attr('action')||'', method = _this.attr('method')||'post', data = _this.param()||{}, dataType;
		if(!action.length || action.substr(0,1)=='#' || action.substr(0,11)=='javascript:')return true;
		_this.data('ajaxsubmit', true);
		if(!$.isFunction(options)){
			dataType = _this.attr('dataType')||options.dataType;
			if($.isFunction(options.before)){
				var result = options.before.call(_this);
				if(typeof result=='boolean')return result;
			}
		}else{
			dataType = _this.attr('dataType')||'html';
		};
		$.ajax({
			type : method,
			url : action,
			data : data,
			dataType : dataType,
			success : function(html){
				if($.isFunction(options)){
					options.call(_this, html);
				}else{
					if($.isFunction(options.success))options.success.call(_this, html);
				}
			},
			error : function(xml, status, e){
				if(!$.isFunction(options) && $.isFunction(options.error))options.error.call(_this, status, e);
			},
			complete : function(xml, status){
				_this.removeData('ajaxsubmit');
				if(!$.isFunction(options) && $.isFunction(options.complete))options.complete.call(_this, status);
			}
		});
	});
};

//获取所有控件值, 若控件存在number自定义属性或type="number"即值转为数值型
//可用带有自定义属性[object|array]=key值的标签包裹特定的控件,达到形成内嵌式的复杂型json数据,不支持options.type=string
$.fn.param = function(options){
	options = $.extend({
		type : 'object', //返回类型，object|string
		attr : '', //没有name属性时使用自定义属性代替
		ignore : '', //忽略的控件，expr|element|jquery
		filter : true, //过滤空值控件
		each : null //每个控件取值时执行,返回值将作为控件的值,this:控件,接收两个参数:index(控件索引),element(原生控件)
	}, options);
	var _form = this, url = options.type=='object'?{}:'', params = this.find('input, select, textarea').not(options.ignore).not('[disabled]');
	params.each(function(k){
		var _this = $(this), name = _this.attr('name'), val = '', isArray = false;
		if(_this.is(':radio, :checkbox') && !_this.is(':checked'))return true;
		if(!!!name && options.attr.length)name = _this.attr(options.attr);
		if(!!!name || (!!name && !$.trim(name.replace(/\[\]/ig, '')).length))return true;
		if($.isFunction(options.each) && typeof (returnVal = options.each.call(_this, k, _this[0]))!='undefined'){
			val = returnVal;
		}else{
			val = _this.is('select') ? $.trim(_this.selected().val()) : $.trim(_this.val());
		}
		if(options.filter && ((typeof val=='string'&&!val.length) || ($.isArray(val)&&!val.length) || ($.isPlainObject(val)&&!$.isJson(val))))return true;
		if(!!_this.attr('number') || _this.attr('type')=='number')val = Number(val);
		isArray = /\[\]/ig.test(name);
		name = name.replace(/\[\]/ig, '');
		if(options.type=='object'){
			if(_this.parents('[object]').length || _this.parents('[array]').length){
				var _last = _this;
				do{
					var p = _last.parents('[object], [array]').eq(0), attr = p.attr('object')||p.attr('array');
					if(!p.is('[object]')){
						var parent = p;
						p = _form.find('['+attr+']');
						if(!p.length){
							p = $('<p style="display:none;" '+attr+'="'+attr+'" array="'+attr+'"></p>').data('mirror', true);
							parent.before(p);
						}
					};
					var data = p.data(attr);
					if(_last.is('input, select, textarea')){
						if(!!!data){
							var obj = {};
							if(isArray){
								obj[name] = [val];
							}else{
								obj[name] = val;
							}
							if(p.is('[object]')){
								p.data(attr, obj);
							}else{//array
								p.data(attr, [obj]);
							}
						}else{
							if(!$.isArray(data)){
								if(isArray){
									if($.isArray(data[name]))data[name].push(val);
									else data[name] = [val];
								}else{
									data[name] = val;
								};
								p.data(attr, data);
							}else{//array
								if($.inArray(name, Object.keys(data[data.length-1]))==-1){
									if(isArray){
										data[data.length-1][name] = [val];
									}else{
										data[data.length-1][name] = val;
									}
								}else{
									if($.isArray(data[data.length-1][name])){
										data[data.length-1][name].push(val);
									}else{
										var obj = {};
										obj[name] = val;
										data.push(obj);
									}
								};
								p.data(attr, data);
							}
						}
					}else{//nested
						var _attr = _last.attr('object')||_last.attr('array'), _d = _last.data(_attr);
						if(!!!data){
							var obj = {};
							obj[_attr] = _d;
							if(p.is('[object]')){
								p.data(attr, obj);
							}else{//array
								p.data(attr, [obj]);
							}
						}else{
							if(!$.isArray(data)){
								data[_attr] = _d;
								p.data(attr, data);
							}else{//array
								if($.inArray(_attr, Object.keys(data[data.length-1]))==-1){
									data[data.length-1][_attr] = _d;
								}else{
									data[_attr] = _d;
								};
								p.data(attr, data);
							}
						}
					};
					_last = p;
					if(!_last.parents('[object], [array]').length)break;
				}while(true);
				url[_last.attr('object')||_last.attr('array')] = _last.data(attr);
			}else{
				if(isArray){
					if($.isArray(url[name]))url[name].push(val);
					else url[name] = [val];
				}else{
					url[name] = val;
				}
			}
		}else{
			url = (url==''?name+'=':url+'&'+name+'=') + encodeURIComponent(val);
		}
	});
	params.each(function(){
		$(this).parents('[object], [array]').each(function(){
			var p = $(this), attr = p.attr('object')||p.attr('array');
			p.removeData(attr);
			_form.find('['+attr+']').each(function(){
				if(!!$(this).data('mirror'))$(this).remove();
			});
		});
	});
	return url;
};

//自动保存表单项目(利用Cookie)
$.fn.autosave = function(options){
	options = $.extend({
		clear : false, //提交后立即清空
		expires : 1, //自动保存过期时间,单位天
		interval : 60, //保存间隔,单位秒
		exceptEl : [], //指定不保存的控件,expr|element|jquery
		aloneEl : [] //单独一个Cookie保存的控件与对应的Cookie名,值:[['expr1','cookie1'], ['expr2','cookie2']],一般长内容控件用,支持window.localStorage的浏览器不需使用
	}, options);
	return this.each(function(){
		var _this = $(this), formid = _this.attr('id'), splitChar = '#as#';
		if(!!_this.data('autosave'))return true;
		_this.data('autosave', true);
		if(!!!formid)formid = _this.attr('name');
		if(!!!formid)formid = _this.attr('class').replace(/\s/g,'');
		var els, aloneEl = [];
		if(options.aloneEl.length){
			for(var i=0; i<options.aloneEl.length; i++)aloneEl.push(options.aloneEl[i][0]);
		};
		els = _this.find(':hidden, :text, :checkbox, :radio, select, textarea').not(options.exceptEl.join(',')).not(aloneEl.join(',')).not('option');
		els.each(function(){
			var ths = $(this), id = ths.attr('id');
			if(!!!id)id = ths.attr('name');
			if(!!!id)id = formid+'_'+ths[0].tagName.toLowerCase();
			ths.attr('id', id);
			if(ths.is(':hidden, :text, textarea'))ths.on('keyup change', function(){_this.removeData('autosaved')});
			if(ths.is('select'))ths.on('change', function(){_this.removeData('autosaved')});
			if(ths.is(':checkbox, :radio'))ths.onclick(function(){_this.removeData('autosaved')});
		});
		/*
		_this.find(':reset').click(function(){
			getCookie();
			e.preventDefault();
			return false;
		});
		*/
		_this.submit(function(){
			if(options.clear){
				$.cookie('autosave_'+formid, null);
				if(options.aloneEl.length){
					for(var i=0; i<options.aloneEl.length; i++)$.cookie('autosave_'+formid+'_'+options.aloneEl[i][1], null);
				}
			}
		});
		var getCookie = function(){
			_this.removeData('autosaved');
			var cookie = $.cookie('autosave_'+formid), arr;
			if(!!cookie){
				arr = cookie.split(splitChar);
				for(var i=0; i<arr.length; i++){
					if(arr[i]!=''){
						var expr = _this.find(arr[i].substr(0, arr[i].indexOf('='))), value;
						if(expr.length){
							value = arr[i].substr(arr[i].indexOf('=')+1);
							if(expr.is(':hidden, :text, textarea'))expr.val(value.replace(/\$nl\$/g,'\n').replace(/\$nbsp\$/g,' '));
							if(expr.is('select')){
								var option = expr.find('option'), selected = value.split(',');
								for(var j=0; j<selected.length; j++){
									if(!!(selected[j]*1))option.eq(j).attr('selected', 'selected');
									else option.eq(j).removeAttr('selected');
								}
							}
							if(expr.is(':checkbox, :radio')){
								var box = [];
								if(!!expr.attr('name'))box = $('input[name="'+expr.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _this), checked = value.split(',');
								if(box.length){
									for(var j=0; j<checked.length; j++){
										if(!!(checked[j]*1))box.eq(j).attr('checked',true).prop('checked',true);
										else box.eq(j).removeAttr('checked').removeProp('checked');
									}
								}
							}
						}
					}
				}
			}
			if(options.aloneEl.length){
				for(var i=0; i<options.aloneEl.length; i++){
					cookie = $.cookie('autosave_'+formid+'_'+options.aloneEl[i][1]);
					if(!!cookie){
						var expr = _this.find(options.aloneEl[i][0]), value;
						if(expr.length){
							value = cookie.substr(cookie.indexOf('=')+1);
							expr.val(value.replace(/\$nl\$/g,'\n').replace(/\$nbsp\$/g,' '));
						}
					}
				}
			}
		};
		getCookie();
		setInterval(function(){
			if(!!!_this.data('autosaved')){
				var str = '', tmp;
				els.each(function(){
					var ths = $(this), id = ths.attr('id');
					if(ths.is(':hidden, :text, textarea')){
						tmp = ths.val().replace(/\n/g,'$nl$').replace(/\s/g,'$nbsp$');
						if(tmp=='')return true;
						str += (str=='' ? '#'+id+'='+tmp : splitChar+'#'+id+'='+tmp);
					}
					if(ths.is('select')){
						if(!ths.find('option').length)return true;
						tmp = '';
						ths.find('option').each(function(){
							if($(this).is(':selected'))tmp += (tmp=='' ? '1' : ',1');
							else tmp += (tmp=='' ? '0' : ',0');
						});
						str += (str=='' ? '#'+id+'='+tmp : splitChar+'#'+id+'='+tmp);
					}
					if(ths.is(':checkbox, :radio')){
						tmp = '';
						var box = [];
						if(!!ths.attr('name'))box = $('input[name="'+ths.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]', _this);
						if(box.length){
							box.each(function(){
								if(!!$(this).data('autosaveClick'))return false;
								$(this).data('autosaveClick', true);
								if($(this).is(':checked'))tmp += (tmp=='' ? '1' : ',1');
								else tmp += (tmp=='' ? '0' : ',0');
								if(box.index(this)==box.length-1)str += (str=='' ? '#'+id+'='+tmp : splitChar+'#'+id+'='+tmp);
							});
							if(box.index(ths)==box.length-1)box.removeData('autosaveClick');
						}
					}
				});
				$.cookie('autosave_'+formid, str, {expires:options.expires});
				if(options.aloneEl.length){
					for(var i=0; i<options.aloneEl.length; i++){
						var expr = options.aloneEl[i][0], ths = _this.find(expr), tmp = ths.val().replace(/\n/g,'$nl$').replace(/\s/g,'$nbsp$');
						if(tmp!='')$.cookie('autosave_'+formid+'_'+options.aloneEl[i][1], expr+'='+tmp, {expires:options.expires});
					}
				};
				_this.data('autosaved', true);
				var position = _this.position(), date = new Date(),
				now = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate()+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds(),
				tip = $('<div>在 '+now+' 自动保存成功</div>').css({
					position:'absolute', 'z-index':'555', background:'#fc0', color:'#fff', left:position.left, top:position.top,
					height:'30px', 'line-height':'30px', overflow:'hidden', 'text-align':'center', opacity:0
				});
				_this.after(tip);
				var padding = tip.padding(), width = _this.width() - padding.left - padding.right;
				tip.width(width).animate({opacity:0.85}, 200, function(){
					setTimeout(function(){
						tip.animate({opacity:0}, 200, function(){tip.remove()});
					}, 5000);
				});
			}
		}, options.interval*1000);
	});
};

//多|单选框美化, CSS3
$.fn.boxstyle = function(options){
	options = $.extend({
		cls : '', //样式类名
		face : '', //app类型按钮的类名
		callback : null //选择后执行(this指向本对象[jQuery])
	}, options);
	return this.each(function(){
		var _this = $(this), cls = _this.attr('cls')||options.cls, face = _this.attr('face')||options.face;
		if(!!!cls && !!!face)return true;
		if(!!_this.data('boxstyle'))return true;
		_this.data('boxstyle', true);
		var box = [];
		if(!!_this.attr('name'))box = $('input[name="'+_this.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]');
		if(!box.length && !!_this.attr('id'))box = $('input[id="'+_this.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]');
		box.each(function(){
			var _this = $(this), type = _this.attr('type'), title = _this.attr('title'), label = null, div = null;
			if(!!_this.data('spbox'))return true;
			_this.data('spbox', true);
			if(!!face){
				label = $('<label class="boxstyle '+face+'" '+(!!title?'title="'+title+'"':'')+'></label>');
				div = '<div></div>';
			}else{
				label = $('<label class="boxstyle '+cls+'" '+(!!title?'title="'+title+'"':'')+'></label>');
				div = _this.is(':checkbox') ? '<div><span></span></div>' : '<div></div>';
			};
			_this.wrap(label);
			_this.after(div);
			var callback = _this.attr('callback')||options.callback;
			if(!!callback && typeof(callback)=='string'){
				var func;
				eval("func = "+callback);
				callback = func;
			}
			if($.isFunction(callback)){
				_this.change(function(){
					callback.call(_this);
				});
			}
			/*
			if(!!_this.attr('id'))$('label[for="'+_this.attr('id')+'"]').onclick(function(e){
				setTimeout(function(){
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
					var fn = _this.attr('fn');
					if(!!fn)eval(fn+'.call(_this)');
					var objEvt = $._data(_this[0], 'events');
					if(objEvt && objEvt['click']){
						_this.click();
						e.preventDefault();
						return false;
					}
				}, 10);
			});
			*/
		});
	});
};

//多|单选框美化, 使用图片
$.fn.boximage = function(options){
	options = $.extend({
		src : '', //默认图片|类名:.class
		check : '', //选择后的图片|类名:.class
		hover : '', //鼠标经过时的图片|类名:.class
		disable : '', //禁用时的图片|类名:.class
		face : '', //app类型按钮的类名
		width : 0, //宽度(用类名时可为0)
		height : 0, //高度(用类名时可为0)
		callback : null //选择后执行(接受一个参数:本对象[jQuery])
	}, options);
	return this.each(function(){
		var _this = $(this), src = _this.attr('src')||options.src, check = _this.attr('check')||options.check, hover = _this.attr('hover')||options.hover,
		disable = _this.attr('disable')||options.disable, face = _this.attr('face')||options.face,
		name = _this.attr('name')||_this.attr('id'), type = _this.attr('type'), opacity = this.disabled ? (!!disable?1:0.4) : 1,
		width = _this.attr('w')||options.width, height = _this.attr('h')||options.height, ie6 = $.browser.ie6, useclass = false;
		if((!!!src || !!!check) && !!!face)return true;
		if(!!_this.data('boximage'))return true;
		_this.data('boximage', true);
		if(face==''){
			if(src.charAt(0)=='.'){
				src = src.substring(1);
				check = check.substring(1);
				if(!!hover)hover = hover.substring(1);
				if(!!disable)disable = disable.substring(1);
				useclass = true;
			}
			if(!useclass && (!width || !height))return true;
		}
		if(type=='checkbox'){
			if(!!!face){
				var div = $('<div class="boximage" style="display:inline-block;*display:inline;*zoom:1;"></div>'),
				checkimg, hoverimg, desrc = this.checked ? check : ((this.disabled&&!!disable)?disable:src);
				_this.before(div);
				_this.hide();
				if(!useclass){
					checkimg = new Image();
					hoverimg = new Image();
					checkimg.src = check;
					if(!!hover)hoverimg.src = hover;
					div.css({
						background : 'url('+desrc+')',
						opacity : opacity,
						width : width,
						height : height,
						overflow : 'hidden',
						cursor : 'pointer'
					});
					if(this.disabled)div.css('cursor','not-allowed');
					if(ie6)div.css({
						background : 'none',
						filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+desrc+'")'
					});
				}else{
					div.addClass(src).addClass(desrc).css({opacity:opacity, overflow:'hidden'});
					if(div.css('cursor')=='auto')div.css('cursor','pointer');
				}
				if(this.disabled)return true;
				div.hover(function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+hover+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+hover+'")'
						});
					}else{
						div.addClass(hover);
					}
				},function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+src+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
						});
					}else{
						div.removeClass(hover);
					}
				}).click(function(){
					if(_this[0].checked){
						if(!useclass){
							div.css({background:'url('+src+')'});
							if(ie6)div.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
							});
						}else{
							div.removeClass(check);
						};
						_this.removeAttr('checked').removeProp('checked');
					}else{
						if(!useclass){
							div.css({background:'url('+check+')'});
							if(ie6)div.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+check+'")'
							});
						}else{
							div.addClass(check);
						};
						_this.attr('checked',true).prop('checked',true).parent().find('label.error').remove();
					};
					var onclick = _this.attr('onclick');
					if(!!onclick && typeof(onclick)=='string'){
						var clickfunc;
						eval("clickfunc = function(_ths){"+onclick.replace(/this/g,'_ths')+"}");
						onclick = clickfunc;
					}
					if($.isFunction(onclick))onclick(_this[0]);
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
				});
				if(!!_this.attr('id'))$('label[for="'+_this.attr('id')+'"]').hover(function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+hover+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+hover+'")'
						});
					}else{
						div.addClass(hover);
					}
				},function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+src+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
						});
					}else{
						div.removeClass(hover);
					}
				}).click(function(){
					if(_this[0].checked){
						if(!useclass){
							div.css({background:'url('+src+')'});
							if(ie6)div.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
							});
						}else{
							div.removeClass(check);
						};
						_this.removeAttr('checked').removeProp('checked');
					}else{
						if(!useclass){
							div.css({background:'url('+check+')'});
							if(ie6)div.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+check+'")'
							});
						}else{
							div.addClass(check);
						};
						_this.attr('checked',true).prop('checked',true).parent().find('label.error').remove();
					};
					var onclick = _this.attr('onclick');
					if(!!onclick && typeof(onclick)=='string'){
						var clickfunc;
						eval("clickfunc = function(_ths){"+onclick.replace(/this/g,'_ths')+"}");
						onclick = clickfunc;
					}
					if($.isFunction(onclick))onclick(_this[0]);
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
					return false;
				});
			}else{
				var div = $('<div class="'+face+'"></div>'),
				label = '<label for="'+(!!_this.attr('id')?_this.attr('id'):'')+'"><div><font class="on">ON</font><font class="off">OFF</font></div><span></span></label>';
				_this.wrap(div);
				_this.after(label);
				if(!!_this.attr('id'))$('label[for="'+_this.attr('id')+'"]').click(function(){
					var onclick = _this.attr('onclick');
					if(!!onclick && typeof(onclick)=='string'){
						var clickfunc;
						eval("clickfunc = function(_ths){"+onclick.replace(/this/g,'_ths')+"}");
						onclick = clickfunc;
					}
					if($.isFunction(onclick))onclick(_this[0]);
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
				});
			}
		}else{
			if(!!_this.data('spbox'))return true;
			var box = [];
			if(!!_this.attr('name'))box = $('input[name="'+_this.attr('name').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]');
			if(!box.length && !!_this.attr('id'))box = $('input[id="'+_this.attr('id').replace(/\[/,'\\[').replace(/\]/,'\\]')+'"]');
			box.each(function(){
				var _this = $(this), div = $('<div class="boximage" style="display:inline-block;*display:inline;*zoom:1;"></div>'),
				src = _this.attr('src')||options.src, check = _this.attr('check')||options.check, hover = _this.attr('hover')||options.hover,
				disable = _this.attr('disable')||options.disable, opacity = this.disabled ? (!!disable?1:0.4) : 1,
				width = _this.attr('w')||options.width, height = _this.attr('h')||options.height, useclass = false;
				if(src.charAt(0)=='.'){
					src = src.substring(1);
					check = check.substring(1);
					if(!!hover)hover = hover.substring(1);
					if(!!disable)disable = disable.substring(1);
					useclass = true;
				}
				if(!useclass && (!width || !height))return true;
				var checkimg, hoverimg, desrc = this.checked ? check : ((this.disabled&&!!disable)?disable:src);
				_this.data('spbox', true);
				_this.before(div);
				_this.hide();
				if(!useclass){
					checkimg = new Image();
					hoverimg = new Image();
					checkimg.src = check;
					if(!!hover)hoverimg.src = hover;
					div.css({
						background : 'url('+desrc+')',
						opacity : opacity,
						width : width,
						height : height,
						overflow : 'hidden',
						cursor : 'pointer'
					});
					if(this.disabled)div.css('cursor','not-allowed');
					if(ie6)div.css({
						background : 'none',
						filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+desrc+'")'
					});
				}else{
					div.addClass(src).addClass(desrc).css({opacity:opacity, overflow:'hidden'});
					if(div.css('cursor')=='auto')div.css('cursor','pointer');
				}
				if(this.disabled)return true;
				div.hover(function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+hover+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+hover+'")'
						});
					}else{
						div.addClass(hover);
					}
				},function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+src+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
						});
					}else{
						div.removeClass(hover);
					}
				}).click(function(){
					if(_this[0].checked)return;
					$(':radio[name="'+name+'"]').removeAttr('checked').removeProp('checked').each(function(){
						if(this.disabled)return true;
						var dp = $(this).prev();
						if(!useclass){
							dp.css({background:'url('+src+')'});
							if(ie6)dp.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
							});
						}else{
							dp.removeClass(hover).removeClass(check);
						}
					});
					if(!useclass){
						div.css({background:'url('+check+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+check+'")'
						});
					}else{
						div.addClass(check);
					};
					_this.attr('checked',true).prop('checked',true).parent().find('label.error').remove();
					var onclick = _this.attr('onclick');
					if(!!onclick && typeof(onclick)=='string'){
						var clickfunc;
						eval("clickfunc = function(_ths){"+onclick.replace(/this/g,'_ths')+"}");
						onclick = clickfunc;
					}
					if($.isFunction(onclick))onclick(_this[0]);
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
				});
				if(!!_this.attr('id'))$('label[for="'+_this.attr('id')+'"]').hover(function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+hover+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+hover+'")'
						});
					}else{
						div.addClass(hover);
					}
				},function(){
					if(_this[0].checked)return;
					if(!!!hover)return;
					if(!useclass){
						div.css({background:'url('+src+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
						});
					}else{
						div.removeClass(hover);
					}
				}).click(function(){
					if(_this[0].checked)return;
					$(':radio[name="'+name+'"]').removeAttr('checked').removeProp('checked').each(function(){
						if(this.disabled)return true;
						var dp = $(this).prev();
						if(!useclass){
							dp.css({background:'url('+src+')'});
							if(ie6)dp.css({
								background : 'none',
								filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+src+'")'
							});
						}else{
							dp.removeClass(hover).removeClass(check);
						}
					});
					if(!useclass){
						div.css({background:'url('+check+')'});
						if(ie6)div.css({
							background : 'none',
							filter : 'progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src="'+check+'")'
						});
					}else{
						div.addClass(check);
					};
					_this.attr('checked',true).prop('checked',true).parent().find('label.error').remove();
					var onclick = _this.attr('onclick');
					if(!!onclick && typeof(onclick)=='string'){
						var clickfunc;
						eval("clickfunc = function(_ths){"+onclick.replace(/this/g,'_ths')+"}");
						onclick = clickfunc;
					}
					if($.isFunction(onclick))onclick(_this[0]);
					var callback = _this.attr('callback')||options.callback;
					if(!!callback && typeof(callback)=='string'){
						var func;
						eval("func = "+callback);
						callback = func;
					}
					if($.isFunction(callback))callback.call(_this);
					return false;
				});
			});
		};
	});
};

//文件框美化
$.fn.filestyle = function(options){
	options = $.extend({
		text : '', //默认文字
		src : '', //默认图片(设置后默认文字无效)|类名:.class
		hoversrc : '', //鼠标经过时的图片|类名:.class
		clicksrc : '', //按下鼠标时的图片|类名:.class
		width : 0, //宽度
		height : 0, //高度
		disabled : false, //是否禁用
		callback : null //选择后执行(接受一个参数:本对象[jQuery])
	}, options);
	return this.each(function(){
		var _this = $(this), id = _this.attr('id')||_this.attr('name'), cls = _this.attr('class'), useclass = false, text = _this.attr('text')||options.text,
		src = _this.attr('src')||options.src, hoversrc = _this.attr('hoversrc')||options.hoversrc, clicksrc = _this.attr('clicksrc')||options.clicksrc,
		width = _this.attr('w')||options.width||70, height = _this.attr('h')||options.height||19, disabled = _this.attr('disabled')||options.disabled;
		if(!src && !text)return true;
		if(_this.data('filestyle')){
			_this[0].style.cssText = '';
			if(!!_this.attr('cls'))_this.addClass(_this.attr('cls'));
			var parent = _this.parent();
			parent.before(_this);
			parent.remove();
		}
		if(!!_this.data('coo.label'))_this.attr('pos', 'parent');
		if(!id)id = 'undefined';
		if(!!src && src.charAt(0)=='.'){
			src = src.substring(1);
			if(!!hoversrc)hoversrc = hoversrc.substring(1);
			if(!!clicksrc)clicksrc = disablesrc.substring(1);
			useclass = true;
		};
		var wrapper = $('<div></div>').css({
			position : 'relative',
			display : 'inline-block',
			cursor : 'pointer',
			overflow : 'hidden',
			width : fixUnit(width),
			height : fixUnit(height),
			'line-height' : fixUnit(height)
		});
		if($.browser.ie7)wrapper.css({display:'inline', zoom:'1'});
		_this.wrap(wrapper);
		_this.css({
			width : fixUnit(width),
			height : fixUnit(height),
			position : 'absolute',
			top : '0',
			right : '0',
			margin : '0',
			'font-size' : height+'px',
			opacity : '0',
			cursor : 'pointer'
		});
		//if($.browser.ie6)_this.hide();
		_this.data('filestyle', true);
		wrapper = _this.parent();
		if(cls){
			wrapper.addClass(cls);
			_this.removeClass(cls).attr('cls', cls);
		}
		if(!!disabled){
			_this.attr('disabled', 'disabled');
		}else{
			_this.removeAttr('disabled');
			if(!useclass){
				var hoverimg = new Image(), clickimg = new Image();
				if(!!hoversrc)hoverimg.src = hoversrc;
				if(!!clicksrc)clickimg.src = clicksrc;
				if(src){
					wrapper.css({background:'url('+src+')'});
				}else{
					if(text)wrapper.append('<span>'+text+'</span>');
				}
			}else{
				wrapper.addClass(src);
			};
			wrapper.hover(function(){
				if(!!!hoversrc)return;
				if(!useclass)$(this).css({background:'url('+hoversrc+')'});
				else $(this).addClass(hoversrc);
			},function(){
				if(!!!hoversrc)return;
				if(!useclass)$(this).css({background:'url('+src+')'});
				else $(this).removeClass(hoversrc);
			}).mousedown(function(e){
				if(!!!clicksrc)return;
				if(!useclass)$(this).css({background:'url('+clicksrc+')'});
				else $(this).addClass(clicksrc);
			}).mouseup(function(){
				if(!!!clicksrc)return;
				if(!useclass){
					//var desrc = !!hoversrc ? hoversrc : src;
					var desrc = src;
					$(this).css({background:'url('+desrc+')'});
				}else{
					$(this).removeClass(clicksrc);
				}
			});
			if($.browser.ie6)wrapper.click(function(){_this[0].click()});
			_this.on('change', function(){
				if($.isFunction(options.callback))options.callback(_this);
			});
		}
		function fixUnit(str){
			if(!str)return '0';
			if((str+'')!='0')str = /^\-?\d+$/.test(str) ? str+'px' : str;
			return str;
		}
	});
};

//下拉框美化
$.fn.selectstyle = function(options){
	options = $.extend({
		normal : '', //美化使用的类名
		focus : '', //显示下拉列表后增加的类名
		option : '', //下拉列表的类名
		callback : null //选择后执行,this:本对象
	}, options);
	return this.not('[multiple]').each(function(){
		var _this = $(this).attr('pos','right'), id = _this.attr('id');
		if(!!!id)id = _this.attr('name');
		if(!!!id){id = new Date().getTime();_this.attr('id',id)};
		if(_this.removePlug('selectstyle')){_this.prev().remove();$('#selectstyle_list_'+id).remove()};
		var width = _this.outerWidth(false), option = _this.children(), normal = _this.attr('normal')||options.normal, focus = _this.attr('focus')||options.focus,
		optionClass = _this.attr('option')||options.option, callback = _this.attr('callback')||options.callback,
		text = _this.selected().text(), optionStyle = _this.selected().attr('style');
		if(!option.length)text = '没有选项';
		var a = $('<a href="javascript:void(0)" class="'+normal+'"'+(!!optionStyle?' style="'+optionStyle+'"':'')+'><strong></strong>'+text+'</a>');
		_this.hide().before(a);
		a.css('font-size','');
		var padding = a.padding();
		if(!option.length)a.width(80-padding.left-padding.right);
		if(!!_this.attr('disabled') || !option.length){
			a.css({opacity:0.6});
			if(option.length)a.width(width);
			return true;
		};
		var list = $('<div style="width:'+width+'px;height:auto;left:-9999px;top:-9999px;" id="selectstyle_list_'+id+'" class="'+optionClass+'"><ul></ul></div>');
		$(document.body).append(list);
		a.width(width-padding.left-padding.right).click(function(){
			if(list.css('display')=='none'){
				a.addClass(focus);
				var position = a.position();
				list.stopScroll(true).show().css({top:position.top+a.outerHeight(false)+3, left:position.left})
					.find('ul').scrollTop(0).scrollTop(list.find('a.this').position().top-7);
				$(document).on('click', selectControlReg);
			}else selectControl();
		});
		option.each(function(n){
			var o = $(this), style = o.attr('style'), cls = o.attr('class'), disabled = o.attr('disabled'),
			val = !!o.attr('value') ? o.attr('value').replace(/"/g,'\\"') : '', text = o.text(), l;
			if(o.is('option')){
				if(!!!disabled){
					l = $('<li><a href="javascript:void(0)"'+(!!style?' style="'+style+'"':'')+(!!cls?' class="'+cls+'"':'')+' title="'+val+'">'+text+'</a></li>');
					list.find('ul').append(l.attr('index', n));
					if(o.is(':selected'))l.find('a').addClass('this');
					l.click(function(){
						list.find('a').removeClass('this');
						$(this).find('a').addClass('this');
						option.removeProp('selected').eq(Number($(this).attr('index'))).prop('selected', 'selected');
						var w = a.width();
						a[0].style.cssText = '';
						if(!!style)a[0].style.cssText += ';'+style;
						a.html('<strong></strong>'+text).css({width:w, 'font-size':''});
						selectControl();
						_this.trigger('change');
						/*
						var objEvt = $._data(_this[0], 'events');
						if(objEvt && objEvt['change'])_this.change();
						*/
						if(!!callback && typeof(callback)=='string'){
							var func;
							eval("func = "+callback);
							callback = func;
						}
						if($.isFunction(callback))callback.call(_this);
						if(!!_this.data('coo.label'))_this.data('coo.label').remove();
						return false;
					});
				}else{
					l = $('<li><span'+(!!style?' style="'+style+'"':'')+(!!cls?' class="'+cls+'"':'')+' title="'+val+'">'+text+'</span></li>');
					list.find('ul').append(l);
					l.find('span').css('color', '#ccc');
				}
			}else{
				text = o.attr('label');
				if(!!!text)text = o.text();
				l = $('<li><strong'+(!!style?' style="'+style+'"':'')+(!!cls?' class="'+cls+'"':'')+'>'+text+'</strong></li>');
				list.find('ul').append(l);
			}
		});
		if(option.length>10){
			var height = list.find('a').height(), padding = list.find('ul').padding();
			list.find('ul').height(padding.top+(height*10)+padding.bottom).parent().hide();
		}else list.hide();
		function selectControlReg(e){
			var e = e||window.event, o = e.target||e.srcElement;
			do{
				if($(o).is(a) || $(o).is(list))return;
				if((/^(html|body)$/i).test(o.tagName)){
					selectControl();
					return;
				};
				o = o.parentNode;
			}while(o.parentNode);
		}
		function selectControl(){
			a.removeClass(focus);
			list.hide().stopScroll(false);
			$(document).off('click', selectControlReg);
		}
	});
};

/*
function ajaxUploadFn(){
	return {
		success : function(json, status){
			if(typeof json.error!='undefined'){
				if(json.error!=0){$.overloadError(json.message);return}
				$.overloadSuccess(json.url[0]);
			}
		},
		error : function(data, status, e){
			$.overloadError('Upload error<br />'+e);
		},
		complete : function(data, status){
			$.overloadSuccess('Upload complete');
		}
	};
}
*/
//AJAX上传, master为非file控件时的对象,不用指定
$.fn.ajaxupload = function(options, master){
	options = $.extend({
		url : '/upload', //上传提交的目标网址
		name : 'filename', //非file控件上传时指定的提交控件名称
		loading : '', //上传中在右边显示的图片路径
		innerFile : $.browser.mobile, //直接在内部生成input:file,为兼容某些浏览器不支持模拟点击
		fileType : ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'], //允许上传文件类型,后缀名,数组或字符串(逗号隔开)
		dataType : 'json', //请求类型
		data : null, //上传时一同提交的数据
		multiple : false, //多文件选择(只支持HTML5浏览器)
		before : null, //上传前执行, 若返回false即终止上传, 接受一个参数:选择的文件数量
		cancel : null, //终止上传后执行
		callback : null, //上传操作完毕后返回的回调函数(函数|对象字面量)
		replace : null //替换上传操作, 例如使用又拍云替换ajaxupload的默认上传, 接受一个参数:file控件
	}, options);
	return this.each(function(){
		var _this = $(this), loading = [], dat = {}, s = {}, result,
		url = _this.attr('url')||options.url, load = _this.attr('loading')||options.loading, dataType = _this.attr('dataType')||options.dataType,
		data = _this.attr('data')||options.data, before = _this.attr('before')||options.before, cancel = _this.attr('cancel')||options.cancel,
		multiple = _this.attr('multiple')||options.multiple, fileType = _this.attr('fileType')||options.fileType,
		callback = _this.attr('callback')||options.callback, replace = _this.attr('replace')||options.replace;
		if(!!!url && !$.isFunction(replace))return true;
		if(!!_this.data('ajaxupload'))return true;
		_this.data('ajaxupload', true);
		if(!_this.is(':file')){
			if(!options.name.length && !$.isFunction(replace))return true;
			if(options.innerFile){
				var width = _this.outerWidth(false), height = _this.outerHeight(false);
				_this.css({position:'relative', width:width, height:height, overflow:'hidden'}).find(':file[name="'+options.name.replace(/(\[|\])/g,'\\$1')+'"]').remove();
				var mul = multiple?'multiple':'', opt = $.extend({}, options, {name:''}),
				file = $('<input type="file" name="'+options.name+'" '+mul+' />');
				_this.append(file);
				file.css({position:'absolute', 'z-index':9999, top:0, right:0, opacity:0, margin:'0', width:width, height:height, 'font-size':height+'px', cursor:'pointer'});
				file.ajaxupload(opt, _this);
			}else{
				_this.onclick(function(){
					$(':file:hidden[name="'+options.name.replace(/(\[|\])/g,'\\$1')+'"]').remove();
					var mul = multiple?'multiple':'', opt = $.extend({}, options, {name:''}),
					file = $('<input type="file" name="'+options.name+'" '+mul+' style="display:none;" />');
					_this.before(file);
					file.ajaxupload(opt, _this);
					file.simclick();
				});
			}
		}else{
			var _ths = _this, isMaster = true;
			if(!$.isArray(fileType))fileType = fileType.split(',');
			if(typeof master!='undefined'){
				_ths = master;
				isMaster = false;
			};
			_this.change(function(){
				if(!this.value.length)return false;
				if(fileType.length && !RegExp('\.('+fileType.join('|')+')$', 'i').test(this.value.toLowerCase())){
					$.overloadError('上传的文件类型必须是'+fileType.join(', ')+'中的一种');
					this.value = '';
					return false;
				};
				var coo = _this.parents('form').data('coo');
				if(coo){
					for(var i in coo){
						if(!_this.coo(coo[i])){
							return false;
						}else{
							if(!!_this.data('coo.label'))_this.data('coo.label').remove();
						}
					}
				}
				if($.isFunction(before)){
					result = before.call(_this, this.files.length);
					if(typeof(result)=='boolean' && !result){
						if($.isFunction(cancel))cancel.call(_this);
						return false;
					}
				}
				if($.isFunction(replace)){
					replace.call(_this, $(this));
					return false;
				}
				if(!!load){
					if(/^[\.#]\w+$/.test(load)){
						loading = $(load);
					}else if(loading.length==0){
						loading = $('<img src="'+load+'" border="0" align="absmiddle" style="margin-left:5px;" />');
						if(_this.data('filestyle')){
							_this.parent().after(loading);
						}else{
							_ths.after(loading);
						}
					}
				}
				if(!!data){
					if($.isPlainObject(data)){
						dat = data;
					}else if($.isFunction(data)){
						dat = data();
					}else{
						eval("s="+data);
						if($.isPlainObject(s)){
							dat = s;
						}else if($.isFunction(s)){
							dat = s();
						}else{
							dat = {};
						}
					}
				};
				$.event.trigger('ajaxStart');
				var id = new Date().getTime(), frameId = 'ajaxUpload_'+id,
				iframe = $('<iframe name="'+frameId+'" id="'+frameId+'" style="position:absolute;top:-9999px;left:-9999px;" src="javascript:void(0)"></iframe>'),
				form = $('<form style="position:absolute;top:-9999px;left:-9999px;" action="" method="post" enctype="multipart/form-data"></form>');
				for(var i in dat)form.append('<input type="hidden" name="'+i+'" value="'+dat[i]+'" />');
				_this.parents('body').append(iframe);
				var io = iframe[0], xml = {}, oldElement = _this,
				newElement = oldElement.clone(true);
				_this.parents('body').append(form);
				oldElement.before(newElement);
				form.append(oldElement);
				$.event.trigger('ajaxSend', [xml, options]);
				if(!!callback){
					if($.isPlainObject(callback)){
						s = callback;
					}else if($.isFunction(callback)){
						s = callback();
					}else{
						eval("s="+callback);
						if($.isPlainObject(s)){
							s = s;
						}else if($.isFunction(s)){
							s = s();
						}else{
							s = {};
						}
					}
				};
				var uploadHttpData = function(r, type){
					var data = !type;
					data = (type=='xml' || data) ? r.responseXML : r.responseText;
					// If the type is 'script', eval it in global context
					if(type=='script')$.globalEval(data);
					// Get the JavaScript object, if JSON is used.
					if(type=='json')eval('data='+data);
					// evaluate scripts within html
					if(type=='html')$('<div></div>').html(data).evalScripts();
					return data;
				},
				uploadCallback = function(){
					try{
						if(io.contentWindow){
							xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
							xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
						}else if(io.contentDocument){
							xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
							xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
						}
					}catch(e){
						//if($.isFunction(s.error))s.error.call(_this, options, xml, null, e);
					}
					if(xml){
						var status;
						try{
							status = 'success';
							var data = uploadHttpData(xml, dataType);
							if($.isFunction(s.success))s.success.call(_ths, data, status);
							$.event.trigger('ajaxSuccess', [xml, options]);
						}catch(e){
							status = 'error';
							if($.isFunction(s.error))s.error.call(_ths, xml, status, e);
						};
						$.event.trigger('ajaxComplete', [xml, options]);
						$.event.trigger('ajaxStop');
						if($.isFunction(s.complete))s.complete.call(_ths, xml, status);
						if(!isMaster && _ths.prev().is(':file'))_ths.prev().remove();
						$(io).off();
						setTimeout(function(){
							try{
								$(io).remove();
								form.remove();
							}catch(e){
								if($.isFunction(s.error))s.error.call(_ths, xml, null, e);
							}
						}, 100);
						xml = null;
					}
					if(loading.length)loading.hide();
				};
				try{
					form.attr('action', url).attr('target', frameId).submit();
				}catch(e){
					//$.handleError(xml, null, e);
					$.overloadError(e);
				};
				$(io).on('load', uploadCallback);
			});
		}
	});
};

//颜色选择器
$.fn.colorpicker = function(options){
	options = $.extend({
		size : 15, //色块大小, 单位像素
		type : '', //big:228色, small:16色, 默认:small
		target : '', //选择颜色后填写颜色的目标元素(为空即代表当前点击对象,null为不填写)
		readonly : true, //可否填写
		over : null, //鼠标移到颜色上执行(接受两个参数:颜色代码,目标对象[jQuery对象])
		out : null, //鼠标移出颜色后执行(一个参数:目标对象[jQuery对象])
		callback : null, //选择后执行(接受两个参数:颜色代码,目标对象[jQuery对象])
		table : [
			['#E53333', '#E56600', '#FF9900', '#64451D', '#DFC5A4', '#FFE500'],
			['#009900', '#006600', '#99BB00', '#B8D100', '#60D978', '#00D5FF'],
			['#337FE5', '#003399', '#4C33E5', '#9933E5', '#CC33E5', '#EE33EE'],
			['#FFFFFF', '#CCCCCC', '#999999', '#666666', '#333333', '#000000']
		],
		bigTable : [
			['#000000', '#CCFFFF', '#CCFFCC', '#CCFF99', '#CCFF66', '#CCFF33', '#CCFF00', '#66FF00', '#66FF33', '#66FF66', '#66FF99', '#66FFCC', '#66FFFF', '#00FFFF', '#00FFCC', '#00FF99', '#00FF66', '#00FF33', '#00FF00'],
			['#333333', '#CCCCFF', '#CCCCCC', '#CCCC99', '#CCCC66', '#CCCC33', '#CCCC00', '#66CC00', '#66CC33', '#66CC66', '#66CC99', '#66CCCC', '#66CCFF', '#00CCFF', '#00CCCC', '#00CC99', '#00CC66', '#00CC33', '#00CC00'],
			['#666666', '#CC99FF', '#CC99CC', '#CC9999', '#CC9966', '#CC9933', '#CC9900', '#669900', '#669933', '#669966', '#669999', '#6699CC', '#6699FF', '#0099FF', '#0099CC', '#009999', '#009966', '#009933', '#009900'],
			['#999999', '#CC66FF', '#CC66CC', '#CC6699', '#CC6666', '#CC6633', '#CC6600', '#666600', '#666633', '#666666', '#666699', '#6666CC', '#6666FF', '#0066FF', '#0066CC', '#006699', '#006666', '#006633', '#006600'],
			['#CCCCCC', '#CC33FF', '#CC33CC', '#CC3399', '#CC3366', '#CC3333', '#CC3300', '#663300', '#663333', '#663366', '#663399', '#6633CC', '#6633FF', '#0033FF', '#0033CC', '#003399', '#003366', '#003333', '#003300'],
			['#FFFFFF', '#CC00FF', '#CC00CC', '#CC0099', '#CC0066', '#CC0033', '#CC0000', '#660000', '#660033', '#660066', '#660099', '#6600CC', '#6600FF', '#0000FF', '#0000CC', '#000099', '#000066', '#000033', '#000000'],
			['#FF0000', '#FF00FF', '#FF00CC', '#FF0099', '#FF0066', '#FF0033', '#FF0000', '#990000', '#990033', '#990066', '#990099', '#9900CC', '#9900FF', '#3300FF', '#3300CC', '#330099', '#330066', '#330033', '#330000'],
			['#00FF00', '#FF33FF', '#FF33CC', '#FF3399', '#FF3366', '#FF3333', '#FF3300', '#993300', '#993333', '#993366', '#993399', '#9933CC', '#9933FF', '#3333FF', '#3333CC', '#333399', '#333366', '#333333', '#333300'],
			['#0000FF', '#FF66FF', '#FF66CC', '#FF6699', '#FF6666', '#FF6633', '#FF6600', '#996600', '#996633', '#996666', '#996699', '#9966CC', '#9966FF', '#3366FF', '#3366CC', '#336699', '#336666', '#336633', '#336600'],
			['#FFFF00', '#FF99FF', '#FF99CC', '#FF9999', '#FF9966', '#FF9933', '#FF9900', '#999900', '#999933', '#999966', '#999999', '#9999CC', '#9999FF', '#3399FF', '#3399CC', '#339999', '#339966', '#339933', '#339900'],
			['#00FFFF', '#FFCCFF', '#FFCCCC', '#FFCC99', '#FFCC66', '#FFCC33', '#FFCC00', '#99CC00', '#99CC33', '#99CC66', '#99CC99', '#99CCCC', '#99CCFF', '#33CCFF', '#33CCCC', '#33CC99', '#33CC66', '#33CC33', '#33CC00'],
			['#000000', '#FF00FF', '#FF00CC', '#FF0099', '#FF0066', '#FF0333', '#FF0000', '#99FF00', '#99FF33', '#99FF66', '#99FF99', '#99FFCC', '#99FFFF', '#33FFFF', '#33FFCC', '#33FF99', '#33FF66', '#33FF33', '#33FF00'],
		]
	}, options);
	return this.each(function(){
		var _this = $(this), el = options.target ? $(options.target) : (!!_this.attr('target')?$(_this.attr('target')):_this),
			mark = _this.attr('id'), ie6 = $.browser.ie6;
		if(!mark){mark = _this.attr('name');_this.attr('id', mark)}
		if(!_this.is('input')){
			if(!!(_this.attr('size')*1)){eval('tmp='+_this.attr('size'));options = $.extend(options, {size:tmp})}
			if(_this.attr('type'))options = $.extend(options, {type:_this.attr('type')});
		};
		el.attr('readonly', options.readonly);
		_this.click(function(e){
			if(!$('#' + mark + '_colorpicker').length){
				var area = $('<div id="' + mark + '_colorpicker" class="colorpickerControl" style="position:absolute;z-index:888;left:0;top:0;font-family:Arial,\'Microsoft YaHei\';"><div style="position:absolute;left:0;top:0;font-size:12px;background:#f5f3e5;border:1px solid #c2ba9d;padding:1px;-moz-box-shadow:0 2px 10px #A0A0A0;-webkit-box-shadow:0 2px 10px #A0A0A0;box-shadow:0 2px 10px #A0A0A0;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;"></div></div>'), div, w, size = options.size, colors = options.type=='big' ? options.bigTable : options.table;
				$('body').append(area);
				div = area.children('div');
				w = colors[0].length*(size+2);
				div.width(w);
				for (var i = 0; i < colors.length; i++) {
					if (colors[i]) {
						var row = $('<div style="padding:1px 0;width:auto;height:'+size+'px;overflow:hidden;"></div>');
						div.append(row);
						for (var j = 0; j < colors[i].length; j++) {
							var cell = $('<div style="float:left;margin:0 1px;display:inline;cursor:pointer;width:'+size+'px;height:'+size+'px;overflow:hidden;background-color:'+colors[i][j]+';" title="'+colors[i][j]+'"></div>');
							row.append(cell);
							cell.click(function(){
								var fn, color = $(this).attr('title');
								if(options.target!=null)el.val(color);
								if($.isFunction(options.callback))options.callback.call(_this, color, el);
								removeControl();
								fn = el.attr('fn');
								if(!!fn){
									var func;
									eval("func = "+fn);
									if($.isFunction(func))func(color, el);
								};
								setTimeout(function(){
									if(el.data('coo.label'))el.data('coo.label').remove();
								}, 0);
							}).hover(function(){
								if($.isFunction(options.over))options.over($(this).attr('title'), el);
							},function(){
								if($.isFunction(options.out))options.out(el);
							});
						};
						row.append('<div tyle="clear:both;font-size:0;width:auto;height:0;overflow:hidden;"></div>');
					}
				}
				if(ie6){
					var iframe = $('<iframe frameborder="0"></iframe>');
					area.append(iframe);
					iframe.css({
						width: area.children('div').outerWidth(false),
						height: area.children('div').outerHeight(false)
					});
				};
				var doc = $.document(),
				position = _this.position(), posl = position.left, post = position.top + _this.outerHeight(false) + 4,
				cw = $.scroll().left + doc.clientWidth, ch = $.scroll().top + doc.clientHeight;
				if(position.left+area.children('div').outerWidth(false)>cw)posl = cw - area.children('div').outerWidth(false);
				var cbottom = post + area.children('div').outerHeight(false),
				ctop = area.children('div').outerHeight(false) + 4;
				if(cbottom>ch && position.top>=ctop)post = position.top - ctop;
				area.css({left:posl, top:post});
				$(document).on('click', removeControlReg);
			}else{
				removeControl();
			}
		});
		function removeControlReg(e){
			var e = e||event, o = e.target||e.srcElement;
			do{
				if(o.id == mark+'_colorpicker' || o.id == mark || o === _this[0])return;
				if((/^(html|body)$/i).test(o.tagName)){
					removeControl();
					return;
				};
				o = o.parentNode;
			}while(o.parentNode);
		}
		function removeControl(){
			$('.colorpickerControl').remove();
			$(document).off('click', removeControlReg);
		}
	});
};

//日期选择器
$.fn.datepicker = function(options){
	options = $.extend({
		initDate : new Date(), //默认选定日期
		parent : 'body', //生成的选择器html代码插入到指定容器里的最后,平板模式无效
		target : '', //选择日期后填写日期的目标元素(不指定即为当前点击对象)
		cls : '', //使用外部class,而不是内置的样式
		just : '', //只显示选择年份或月份(参数只支持两种:year、month)
		next : '', //选择日期后直接跳到下个日期控件,格式为expr
		flat : false, //平板模式,直接显示在调用者的html内,一般与target参数同时使用
		hiddenNavBar : false, //隐藏导航栏
		breakClick : false, //不执行插件默认的点击选择日期操作
		readonly : true, //可否填写
		range : false, //范围选择
		multiple : false, //日期多选,设置后next无效
		showTime : false, //显示时分秒,返回的日期格式(format)需自己设定
		showHour : true, //显示时,需设置showTime
		showMinute : true, //显示分,需设置showTime,showHour
		showSecond : true, //显示秒,需设置showTime,showHour,showMinute
		changeYear : true, //可否更改年份
		changeMonth : true, //可否更改月份
		touchMove : true, //拖曳切换年月
		enText : false, //将所有文字改为英文
		yearText : '年', //一般以英文显示才需修改
		monthText : '月', //同上
		weekText : [], //同上,以星期天起始,留空即使用默认
		minYear : 1949, //最小年份(数值型)(字符型:this为今年|[+-]数字(以今天作为界限))
		maxYear : new Date().getFullYear()+15, //最大年份(数值型)(字符型:this为今年|[+-]数字(以今天作为界限))
		disMonths : '', //禁用月份(逗号隔开)
		disDays : '', //禁用每月的某些日(逗号隔开)
		disWeeks : '', //禁用每月的某些星期(逗号隔开)(格式:0,1,2,3,4,5,6,星期日为0)
		disDates : '', //禁用某些日(逗号隔开)(today:使用今天作为日期)(格式:年-月-日)
		minDate : '', //只能选择该日以后的日期(格式:年-月-日)(today:使用今天作为日期)([+-]数字[y|m|d](以今天作为界限))
		maxDate : '', //只能选择该日以前的日期(格式:年-月-日)(today:使用今天作为日期)([+-]数字[y|m|d](以今天作为界限))
		format : 'yyyy-m-d', //以逗号分隔的日期数组内每个日期的格式,range格式例子:从(#yyyy-m-d)至(#yyyy-m-d)
		date : null, //每个日期生成后执行
		prevMonth : null, //外部切换到上个月的控件
		nextMonth : null, //外部切换到下个月的控件
		prevMonthCallback : null, //点击上个月箭头后执行
		nextMonthCallback : null, //点击下个月箭头后执行
		change : null, //改变年月后执行
		complete : null //生成日期控件后执行
		//自定义函数名 : function(dates){...} //可加入多个自定义函数(只要是函数都会执行),dates参数为[多个日期元素的数组|range两个日期元素的数组]
	}, options);
	return this.each(function(){
		var _this = $(this), body = _this.parents('body');
		_this.on('makepicker', function(){
			var el = options.target ? $(options.target) : (_this.attr('target')?$(_this.attr('target')):_this), mark = el.attr('id'), tmp;
			if(!!!mark){mark = el.attr('name');el.attr('id', mark)}
			if(!!!mark){mark = (new Date).formatDate('yyyymmddhhnnss')+''+$.random(1000, 9999);el.attr('id', mark)}
			_this.data({mark:mark, el:el});
			if(_this.is('input'))_this.addClass('datepicker');
			if(!!_this.attr('parent'))options = $.extend(options, {parent:_this.attr('parent')});
			if(!!_this.attr('cls'))options = $.extend(options, {cls:_this.attr('cls')});
			if(!!_this.attr('just')&&(_this.attr('just')=='year'||_this.attr('just')=='month'))options = $.extend(options, {just:_this.attr('just')});
			if(!!el.attr('@') || !!_this.attr('@'))options = $.extend(options, {next:el.attr('@')||_this.attr('@')});
			if(!!_this.attr('next'))options = $.extend(options, {next:_this.attr('next')});
			if(options.readonly){el.attr('readonly','readonly').css('cursor','default')}else{el.removeAttr('readonly')}
			if($.inArray(_this.attr('hiddenNavBar'),['true','false'])!=-1){eval('tmp='+_this.attr('hiddenNavBar'));options = $.extend(options, {hiddenNavBar:tmp})}
			if($.inArray(_this.attr('breakClick'),['true','false'])!=-1){eval('tmp='+_this.attr('breakClick'));options = $.extend(options, {breakClick:tmp})}
			if($.inArray(_this.attr('range'),['true','false'])!=-1){eval('tmp='+_this.attr('range'));options = $.extend(options, {range:tmp})}
			if(!!_this.attr('multiple'))options = $.extend(options, {multiple:true});
			if($.inArray(_this.attr('showTime'),['true','false'])!=-1){eval('tmp='+_this.attr('showTime'));options = $.extend(options, {showTime:tmp})}
			if($.inArray(_this.attr('showHour'),['true','false'])!=-1){eval('tmp='+_this.attr('showHour'));options = $.extend(options, {showHour:tmp})}
			if($.inArray(_this.attr('showMinute'),['true','false'])!=-1){eval('tmp='+_this.attr('showMinute'));options = $.extend(options, {showMinute:tmp})}
			if($.inArray(_this.attr('showSecond'),['true','false'])!=-1){eval('tmp='+_this.attr('showSecond'));options = $.extend(options, {showSecond:tmp})}
			if($.inArray(_this.attr('changeYear'),['true','false'])!=-1){eval('tmp='+_this.attr('changeYear'));options = $.extend(options, {changeYear:tmp})}
			if($.inArray(_this.attr('changeMonth'),['true','false'])!=-1){eval('tmp='+_this.attr('changeMonth'));options = $.extend(options, {changeMonth:tmp})}
			if($.inArray(_this.attr('touchMove'),['true','false'])!=-1){eval('tmp='+_this.attr('touchMove'));options = $.extend(options, {touchMove:tmp})}
			if($.inArray(_this.attr('enText'),['true','false'])!=-1){eval('tmp='+_this.attr('enText'));options = $.extend(options, {enText:tmp})}
			if(!!(_this.attr('minYear')*1)){eval('tmp='+_this.attr('minYear'));options = $.extend(options, {minYear:tmp})}
			if(!!(_this.attr('maxYear')*1)){eval('tmp='+_this.attr('maxYear'));options = $.extend(options, {maxYear:tmp})}
			if(!!_this.attr('disMonths'))options = $.extend(options, {disMonths:_this.attr('disMonths')});
			if(!!_this.attr('disDays'))options = $.extend(options, {disDays:_this.attr('disDays')});
			if(!!_this.attr('disWeeks'))options = $.extend(options, {disWeeks:_this.attr('disWeeks')});
			if(!!_this.attr('disDates'))options = $.extend(options, {disDates:_this.attr('disDates')});
			if(!!_this.attr('minDate') || !!_this.data('minDate'))options = $.extend(options, {minDate:_this.data('minDate')||_this.attr('minDate')});
			if(!!_this.attr('maxDate'))options = $.extend(options, {maxDate:_this.attr('maxDate')});
			if(!!_this.attr('format'))options = $.extend(options, {format:_this.attr('format')});
			if(options.enText)options = $.extend(options, {yearText:'', monthText:''});
			if(options.multiple)options = $.extend({}, options, {next:''});
			if(options.range && options.format.indexOf('(#')==-1)options = $.extend(options, {format:'(#yyyy-m-d)~(#yyyy-m-d)'});
			if(!!body.data('datepicker.cls'))options = $.extend(options, {cls:body.data('datepicker.cls')});
			if(!!!_this.data('datepicker')){
				_this.data('datepicker', true);
				if(!options.flat){
					_this.click(function(){
						if(!body.find('#' + mark + '_datepicker').length)makepicker();
						else removeControl();
					});
				}
			}
			if(!options.flat){
				if(options.parent!='body'){
					var position = _this.position(), left = position.left, top = position.top + _this.outerHeight(false) + 4;
					_this.attr({left:left, top:top});
				}
			}else{
				makepicker();
			}
			if(!!el.attr('initdate')){
				var val = el.attr('initdate'), datas = val.split(','), arrFormat = [];
				if(options.range){
					var count = 0, fmt = options.format.replace(/\(#([^)]+)\)/g, function(m, format){
						format = (datas[count]).date().formatDate(format);
						count++;
						return format;
					});
					arrFormat.push(fmt);
				}else{
					for(var i=0; i<datas.length; i++){
						var format = (datas[i]).date().formatDate(options.format);
						arrFormat.push(format);
					}
				};
				var val = arrFormat.join(','), fn = el.attr('fn');
				if(el.is('input, textarea'))el.val(val);
				if(el.is('select'))el.find('option').removeAttr('selected').filter('[value='+val+']').attr('selected', 'selected');
				if(!el.is('input, textarea, select'))el.html(val);
				if(!!fn){
					var func;
					eval("func = "+fn);
					if($.isFunction(func))func.call(el, datas);
				}
			}
		});
		_this.trigger('makepicker');
		var mark = _this.data('mark'), el = _this.data('el');
		function makepicker(){
			var mark = _this.data('mark'), el = _this.data('el'), initdate = [], td = new Date(), d,
			childrenDIV = options.cls!='' ? '' : 'background:#f5f3e5;border:1px solid #c2ba9d;color:#2a2a2a;-moz-box-shadow:0 2px 10px #a0a0a0;-webkit-box-shadow:0 2px 10px #a0a0a0;box-shadow:0 2px 10px #a0a0a0;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;width:224px;padding:2px;',
			area = $('<div id="' + mark + '_datepicker" class="datepickerView" style="position:relative;font-family:Arial,\'Microsoft YaHei\';"><div class="childrenDIV" style="position:relative;font-size:12px;height:auto;overflow:hidden;'+childrenDIV+'"><div></div></div></div>');
			if(!options.flat)area.css({position:'absolute', 'z-index':'9999', left:0, top:0}).children('div').css({position:'absolute', left:0, top:0});
			else area.addClass('datepickerViewNotHide');
			if(!!el.attr('initdate')){
				var val = el.attr('initdate'), dates = val.split(',');
				for(var i=0; i<dates.length; i++){
					var date = dates[i].split(' ');
					initdate.push((date[0]).date());
				}
			};
			var option = $.extend(options, {
				obj : el,
				initDate : initdate,
				click : function(dates){
					var fn, arr = [], arrFormat = [];
					if(options.range){
						var count = 0, fmt = options.format.replace(/\(#([^)]+)\)/g, function(m, format){
							format = dates[count].formatDate(format, function(date){
								var showTime = '';
								if(options.showTime){
									if(options.showHour)showTime += ' '+date.hour;
									if(options.showHour && options.showMinute)showTime += ':'+date.minute;
									if(options.showHour && options.showMinute && options.showSecond)showTime += ':'+date.second;
								};
								arr.push(date.year+'-'+date.month+'-'+date.day+showTime);
							});
							count++;
							return format;
						});
						arrFormat.push(fmt);
					}else{
						for(var i=0; i<dates.length; i++){
							var format = dates[i].formatDate(options.format, function(date){
								var showTime = '';
								if(options.showTime){
									if(options.showHour)showTime += ' '+date.hour;
									if(options.showHour && options.showMinute)showTime += ':'+date.minute;
									if(options.showHour && options.showMinute && options.showSecond)showTime += ':'+date.second;
								};
								arr.push(date.year+'-'+date.month+'-'+date.day+showTime);
							});
							arrFormat.push(format);
						}
					};
					var val = arrFormat.join(',');
					if(el.is('input, textarea'))el.val(val);
					if(el.is('select'))el.selected(val);
					if(!el.is('input, textarea, select'))el.html(val);
					if(!!el.data('coo.label'))$(el.data('coo.label')).remove();
					if(!options.flat && !options.multiple)removeControl();
					el.attr('initdate', arr.join(','));
					fn = el.attr('fn');
					if(!!fn){
						var func;
						eval("func = "+fn);
						if($.isFunction(func))func.call(el, dates);
					};
					setTimeout(function(){
						if(el.data('coo.label'))el.data('coo.label').remove();
					}, 0);
					if(!options.multiple && options.next){
						var next = $(options.next), date = dates[0];
						if(!next.length || next.is(el))return;
						next.data('minDate', date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate());
						if(!!!next.data('makedatepicker')){
							next.data('makedatepicker', true);
							if(!!!next.data('datepicker'))next.datepicker($.extend({}, options, {target:''}));
						}
						if(!options.flat){
							next.simclick();
						}else{
							next.trigger('makepicker');
						}
					}
				}
			});
			area.calendar(option);
			if(options.cls!='')area.addClass(options.cls);
			if(options.next && options.flat){
				var next = $(options.next);
				if(next.length && !next.is(el)){
					next.data('makedatepicker', true);
					if(!!!next.data('datepicker'))next.datepicker($.extend({}, options, {target:''}));
					setTimeout(function(){
						if(!!next.data('datepicker')){
							var ar = next.children().eq(0), block;
							ar.children().eq(0).css({opacity:0.4});
							if(!ar.find('.datepickerBlock').length){
								block = $('<div class="datepickerBlock"></div>').css({
									width:ar.width(), height:ar.height(), overflow:'hidden', background:'#fff', opacity:0,
									position:'absolute', 'z-index':1, top:0, left:0
								});
								ar.prepend(block);
							}
						}
					}, 100);
				}
			}
			if(!options.flat){
				var position;
				if(options.parent=='body'){
					body.append(area);
					position = _this.offset();
				}else{
					$(options.parent).append(area);
					position = _this.position();
				};
				var left = _this.attr('left')||position.left, top = _this.attr('top')||(position.top + _this.outerHeight(false) + 4),
					ow = area.children('div').outerWidth(false), oh = area.children('div').outerHeight(false), cw = 0, ch = 0;
				if($.browser.ie6){
					var iframe = $('<iframe frameborder="0"></iframe>');
					area.append(iframe);
					iframe.css({width:ow, height:oh});
				}
				if(options.parent=='body'){
					cw = $.scroll().left + $.window().width, ch = $.scroll().top + $.window().height;
					if(left+ow>cw)left = cw - ow;
					if(top+oh>ch)top = position.top - (oh + 4);
				};
				area.css({left:Number(left), top:Number(top)});
				body.on('click', removeControlReg);
			}else{
				_this.children().remove();
				_this.append(area);
			};
			var yul = area.find('ul:eq(0)'), yli = yul.find('li');
			yli.eq(1).width(yul.width()-yli.eq(0).outerWidth(true)-yli.eq(2).outerWidth(true)-($.browser.msie?($.browser.version==8?2:1):0));
		}
		function removeControlReg(e){
			var e = e||event, o = e.target||e.srcElement;
			do{
				if(o.id == mark+'_datepicker' || o.id == mark || $(o).is(_this))return;
				if((/^(html|body)$/i).test(o.tagName)){
					removeControl();
					return;
				};
				o = o.parentNode;
			}while(o.parentNode);
		}
		function removeControl(){
			body.find('.datepickerView').not('.datepickerViewNotHide').remove();
			body.off('click', removeControlReg);
			//_this.removeAttr('initdate');
		}
	});
};
$.fn.calendar = function(options){
	options = $.extend({
		obj : null,
		initDate : [],
		cls : '',
		just : '',
		hiddenNavBar : false,
		breakClick : false,
		range : false,
		multiple : false,
		showTime : false,
		showHour : true,
		showMinute : true,
		showSecond : true,
		changeYear : true,
		changeMonth : true,
		touchMove : true,
		enText : false,
		yearText : '年',
		monthText : '月',
		weekText : [],
		minYear : 1949,
		maxYear : new Date().getFullYear()+15,
		disMonths : '',
		disDays : '',
		disWeeks : '',
		disDates : '',
		minDate : '',
		maxDate : '',
		format : 'yyyy-m-d',
		click : null,
		date : null,
		prevMonth : null,
		nextMonth : null,
		prevMonthCallback : null,
		nextMonthCallback : null,
		change : null,
		complete : null
	}, options);
	var oriHeight = 0, dates = options.initDate, beginDate = null, endDate = null,
	monthName = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	weekName = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
	weekClass = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
	if(!options.enText){
		monthName = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'];
		weekName = ['日', '一', '二', '三', '四', '五', '六'];
	}
	if($.isArray(options.weekText) && options.weekText.length==7)weekName = options.weekText;
	if(options.range && dates.length==2){beginDate = dates[0]; endDate = dates[1]}
	if(typeof options.minYear=='string'){
		if(options.minYear=='this'){
			options.minYear = new Date().getFullYear();
		}else{
			var tdy = options.minYear.match(/^([\+\-]\d+)$/);
			if(tdy)options.minYear = new Date().getFullYear() + tdy[1]*1;
		}
	}
	if(typeof options.maxYear=='string'){
		if(options.maxYear=='this'){
			options.maxYear = new Date().getFullYear();
		}else{
			var tdy = options.maxYear.match(/^([\+\-]\d+)$/);
			if(tdy)options.maxYear = new Date().getFullYear() + tdy[1]*1;
		}
	}
	function MonthInfo(y, m){
		var monthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
		d = (new Date(y, m, 1));
		d.setDate(1);
		if(d.getDate() == 2) d.setDate(0);
		y += 1900;
		return{
			days : m == 1 ? (((y % 4 == 0) && (y % 100 != 0)) || (y % 400 == 0) ? 29 : 28) : monthDays[m],
			firstDay : d.getDay()
		}
	}
	function InitCalendar(calp, date, position, showYears, showMonths){
		var doc = $.document(), cal = $('<div></div>').css({float:'left'}),
		initdate = new Date(), td = new Date(), obj = $(options.obj), attrDate = obj.attr('initdate'),
		tdYear = td.getFullYear(), tdMonth = td.getMonth(), tdDay = td.getDate(), tdHour = td.getHours(), tdMinute = td.getMinutes(), tdSecond = td.getSeconds(),
		month = MonthInfo(date.getFullYear(), date.getMonth()), minDate = obj.data('minDate')||options.minDate, maxDate = obj.data('maxDate')||options.maxDate,
		minFullDate = new Date(options.minYear, tdMonth, tdDay, tdHour, tdMinute, tdSecond),
		maxFullDate = new Date(options.maxYear, tdMonth, tdDay, tdHour, tdMinute, tdSecond),
		current_start, current_end, current_bgcolor, today_start, today_end, today_bgcolor, normal_start, normal_end, normal_bgcolor,
		hover_start, hover_end, hover_bgcolor, dayUL_disabled = '', dayUL_notcurrent = '';
		if(!!attrDate){
			attrDate = attrDate.split(',');
			attrDate = attrDate[0];
			initdate = attrDate.date();
		}
		if(typeof position!='undefined'){
			var width = calp.parent().width(), height = calp.parent().height(), speed = 300, easing = 'easeout';
			if(!oriHeight){
				if(calp.find('.dayUL').length==3){
					oriHeight = calp.find('.yearUL').outerHeight(true) + calp.find('.dayUL').outerHeight(true)*3;
				}else{
					oriHeight = calp.find('.yearUL').outerHeight(true) + calp.find('.weekUL').outerHeight(true) + calp.find('.dayUL').outerHeight(true)*5;
				}
			};
			calp.css({position:'relative', width:width, height:height, overflow:'hidden'}).parent().animate({height:oriHeight}, 200, function(){
				switch(position){
					case 0:case 'top':
						calp.append(cal);
						calp.children().width(width);
						calp.css({top:0, left:0, height:height*2}).animate({top:-height}, speed, easing, function(){
							cal.css({width:''}).prev().remove();
							calp.css({position:'', top:'', left:'', width:'', height:''}).parent().css({height:'auto'});
							var h = calp.parent().height();
							calp.parent().css({height:oriHeight}).animate({height:h}, 200, function(){
								if($.isFunction(options.change))options.change.call(cal.parent(), date);
							});
						});
						break;
					case 1:case 'right':
						calp.prepend(cal);
						calp.children().width(width);
						calp.css({top:0, left:-width, width:width*2}).animate({left:0}, speed, easing, function(){
							cal.css({width:''}).next().remove();
							calp.css({position:'', top:'', left:'', width:'', height:''}).parent().css({height:'auto'});
							var h = calp.parent().height();
							calp.parent().css({height:oriHeight}).animate({height:h}, 200, function(){
								if($.isFunction(options.change))options.change.call(cal.parent(), date);
							});
						});
						break;
					case 2:case 'bottom':
						calp.prepend(cal);
						calp.children().width(width);
						calp.css({top:-height, left:0, height:height*2}).animate({top:0}, speed, easing, function(){
							cal.css({width:''}).next().remove();
							calp.css({position:'', top:'', left:'', width:'', height:''}).parent().css({height:'auto'});
							var h = calp.parent().height();
							calp.parent().css({height:oriHeight}).animate({height:h}, 200, function(){
								if($.isFunction(options.change))options.change.call(cal.parent(), date);
							});
						});
						break;
					case 3:case 'left':
						calp.append(cal);
						calp.children().width(width);
						calp.css({top:0, left:0, width:width*2}).animate({left:-width}, speed, easing, function(){
							cal.css({width:''}).prev().remove();
							calp.css({position:'', top:'', left:'', width:'', height:''}).parent().css({height:'auto'});
							var h = calp.parent().height();
							calp.parent().css({height:oriHeight}).animate({height:h}, 200, function(){
								if($.isFunction(options.change))options.change.call(cal.parent(), date);
							});
						});
						break;
				}
			});
		}else{
			calp.append(cal);
		}
		if(options.cls==''){
			current_start = '#fbf4c7'; current_end = '#ffed7f'; current_bgcolor = '#fcf3c9';
			today_start = '#a4ef5c'; today_end = '#74b833'; today_bgcolor = '#81c342';
			normal_start = '#6fc22e'; normal_end = '#489909'; normal_bgcolor = '#4da20b';
			hover_start = '#8bcc4b'; hover_end = '#66a627'; hover_bgcolor = '#72b531';
			dayUL_disabled = 'text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;background:#C2DFAB;color:#FFFFF8;border:1px solid #B7D2A7;';
			dayUL_notcurrent = 'text-align:right;margin:0 1px;margin-top:2px;width:30px;height:28px;line-height:28px;';
		}
		if(minDate){
			if(typeof minDate == 'string'){
				if(minDate == 'today'){
					minDate = new Date();
					minDate = new Date(minDate.getFullYear(), minDate.getMonth(), minDate.getDate());
				}else{
					var tdn = new Date(), tdt = minDate.match(/^([\+\-]\d+)(y|m|d)$/);
					if(tdt){
						switch(tdt[2]){
							//case 'y':minDate = new Date(tdn.getTime()+tdt[1]*365*24*3600*1000);break;
							//case 'm':minDate = new Date(tdn.getTime()+tdt[1]*30*24*3600*1000);break;
							//case 'd':minDate = new Date(tdn.getTime()+tdt[1]*24*3600*1000);break;
							case 'y':minDate = new Date(tdn.setFullYear(tdn.getFullYear()+tdt[1]*1));break;
							case 'm':minDate = new Date(tdn.setMonth(tdn.getMonth()+tdt[1]*1));break;
							case 'd':minDate = new Date(tdn.setDate(tdn.getDate()+tdt[1]*1));break;
						}
					}else{
						minDate = minDate.split('-');
						if(minDate.length == 3)minDate = new Date(minDate[0], minDate[1]-1, minDate[2]);
						else minDate = new Date();
					}
				}
			};
			options.minYear = minDate.getFullYear();
			minFullDate = new Date(minDate.getFullYear(), minDate.getMonth(), minDate.getDate());
		}
		if(maxDate){
			if(typeof maxDate == 'string'){
				if(maxDate == 'today'){
					maxDate = new Date();
					maxDate = new Date(maxDate.getFullYear(), maxDate.getMonth(), maxDate.getDate(), 23, 59, 59);
				}else{
					var tdn = new Date(), tdt = maxDate.match(/^([\+\-]\d+)(y|m|d)$/);
					if(tdt){
						switch(tdt[2]){
							case 'y':maxDate = new Date(tdn.setFullYear(tdn.getFullYear()+tdt[1]*1));break;
							case 'm':maxDate = new Date(tdn.setMonth(tdn.getMonth()+tdt[1]*1));break;
							case 'd':maxDate = new Date(tdn.setDate(tdn.getDate()+tdt[1]*1));break;
						}
					}else{
						maxDate = maxDate.split('-');
						if(maxDate.length == 3)maxDate = new Date(maxDate[0], maxDate[1]-1, maxDate[2]);
						else maxDate = new Date();
					}
				}
			};
			options.maxYear = maxDate.getFullYear();
			maxFullDate = new Date(maxDate.getFullYear(), maxDate.getMonth(), maxDate.getDate());
		};
		var just = options.just;
		if(showYears)just = 'year';
		if(showMonths)just = 'month';
		switch(just){
		case 'year':
			cal.attr('type', 'year');
			tdDay = 1;
			var h, ha;
			if($.isDate(attrDate)){
				var d, re = /^(\d{4})/g;
				while( (d=re.exec(attrDate))!=null ){
					initdate = new Date(d[1], tdMonth, tdDay);
					break;
				}
			};
			h = (initdate.getFullYear() - options.minYear) % 12;
			ha = initdate.getFullYear() - h;
			if(showYears)ha = date.getFullYear();
			var hb = (ha+11>options.maxYear) ? options.maxYear : ha+11;
			var yearUL = options.cls!='' ? '' : 'border:1px solid #d4ccb0;background:#f0ede2;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;',
			yearUL_li = options.cls!='' ? '' : 'margin:0 20px;',
			yearUL_li_a = options.cls!='' ? '' : 'color:#2a2a2a;font-weight:bold;text-shadow:0 1px #fff;height:25px;line-height:25px;',
			yearUL_pnl_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'margin-left:-20px;width:20px;height:25px;line-height:25px;';
			yearUL_pnr_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'margin-right:-20px;width:20px;height:25px;line-height:25px;';
			year = $('<ul class="yearUL '+(options.hiddenNavBar?'hidden':'')+'" style="list-style:none;margin:0;padding:0;text-align:center;overflow:hidden;'+yearUL+'"></ul>');
			year.append('<li style="'+yearUL_li+'"><a class="pn pnr" href="javascript:void(0)" cal="nextyears" year="' + ha + '" style="display:block;text-decoration:none;float:right;'+yearUL_pnr_a+'font-family:\'Microsoft YaHei\';text-indent:5px;">》</a><a class="pn pnl" href="javascript:void(0)" cal="prevyears" year="' + ha + '" style="display:block;text-decoration:none;float:left;'+yearUL_pnl_a+'font-family:\'Microsoft YaHei\';text-indent:-7px;">《</a><a class="pc pcy" href="javascript:void(0)" cal="years" style="display:block;width:100%;text-decoration:none;'+yearUL_li_a+'">' + ha + ' - ' + hb + '</a></li>')
			.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
			cal.append(year);
			if(options.minYear>=ha){
				var a = year.find('a[cal="prevyears"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(options.maxYear<=hb){
				var a = year.find('a[cal="nextyears"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(!options.changeYear)year.find('.pn').addClass('disabled');
			else year.find('li a').eq(2).addClass('pc');
			var cs = '';
			if(options.cls==''){
				cs = 'margin:0;padding:0;height:59px;';
				dayUL_disabled += 'width:50px;height:55px;line-height:55px;';
			};
			for(var i = 0; i <= 2; i++){
				var days = $('<ul class="dayUL dayYearUL" style="list-style:none;text-align:center;overflow:hidden;'+cs+'"></ul>');
				for(var j = ha+4*i; j <= ha+3+4*i; j++){
					var unDis = true, css = '', dayUL_li_a = '';
					if((!!!calp.data('curYear') && j==initdate.getFullYear()) || j==calp.data('curYear')){ //current
						if(options.cls==''){
							dayUL_li_a = 'color:#333;padding-right:2px;';
							css = 'color:#333;border:1px solid #dcd18c;background:'+current_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
							css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+current_start+',endColorstr='+current_end+');'; //<=IE9
							css += 'background:-ms-linear-gradient(top,'+current_start+','+current_end+');'; //>IE9
							css += 'background:-moz-linear-gradient(top,'+current_start+','+current_end+');'; //firefox
							css += 'background:-webkit-linear-gradient(top,'+current_start+','+current_end+');'; //safari || chrome
							css += 'background:-o-linear-gradient(top,'+current_start+','+current_end+');'; //opera
							if($.browser.ie6)css += 'background:'+current_bgcolor+';';
						};
						css += '" bg="current" class="current';
						if(j==tdYear)css += '" today="today';
					}else{
						if(options.cls=='')dayUL_li_a = 'color:#fff;padding-right:2px;';
						if(j==tdYear){ //toyear
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;background:'+today_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+today_start+',endColorstr='+today_end+');';
								css += 'background:-ms-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-moz-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-webkit-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-o-linear-gradient(top,'+today_start+','+today_end+');';
								if($.browser.ie6)css += 'background:'+today_bgcolor+';';
							};
							css += '" bg="today" today="today" class="today';
						}else{ //normal
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+normal_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+normal_start+',endColorstr='+normal_end+');';
								css += 'background:-ms-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-moz-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-webkit-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-o-linear-gradient(top,'+normal_start+','+normal_end+');';
								if($.browser.ie6)css += 'background:'+normal_bgcolor+';';
							};
							css += '" class="normal';
						}
					};
					var curd = new Date(j, tdMonth, tdDay);
					if(options.disDates!=''){
						var disDates = options.disDates, dateSplit = disDates.split('-');
						if(dateSplit.length == 3)disDates = dateSplit[0];
						if(disDates == 'today')disDates = tdYear;
						if((','+disDates+',').indexOf(','+j+',')>-1)unDis = false;
					}
					if(options.minDate!=''){
						if(minDate.getFullYear() > curd.getFullYear())unDis = false;
					}
					if(options.maxDate!=''){
						if(maxDate.getFullYear() < curd.getFullYear())unDis = false;
					};
					var format = options.format;
					if(showYears || options.range)format = 'yyyy';
					format = curd.formatDate(format);
					if(j>=options.minYear && j<=options.maxYear && unDis==true){
						days.append('<li style="float:left;overflow:hidden;'+css+'" title="'+format+'"><div year="' + j + '" month="' + tdMonth + '" date="1" style="'+dayUL_li_a+'">' + j + options.yearText + '</div></li>');
					}else{
						days.append('<li class="disabled" style="float:left;overflow:hidden;cursor:default;'+dayUL_disabled+'" title="'+format+'">' + j + options.yearText + '</li>');
					}
				};
				days.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
				cal.append(days);
			};
			break;

		case 'month':
			cal.attr('type', 'month');
			tdDay = 1;
			if(!!attrDate && $.isDate(attrDate)){
				var d, re = /^(\d{4})-(\d{1,2})/g;
				while( (d=re.exec(attrDate))!=null ){
					initdate = new Date(d[1], d[2]-1, tdDay);
					break;
				}
			};
			var yearUL = options.cls!='' ? '' : 'border:1px solid #d4ccb0;background:#f0ede2;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;',
			yearUL_li = options.cls!='' ? '' : 'margin:0 20px;',
			yearUL_li_a = options.cls!='' ? '' : 'color:#2a2a2a;font-weight:bold;text-shadow:0 1px #fff;height:25px;line-height:25px;',
			yearUL_pnl_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'margin-left:-20px;width:20px;height:25px;line-height:25px;';
			yearUL_pnr_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'margin-right:-20px;width:20px;height:25px;line-height:25px;';
			year = $('<ul class="yearUL '+(options.hiddenNavBar?'hidden':'')+'" style="list-style:none;margin:0;padding:0;text-align:center;overflow:hidden;'+yearUL+'"></ul>');
			year.append('<li style="'+yearUL_li+'"><a class="pn pnr" href="javascript:void(0)" cal="nextyear" style="display:block;text-decoration:none;float:right;'+yearUL_pnr_a+'font-family:\'Microsoft YaHei\';text-indent:5px;">》</a><a class="pn pnl" href="javascript:void(0)" cal="prevyear" style="display:block;text-decoration:none;float:left;'+yearUL_pnl_a+'font-family:\'Microsoft YaHei\';text-indent:-7px;">《</a><a class="pc" href="javascript:void(0)" cal="year" year="' + date.getFullYear() + '" style="display:block;width:100%;text-decoration:none;'+yearUL_li_a+'">' + date.getFullYear() + options.yearText + '</a></li>')
			.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
			cal.append(year);
			if(options.minYear>=date.getFullYear()){
				var a = year.find('a[cal="prevyear"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(options.maxYear<=date.getFullYear()){
				var a = year.find('a[cal="nextyear"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(!options.changeYear)year.find('.pn').addClass('disabled');
			else year.find('li a').eq(2).addClass('pc');
			var cs = '';
			if(options.cls==''){
				cs = 'margin:0;padding:0;height:59px;';
				dayUL_disabled += 'width:50px;height:55px;line-height:55px;';
			}
			for(var i = 0; i <= 2; i++){
				var days = $('<ul class="dayUL dayYearUL" style="list-style:none;text-align:center;overflow:hidden;'+cs+'"></ul>');
				for(var j = 1+4*i; j <= 4+4*i; j++){
					var unDis = true, css = '', dayUL_li_a = '';
					if((!!!calp.data('curYear') && !!!calp.data('curMonth') && date.getFullYear()==initdate.getFullYear() && (j-1)==initdate.getMonth()) || (date.getFullYear()==calp.data('curYear') && (j-1)==calp.data('curMonth'))){ //current
						if(options.cls==''){
							dayUL_li_a = 'color:#333;padding-right:2px;';
							css = 'color:#333;border:1px solid #dcd18c;background:'+current_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
							css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+current_start+',endColorstr='+current_end+');'; //<=IE9
							css += 'background:-ms-linear-gradient(top,'+current_start+','+current_end+');'; //>IE9
							css += 'background:-moz-linear-gradient(top,'+current_start+','+current_end+');'; //firefox
							css += 'background:-webkit-linear-gradient(top,'+current_start+','+current_end+');'; //safari || chrome
							css += 'background:-o-linear-gradient(top,'+current_start+','+current_end+');'; //opera
							if($.browser.ie6)css += 'background:'+current_bgcolor+';';
						};
						css += '" bg="current" class="current';
						if(date.getFullYear()==tdYear && j-1==tdMonth)css += '" today="today';
					}else{
						if(options.cls=='')dayUL_li_a = 'color:#fff;padding-right:2px;';
						if(date.getFullYear()==tdYear && j-1==tdMonth){ //tomonth
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;background:'+today_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+today_start+',endColorstr='+today_end+');';
								css += 'background:-ms-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-moz-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-webkit-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-o-linear-gradient(top,'+today_start+','+today_end+');';
								if($.browser.ie6)css += 'background:'+today_bgcolor+';';
							};
							css += '" bg="today" today="today" class="today';
						}else{ //normal
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+normal_bgcolor+';text-align:right;padding-right:2px;margin:0 1px;margin-top:2px;width:50px;height:55px;line-height:55px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+normal_start+',endColorstr='+normal_end+');';
								css += 'background:-ms-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-moz-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-webkit-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-o-linear-gradient(top,'+normal_start+','+normal_end+');';
								if($.browser.ie6)css += 'background:'+normal_bgcolor+';';
							};
							css += '" class="normal';
						}
					};
					var curd = new Date(date.getFullYear(), j, tdDay);
					if(options.disMonths!=''){
						if((','+options.disMonths+',').indexOf(','+j+',')>-1)unDis = false;
					}
					if(options.disDates!=''){
						var disDates = options.disDates, dateSplit = disDates.split('-');
						if(dateSplit.length == 3)disDates = dateSplit[0]+'-'+dateSplit[1];
						if(disDates == 'today')disDates = tdYear+'-'+(tdMonth+1);
						if((','+disDates+',').indexOf(','+date.getFullYear()+'-'+j+',')>-1)unDis = false;
					}
					if(options.minDate!=''){
						if(new Date(minDate.getFullYear(),minDate.getMonth(),1) > new Date(curd.getFullYear(),curd.getMonth(),1))unDis = false;
						minFullDate = new Date(minDate.getFullYear(), minDate.getMonth()+1, minDate.getDate());
					}
					if(options.maxDate!=''){
						if(new Date(maxDate.getFullYear(),maxDate.getMonth(),1) < new Date(curd.getFullYear(),maxDate.getMonth(),1))unDis = false;
						maxFullDate = new Date(maxDate.getFullYear(), maxDate.getMonth()+1, maxDate.getDate());
					};
					var format = options.format;
					if(showMonths || options.range)format = 'yyyy-m';
					var newDat = new Date(date.getFullYear(), j-1, curd.getDate(), curd.getHours(), curd.getMinutes(), curd.getSeconds());
					format = newDat.formatDate(format);
					if(curd>=minFullDate && curd<=maxFullDate && unDis==true){
						days.append('<li style="float:left;overflow:hidden;'+css+'" title="'+format+'"><div year="' + date.getFullYear() + '" month="' + (j-1) + '" date="1" style="'+dayUL_li_a+'">' + monthName[j-1] + '</div></li>');
					}else{
						days.append('<li class="disabled" style="float:left;overflow:hidden;cursor:default;'+dayUL_disabled+'" title="'+format+'">' + monthName[j-1] + '</li>');
					}
				};
				days.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
				cal.append(days);
			};
			break;

		default:
			cal.attr('type', 'day');
			if(!!attrDate && $.isDate(attrDate)){
				var d, re = /^(\d{4})-(\d{1,2})-(\d{1,2})( (\d{1,2}):(\d{1,2}):(\d{1,2}))?$/g;
				while( (d=re.exec(attrDate))!=null ){
					if(typeof d[4]=='undefined'){
						initdate = new Date(d[1], d[2]-1, d[3], 0, 0, 0);
					}else{
						initdate = new Date(d[1], d[2]-1, d[3], d[5], d[6], d[7]);
					}
					break;
				}
			};
			var hc = '';
			if(options.enText)hc = ' - ';
			var yearUL = options.cls!='' ? '' : 'border:1px solid #d4ccb0;background:#f0ede2;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;',
			yearUL_li = options.cls!='' ? '' : 'margin:0 20px;',
			yearUL_li_a = options.cls!='' ? '' : 'color:#2a2a2a;font-weight:bold;text-shadow:0 1px #fff;height:25px;line-height:25px;',
			yearUL_pnl_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'text-shadow:0 1px #fff;margin-left:-20px;width:20px;height:25px;line-height:25px;';
			yearUL_pnr_a = options.cls!='' ? '' : (options.changeYear?'color:#2a2a2a;':'color:#ccc;')+'text-shadow:0 1px #fff;margin-right:-20px;width:20px;height:25px;line-height:25px;';
			year = $('<ul class="yearUL '+(options.hiddenNavBar?'hidden':'')+'" style="list-style:none;margin:0;padding:0;text-align:center;overflow:hidden;'+yearUL+'"></ul>');
			year.append('<li style="'+yearUL_li+'"><a class="pn pnr" href="javascript:void(0)" cal="nextmonth" style="display:block;text-decoration:none;float:right;'+yearUL_pnr_a+'font-family:\'Microsoft YaHei\';text-indent:5px;">》</a><a class="pn pnl" href="javascript:void(0)" cal="prevmonth" style="display:block;float:left;text-decoration:none;float:left;'+yearUL_pnl_a+'font-family:\'Microsoft YaHei\';text-indent:-7px;">《</a><a href="javascript:void(0)" cal="month" month="' + date.getFullYear() + '-' + date.getMonth() + '" style="display:block;width:100%;text-decoration:none;'+yearUL_li_a+'">' + date.getFullYear() + options.yearText + hc + (date.getMonth()+1) + options.monthText + '</a></li>')
			.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
			cal.append(year);
			if(new Date(minFullDate.getFullYear(),minFullDate.getMonth(),1) >= new Date(date.getFullYear(),date.getMonth(),1)){
				var a = year.find('a[cal="prevmonth"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(new Date(maxFullDate.getFullYear(),maxFullDate.getMonth(),1) <= new Date(date.getFullYear(),date.getMonth(),1)){
				var a = year.find('a[cal="nextmonth"]');
				a.addClass('disabled');
				if(options.cls=='')a.css('color','#ccc');
			}
			if(!options.changeMonth)year.find('.pn').addClass('disabled');
			else year.find('li a').eq(2).addClass('pc');
			var cs = '';
			if(options.cls==''){
				cs = 'margin:0;padding:0;';
			};
			var week_li = options.cls!='' ? '' : 'color:#2a2a2a;font-weight:bold;text-shadow:0 1px #fff;padding:0 1px;margin:0 1px;width:28px;height:27px;line-height:27px;',
			week = $('<ul class="weekUL" style="list-style:none;text-align:center;overflow:hidden;'+cs+'"></ul>');
			for(var i = 0; i < 7; i++){
				var worship = weekClass[i];
				week.append('<li class="'+worship+'" style="float:left;overflow:hidden;text-align:center;cursor:default;'+week_li+'">' + weekName[i] + '</li>');
			};
			week.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
			cal.append(week);
			var cs = '';
			if(options.cls==''){
				cs = 'margin:0;padding:0;height:30px;';
				dayUL_disabled += 'width:26px;height:26px;line-height:26px;';
			};
			for(var i = 0; i < 6; i++){
				var days = $('<ul class="dayUL" style="list-style:none;text-align:center;overflow:hidden;'+cs+'"></ul>');
				for(var j = 0; j < 7; j++){
					var d = 7 * i - month.firstDay + j + 1, unDis = true, css = '', dayUL_li_a = '', worship = weekClass[j];
					var inArray = false, everyDate = new Date(date.getFullYear(), date.getMonth(), d, 0, 0, 0);
					if(options.multiple || options.range){
						for(var s=0; s<dates.length; s++){
							if(Date.parse(everyDate) == Date.parse(dates[s])){inArray = true;break;}
						}
					}else{
						inArray = (date.getFullYear()==initdate.getFullYear() && date.getMonth()==initdate.getMonth() && d==initdate.getDate());
					}
					if(inArray){ //current
						if(options.cls==''){
							dayUL_li_a = 'color:#333;padding-right:2px;';
							css = 'color:#333;border:1px solid #dcd18c;text-shadow:0 1px #fff;background:'+current_bgcolor+';text-align:right;margin:0 1px;margin-top:2px;width:28px;height:26px;line-height:26px;';
							css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+current_start+',endColorstr='+current_end+');'; //<=IE9
							css += 'background:-ms-linear-gradient(top,'+current_start+','+current_end+');'; //>IE9
							css += 'background:-moz-linear-gradient(top,'+current_start+','+current_end+');'; //firefox
							css += 'background:-webkit-linear-gradient(top,'+current_start+','+current_end+');'; //safari || chrome
							css += 'background:-o-linear-gradient(top,'+current_start+','+current_end+');'; //opera
							if($.browser.ie6)css += 'background:'+current_bgcolor+';';
						};
						css += '" bg="current" class="current '+worship;
						if(date.getFullYear()==tdYear && date.getMonth()==tdMonth && d==tdDay)css += '" today="today';
					}else{
						if(options.cls=='')dayUL_li_a = 'color:#fff;padding-right:2px;';
						if(date.getFullYear()==tdYear && date.getMonth()==tdMonth && d==tdDay){ //today
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+today_bgcolor+';text-align:right;margin:0 1px;margin-top:2px;width:28px;height:26px;line-height:26px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+today_start+',endColorstr='+today_end+');';
								css += 'background:-ms-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-moz-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-webkit-linear-gradient(top,'+today_start+','+today_end+');';
								css += 'background:-o-linear-gradient(top,'+today_start+','+today_end+');';
								if($.browser.ie6)css += 'background:'+today_bgcolor+';';
							};
							css += '" bg="today" today="today" class="today '+worship;
						}else{ //normal
							if(options.cls==''){
								css = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+normal_bgcolor+';text-align:right;margin:0 1px;margin-top:2px;width:28px;height:26px;line-height:26px;';
								css += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+normal_start+',endColorstr='+normal_end+');';
								css += 'background:-ms-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-moz-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-webkit-linear-gradient(top,'+normal_start+','+normal_end+');';
								css += 'background:-o-linear-gradient(top,'+normal_start+','+normal_end+');';
								if($.browser.ie6)css += 'background:'+normal_bgcolor+';';
							};
							css += '" class="normal '+worship;
						}
					}
					if(d > 0 && d <= month.days){
						var curd = new Date(date.getFullYear(), date.getMonth(), d);
						if(options.disMonths!=''){
							if((','+options.disMonths+',').indexOf(','+(date.getMonth()+1)+',')>-1)unDis = false;
						}
						if(options.disDays!=''){
							if((','+options.disDays+',').indexOf(','+d+',')>-1)unDis = false;
						}
						if(options.disWeeks!=''){
							if((','+options.disWeeks+',').indexOf(','+j+',')>-1)unDis = false;
						}
						if(options.disDates!=''){
							var disDates = options.disDates;
							if(disDates == 'today')disDates = tdYear+'-'+(tdMonth+1)+'-'+tdDay;
							if((','+disDates+',').indexOf(','+date.getFullYear()+'-'+(date.getMonth()+1)+'-'+d+',')>-1)unDis = false;
						}
						if(options.minDate!=''){
							if(minDate > curd)unDis = false;
						}
						if(options.maxDate!=''){
							if(maxDate < curd)unDis = false;
						};
						var format = options.format;
						if(options.range)format = 'yyyy-m-d';
						var newDat = new Date(curd.getFullYear(), curd.getMonth(), curd.getDate(), initdate.getHours(), initdate.getMinutes(), initdate.getSeconds());
						format = newDat.formatDate(format);
						if(curd>=minFullDate && curd<=maxFullDate && unDis==true){
							var li = $('<li style="float:left;overflow:hidden;'+css+'" title="'+format+'" data-title="'+(curd.getFullYear()+'-'+(curd.getMonth()+1)+'-'+curd.getDate())+'"><div year="' + date.getFullYear() + '" month="' + date.getMonth() + '" date="' + d + '"  style="'+dayUL_li_a+'">' + d + '</div></li>');
							days.append(li);
							if($.isFunction(options.date))options.date.call(li, curd);
						}else{
							var li = $('<li class="disabled" style="float:left;cursor:default;overflow:hidden;'+dayUL_disabled+'" title="'+format+'"><span>' + d + '</span></li>');
							days.append(li);
							if($.isFunction(options.date))options.date.call(li, curd);
						}
					}else if(d <= month.days){
						days.append('<li class="notcurrent" style="float:left;overflow:hidden;cursor:default;'+dayUL_notcurrent+'"><span>&nbsp;</span></li>');
					}else{
						break;
					}
				};
				days.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
				cal.append(days);
			}
			if(!cal.find('ul:last').find('li').length)cal.find('ul:last').remove();
			if(options.showTime){
				var cs = '';
				if(options.cls==''){
					cs = 'margin:0;padding:0;margin-top:5px;';
				};
				var hnsUL_li = options.cls!='' ? '' : 'text-shadow:0 1px #fff;',
				hnsUL_li_input = options.cls!='' ? '' : 'width:25px;height:14px;height:16px\\9;border:none;border-bottom:1px solid #ccc;background:transparent;text-align:center;font-size:12px;',
				da = new Date(), T = 'tim_'+da.formatDate('yyyymdhis'),
				hns = $('<ul class="hnsUL" style="list-style:none;text-align:center;'+cs+'"></ul>');
				
				hns.append('<li style="'+hnsUL_li+'"></li>');
				cal.append(hns);
				var hnsInput = '';
				if(options.showHour){
					hnsInput += '<input id="'+T+'_1" type="text" style="'+hnsUL_li_input+'" maxlength="2" value="'+initdate.getHours()+'" />';
				}
				if(options.showHour && options.showMinute){
					hnsInput += ' : <input id="'+T+'_2" type="text" style="'+hnsUL_li_input+'" maxlength="2" value="'+initdate.getMinutes()+'" />';
				}
				if(options.showHour && options.showMinute && options.showSecond){
					hnsInput += ' : <input id="'+T+'_3" type="text" style="'+hnsUL_li_input+'" maxlength="2" value="'+initdate.getSeconds()+'" />';
				};
				if(hnsInput!='')hns.find('li').append(hnsInput);
				hns.find('input').each(function(i){
					$(this).click(function(){
						this.select();
					}).keyup(function(){
						var val = $(this).val(), v = val.substring(0,val.length-1);
						if(/\D+/.test(val))$(this).val(v);
						if(val*1<0)$(this).val(v);
						if(i==0){
							if(val*1>23)$(this).val(v);
						}else{
							if(val*1>59)$(this).val(v);
						}
					});
				});
				hns.find('li').width((100/hns.find('li').length).round(1)+'%');
			};
			if(options.range && dates.length==2)betweenDate();
			break;
		};
		if(options.cls=='')cal.find('.pc:not(.pcy)').hover(function(){
			if($.browser.msie && $.browser.version>9){
				$(this).css('background','-ms-linear-gradient(left, rgba(240,237,226,0), rgb(214,209,189)50%, rgba(240,237,226,0)100%)');
			}else if($.browser.mozilla){
				$(this).css('background','-moz-linear-gradient(left, rgba(240,237,226,0), rgb(214,209,189)50%, rgba(240,237,226,0)100%)');
			}else if($.browser.safari || $.browser.chrome){
				$(this).css('background','-webkit-linear-gradient(left, rgba(240,237,226,0), rgb(214,209,189)50%, rgba(240,237,226,0)100%)');
			}else if($.browser.opera){
				$(this).css('background','-o-linear-gradient(left, rgba(240,237,226,0), rgb(214,209,189)50%, rgba(240,237,226,0)100%)');
			}else{
				$(this).css('background', '#e0dbc7');
			}
		},function(){
			$(this).css('background', '');
		});
		if(options.touchMove)touchMove();
		function changePrevMonth(){
			if(!options.changeMonth)return false;
			if(options.minYear==date.getFullYear() && minFullDate.getMonth()>date.getMonth()-1)return false;
			date.setDate(1);
			date.setMonth(date.getMonth()-1);
			InitCalendar(cal.parent(), date, 'right');
			if($.isFunction(options.prevMonthCallback))setTimeout(function(){options.prevMonthCallback.call(cal.parent(), date)}, 10);
		}
		function changeNextMonth(){
			if(!options.changeMonth)return false;
			if(options.maxYear==date.getFullYear() && maxFullDate.getMonth()<date.getMonth()+1)return false;
			date.setDate(1);
			date.setMonth(date.getMonth()+1);
			InitCalendar(cal.parent(), date, 'left');
			if($.isFunction(options.nextMonthCallback))setTimeout(function(){options.nextMonthCallback.call(cal.parent(), date)}, 10);
		}
		if(options.prevMonth)$(options.prevMonth).off('click').on('click', changePrevMonth);
		if(options.nextMonth)$(options.nextMonth).off('click').on('click', changeNextMonth);
		cal.find('.yearUL a').on('dragstart', function(e){e.preventDefault()})
		cal.find('.yearUL a, .dayUL div').focus(function(){
			this.blur();
		}).hover(function(){
			if(!$(this).attr('cal')){
				if(!$(this).parent().attr('bg')){
					if(options.cls==''){
						if($.browser.ie6){
							$(this).parent().css('background',hover_bgcolor);
						}else if($.browser.ie9){
							$(this).parent().css('filter','progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+hover_start+',endColorstr='+hover_end+')');
						}else if($.browser.msie && $.browser.version>9){
							$(this).parent().css('background','-ms-linear-gradient(top,'+hover_start+','+hover_end+')');
						}else if($.browser.mozilla){
							$(this).parent().css('background','-moz-linear-gradient(top,'+hover_start+','+hover_end+')');
						}else if($.browser.safari || $.browser.chrome){
							$(this).parent().css('background','-webkit-linear-gradient(top,'+hover_start+','+hover_end+')');
						}else if($.browser.opera){
							$(this).parent().css('background','-o-linear-gradient(top,'+hover_start+','+hover_end+')');
						}else{
							$(this).parent().css('background',hover_bgcolor);
						}
					}else{
						$(this).parent().addClass('hover');
					}
				}
			};
		}, function(){
			if(!$(this).attr('cal')){
				if(!$(this).parent().attr('bg')){
					if(options.cls==''){
						if($.browser.ie6){
							$(this).parent().css('background',normal_bgcolor);
						}else if($.browser.ie9){
							$(this).parent().css('filter','progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+normal_start+',endColorstr='+normal_end+')');
						}else if($.browser.msie && $.browser.version>9){
							$(this).parent().css('background','-ms-linear-gradient(top,'+normal_start+','+normal_end+')');
						}else if($.browser.mozilla){
							$(this).parent().css('background','-moz-linear-gradient(top,'+normal_start+','+normal_end+')');
						}else if($.browser.safari || $.browser.chrome){
							$(this).parent().css('background','-webkit-linear-gradient(top,'+normal_start+','+normal_end+')');
						}else if($.browser.opera){
							$(this).parent().css('background','-o-linear-gradient(top,'+normal_start+','+normal_end+')');
						}else{
							$(this).parent().css('background',normal_bgcolor);
						}
					}else{
						$(this).parent().removeClass('hover');
					}
				}
			};
		}).click(function(){
			if($(this).attr('cal') == 'prevyears'){
				if(!options.changeYear)return false;
				var tmpDate = $(this).attr('year')*1-1;
				if(minFullDate.getFullYear()>tmpDate)return false;
				date = new Date($(this).attr('year')*1-12, date.getMonth(), date.getDate());
				InitCalendar(cal.parent(), date, 'right', true);
			}else if($(this).attr('cal') == 'nextyears'){
				if(!options.changeYear)return false;
				var tmpDate = $(this).attr('year')*1+12;
				if(maxFullDate.getFullYear()<tmpDate)return false;
				date = new Date($(this).attr('year')*1+12, date.getMonth(), date.getDate());
				InitCalendar(cal.parent(), date, 'left', true);
			}else if($(this).attr('cal') == 'prevyear'){
				if(!options.changeYear)return false;
				var tmpDate = date.getFullYear()-1;
				if(minFullDate.getFullYear()>tmpDate)return false;
				date.setFullYear(date.getFullYear()-1);
				InitCalendar(cal.parent(), date, 'right', calp.data('selectYear'), calp.data('selectMonth'));
			}else if($(this).attr('cal') == 'nextyear'){
				if(!options.changeYear)return false;
				var tmpDate = date.getFullYear()+1;
				if(maxFullDate.getFullYear()<tmpDate)return false;
				date.setFullYear(date.getFullYear()+1);
				InitCalendar(cal.parent(), date, 'left', calp.data('selectYear'), calp.data('selectMonth'));
			}else if($(this).attr('cal') == 'prevmonth'){
				changePrevMonth();
			}else if($(this).attr('cal') == 'nextmonth'){
				changeNextMonth();
			}else if($(this).attr('cal') == 'years'){
				return false;
			}else if($(this).attr('cal') == 'year'){
				if(!options.changeYear)return false;
				calp.data('curYear', date.getFullYear());
				var h = (date.getFullYear() - options.minYear) % 12, ha = date.getFullYear() - h;
				date.setFullYear(ha);
				calp.data('selectYear', true);
				InitCalendar(cal.parent(), date, 'bottom', true);
			}else if($(this).attr('cal') == 'month'){
				if(!options.changeMonth)return false;
				calp.data('curYear', date.getFullYear());
				calp.data('curMonth', date.getMonth());
				calp.data('selectMonth', true);
				InitCalendar(cal.parent(), date, 'bottom', false, true);
			}else{
				var _this = $(this), newDate = '', current = '', today = '', normal = '';
				if(!!calp.data('selectYear')){
					if(!options.changeYear)return false;
					date.setDate(1);
					date.setFullYear(_this.attr('year'));
					calp.removeData('selectYear');
					InitCalendar(cal.parent(), date, 'top', false, calp.data('selectMonth'));
					calp.removeData('curYear').removeData('curMonth');
					return;
				}
				if(!!calp.data('selectMonth')){
					if(!options.changeMonth)return false;
					date.setDate(1);
					date.setMonth(_this.attr('month'));
					calp.removeData('selectMonth');
					InitCalendar(cal.parent(), date, 'top');
					calp.removeData('curYear').removeData('curMonth');
					return;
				}
				if(options.showTime){
					var inputs = hns.find('input'), hour = 0, minute = 0, second = 0;
					if(options.showHour)hour = inputs.eq(0).val();
					if(options.showHour && options.showMinute)minute = inputs.eq(1).val();
					if(options.showHour && options.showMinute && options.showSecond)second = inputs.eq(2).val();
					newDate = new Date(_this.attr('year'), _this.attr('month'), _this.attr('date'), hour, minute, second);
				}else{
					newDate = new Date(_this.attr('year'), _this.attr('month'), _this.attr('date'), 0, 0, 0);
				}
				if(options.cls==''){
					//current
					current = 'color:#333;border:1px solid #dcd18c;text-shadow:0 1px #fff;background:'+current_bgcolor+';';
					current += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+current_start+',endColorstr='+current_end+');';
					current += 'background:-ms-linear-gradient(top,'+current_start+','+current_end+');';
					current += 'background:-moz-linear-gradient(top,'+current_start+','+current_end+');';
					current += 'background:-webkit-linear-gradient(top,'+current_start+','+current_end+');';
					current += 'background:-o-linear-gradient(top,'+current_start+','+current_end+');';
					if($.browser.ie6)current += 'background:'+current_bgcolor+';';
					//today
					today = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+today_bgcolor+';';
					today += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+today_start+',endColorstr='+today_end+');';
					today += 'background:-ms-linear-gradient(top,'+today_start+','+today_end+');';
					today += 'background:-moz-linear-gradient(top,'+today_start+','+today_end+');';
					today += 'background:-webkit-linear-gradient(top,'+today_start+','+today_end+');';
					today += 'background:-o-linear-gradient(top,'+today_start+','+today_end+');';
					if($.browser.ie6)today += 'background:'+today_bgcolor+';';
					//normal
					normal = 'color:#fff;border:1px solid #327e04;text-shadow:0 -1px #327e04;background:'+normal_bgcolor+';';
					normal += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+normal_start+',endColorstr='+normal_end+');';
					normal += 'background:-ms-linear-gradient(top,'+normal_start+','+normal_end+');';
					normal += 'background:-moz-linear-gradient(top,'+normal_start+','+normal_end+');';
					normal += 'background:-webkit-linear-gradient(top,'+normal_start+','+normal_end+');';
					normal += 'background:-o-linear-gradient(top,'+normal_start+','+normal_end+');';
					if($.browser.ie6)normal += 'background:'+normal_bgcolor+';';
				}
				if(!_this.parent().hasClass('current') || options.range){
					if(options.range){
						if(endDate){
							dates = [];
							beginDate = null;
							endDate = null;
							calp.find('.current').each(function(){
								$(this).removeAttr('bg').removeClass('current').addClass('normal').get(0).style.cssText += ';'+normal;
								if(!!$(this).attr('today'))$(this).attr('bg', 'today').removeClass('normal').addClass('today').get(0).style.cssText += ';'+today;
								$(this).find('div').css('color', options.cls==''?'#fff':'');
							});
							calp.find('.between').each(function(){
								$(this).removeAttr('bg').removeClass('between').addClass('normal').get(0).style.cssText += ';'+normal;
								if(!!$(this).attr('today'))$(this).attr('bg', 'today').removeClass('normal').addClass('today').get(0).style.cssText += ';'+today;
								$(this).find('div').css('color', options.cls==''?'#fff':'');
							});
						}
						if(!beginDate){
							beginDate = newDate;
							dates.push(newDate);
						}else{
							if(Date.parse(beginDate)>Date.parse(newDate)){
								endDate = beginDate;
								beginDate = newDate;
								dates.unshift(newDate);
							}else{
								endDate = newDate;
								dates.push(newDate);
							}
						}
					}else{
						if(!options.multiple){
							var old = calp.find('.current');
							dates = [];
							if(old.length){
								if(options.cls=='')old.find('div').css('color','#fff');
								old.removeAttr('bg').removeClass('current').addClass('normal').get(0).style.cssText += ';'+normal;
								if(!!old.attr('today'))old.attr('bg', 'today').removeClass('normal').addClass('today').get(0).style.cssText += ';'+today;
							}
						};
						dates.push(newDate);
					};
					_this.parent().attr('bg', 'current').removeClass('hover').removeClass('today').removeClass('normal').addClass('current').get(0).style.cssText += ';'+current;
					_this.css('color', options.cls==''?'#333':'');
					if(options.range && dates.length==2)betweenDate();
				}else{
					if(options.range)return;
					if(options.multiple){
						var cur = _this.parent();
						if(options.cls=='')cur.find('div').css('color','#fff');
						cur.removeAttr('bg').removeClass('current').addClass('normal').get(0).style.cssText += ';'+normal;
						if(!!cur.attr('today'))cur.attr('bg', 'today').removeClass('normal').addClass('today').get(0).style.cssText += ';'+today;
						dates.remove(newDate);
					}else{
						dates = [newDate];
					}
				}
				if(options.range){
					if(dates.length==2)washFn();
				}else{
					washFn();
				}
			}
		});
		function washFn(){
			$.each(options, function(key, fn){
				var isBreakKey = (key=='click' && options.breakClick) || key=='date' || key=='prevMonthCallback' || key=='nextMonthCallback' || key=='change' || key=='complete';
				if($.isFunction(fn) && !isBreakKey)fn(dates);
			});
		}
		function betweenDate(){
			var current = cal.find('.current'), between = '';
			if(options.cls==''){
				var between_start = '#ffffff', between_end = '#fffada', between_bgcolor = '#fffada';
				between = 'color:#999;border:1px solid #dcd18c;text-shadow:0 1px #fff;background:'+between_bgcolor+';';
				between += 'filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='+current_start+',endColorstr='+between_end+');';
				between += 'background:-ms-linear-gradient(top,'+between_start+','+between_end+');';
				between += 'background:-moz-linear-gradient(top,'+between_start+','+between_end+');';
				between += 'background:-webkit-linear-gradient(top,'+between_start+','+between_end+');';
				between += 'background:-o-linear-gradient(top,'+between_start+','+between_end+');';
				if($.browser.ie6)between += 'background:'+between_bgcolor+';';
			}
			if(!current.length){
				var unix = Date.parse(cal.find('.dayUL li[title]').eq(0).data('title').date());
				if(unix > Date.parse(beginDate) && unix < Date.parse(endDate)){
					cal.find('.dayUL li[title]').each(function(){
						var obj = $(this);
						obj.attr('bg', 'between').removeClass('hover').removeClass('today').removeClass('normal').addClass('between').get(0).style.cssText += ';'+between;
						if(options.cls==''){
							obj.find('div').css('color', '#999');
						}else{
							obj.find('div').css('color', '');
						}
					});
				}
			}else{
				var li = current.eq(0), obj = li, liDate = li.data('title');
				if(Date.parse(liDate.date()) == Date.parse(endDate)){
					if(Date.parse(liDate.date()) != Date.parse(beginDate)){
						while(obj){
							if(obj.prev().length && !obj.prev().is('.notcurrent')){
								obj = obj.prev();
							}else{
								if(obj.parent().prev().is('.weekUL'))break;
								obj = obj.parent().prev().children().last();
							};
							obj.attr('bg', 'between').removeClass('hover').removeClass('today').removeClass('normal').addClass('between').get(0).style.cssText += ';'+between;
							obj.find('div').css('color', options.cls==''?'#999':'');
						}
					}
				}else{
					while(!obj.is(current.eq(1))){
						if(obj.next().length && obj.next().is('li')){
							obj = obj.next();
						}else{
							if(!obj.parent().next().length)break;
							obj = obj.parent().next().children().eq(0);
						};
						obj.attr('bg', 'between').removeClass('hover').removeClass('today').removeClass('normal').addClass('between').get(0).style.cssText += ';'+between;
						obj.find('div').css('color', options.cls==''?'#999':'');
					}
				}
			}
		}
		function touchMove(){
			if(!options.changeYear || !options.changeMonth)return;
			var type = cal.attr('type'), startX, startY, startDrag = function(e){
				startX = $.touches(e).x;
				startY = $.touches(e).y;
				cal.on('mousemove touchmove', moveDrag);
			},
			moveDrag = function(e){
				e.preventDefault();
				return true;
			},
			endDrag = function(e){
				var a, curX, curY, touchX, touchY;
				cal.off('mousemove touchmove', moveDrag);
				curX = $.touches(e).x;
				curY = $.touches(e).y;
				touchX = curX - startX;
				touchY = curY - startY;
				if(touchX>0 && touchY<15 && touchY>-15){ //prev
					switch(type){
						case 'year':a = cal.find('[cal="prevyears"]');break;
						case 'month':a = cal.find('[cal="prevyear"]');break;
						default:a = cal.find('[cal="prevmonth"]');break;
					}
					if(!a.length)return;
					a.simclick();
				}else if(touchX<0 && touchY<15 && touchY>-15){ //next
					switch(type){
						case 'year':a = cal.find('[cal="nextyears"]');break;
						case 'month':a = cal.find('[cal="nextyear"]');break;
						default:a = cal.find('[cal="nextmonth"]');break;
					}
					if(!a.length)return;
					a.simclick();
				}else if(touchX<15 && touchX>-15 && touchY>0){ //select
					switch(type){
						case 'year':a = cal.find('[cal="years"]');break;
						case 'month':a = cal.find('[cal="year"]');break;
						default:a = cal.find('[cal="month"]');break;
					}
					if(!a.length)return;
					a.simclick();
				}else if(touchX<15 && touchX>-15 && touchY<0){
					if(type!='day'){
						a = cal.find('.current a');
						if(!a.length)return;
						a.simclick();
					}
				}
			};
			cal.unselect().on('mousedown touchstart', startDrag).on('mouseup touchend', endDrag);
		}
	};
	return this.each(function(){
		var cal = $(this).children('div').children('div').eq(0), date = dates.length ? (dates[0]).clone() : new Date();
		InitCalendar(cal, date);
		if($.isFunction(options.complete))setTimeout(function(){options.complete.call(cal.parent(), date)}, 10);
	});
};

//周选择器
$.fn.weekpicker = function(options){
	options = $.extend({
		drag : true, //拖曳滚动
		keydown : true, //箭头键控制滚动
		click : null, //点击日期后执行
		left : null, //点击左按钮后改变周前执行,接受一个参数,当前显示的周的第一天日期
		right : null, //点击右按钮后改变周前执行,接受一个参数,当前显示的周的第一天日期
		change : null, //改变周后执行,接受一个参数,当前显示的周的第一天日期
		complete : null //加载完毕后执行,接受一个参数,当前显示的周的第一天日期
	}, options);
	return this.each(function(){
		$(this).removePlug('weekpicker');
		var _this = $(this), width = $(window).width(), scrolling = false,
		html = '<div class="weekcal">\
					<div class="ym"><div class="year"></div><div class="month"></div></div>\
					<div class="date">\
						<div>\
							<a href="javascript:void(0)" class="left"><span>&lt;</span></a>\
							<font></font>\
							<a href="javascript:void(0)" class="right"><span>&gt;</span></a>\
						</div>\
					</div>\
					<div class="scroll"><div class="part"></div><strong></strong></div>\
				</div>';
		_this.html(html);
		var left = _this.find('.date a:eq(0)'), right = _this.find('.date a:eq(1)'), part = _this.find('.part'),
		firstDay = loopDay(new Date());
		if($.isFunction(options.complete))options.complete.call(_this, firstDay);
		left.click(function(){
			if(scrolling)return;
			scrolling = true;
			if($.isFunction(options.left)){
				var f = options.left.call(_this, firstDay);
				if(typeof f=='boolean' && !f)return;
			};
			firstDay = loopDay(firstDay.DateAdd('d',-7), 0);
			if($.isFunction(options.change))options.change.call(_this, firstDay);
		});
		right.click(function(){
			if(scrolling)return;
			scrolling = true;
			if($.isFunction(options.right)){
				var f = options.right.call(_this, firstDay);
				if(typeof f=='boolean' && !f)return;
			};
			firstDay = loopDay(firstDay.DateAdd('d',7), 1);
			if($.isFunction(options.change))options.change.call(_this, firstDay);
		});
		if(options.keydown)$(document).keydown(function(e){
			var e = e||window.event, code = e.which||e.keyCode;
			if(code==37){
				left.trigger('click');
				if(e.preventDefault)e.preventDefault();
				e.returnValue = false;
				return false;
			}else if(code==39){
				right.trigger('click');
				if(e.preventDefault)e.preventDefault();
				e.returnValue = false;
				return false;
			}
		});
		if(options.drag){
			var d, direction = 0, startDrag = function(e){
				if(scrolling)return;
				if(e.button==2)return;
				d = $.touches(e).x;
				part.on('mousemove', moveDrag).on('mouseup mouseleave', endDrag);
				if(window.addEventListener)part[0].addEventListener('touchmove', moveDrag, true);
			},
			moveDrag = function(e){
				var movepx, curpx;
				curpx = $.touches(e).x;
				movepx = curpx - d;
				direction = movepx<0 ? -1 : 1;
				return true;
			},
			endDrag = function(e){
				part.off('mousemove', moveDrag).off('mouseup mouseleave', endDrag);
				if(window.addEventListener)part[0].removeEventListener('touchmove', moveDrag, true);
				if(direction==1){
					left.click();
				}else if(direction==-1){
					right.click();
				}
			};
			part.unselect().on('mousedown', startDrag);
			if(window.addEventListener){
				part[0].addEventListener('touchstart', startDrag, true);
				part[0].addEventListener('touchend', endDrag, true);
				part[0].addEventListener('touchcancel', endDrag, true);
			}
		}
		function loopDay(date, d){
			var year = _this.find('.year'), month = _this.find('.month'), font = _this.find('.date font'),
			dweek = date.getDay(), d = typeof(d)=='undefined' ? 0 : d,
			weekli = '<ul><li>日</li><li>一</li><li>二</li><li>三</li><li>四</li><li>五</li><li>六</li></ul>',
			field = $('<div class="field"><div class="week"></div><div class="day"></div></div>');
			d ? part.append(field) : part.prepend(field);
			part.width((100*part.find('.field').length)+'%');
			field.width('100%');
			if(!d && part.find('.field').length>1){
				part.find('.field').width('50%');
				part.css({'margin-left':'-100%'}).animate({'margin-left':0}, 1000, 'easeout', function(){
					part.css({width:'100%', 'margin-left':0}).find('.field:last').remove();
					part.find('.field').width('100%');
					scrolling = false;
				});
			}else if(d){
				part.find('.field').width('50%');
				part.animate({'margin-left':'-100%'}, 1000, 'easeout', function(){
					part.css({width:'100%', 'margin-left':0}).find('.field:first').remove();
					part.find('.field').width('100%');
					scrolling = false;
				});
			};
			var week = field.find('.week').html(weekli), day = field.find('.day'), firstDay = date.DateAdd('d',-dweek), w = Math.floor(100/7), html = '';
			for(var i=0; i<7; i++)html += '<li><span>' + firstDay.DateAdd('d',i).getDate() + '</span></li>';
			day.html('<ul>'+html+'</ul>');
			year.html(firstDay.getFullYear()+'年');
			month.animate({ opacity:0 }, 300, function(){
				month.html((firstDay.getMonth()+1).cn()+'月').animate({ opacity:1 }, 300);
			});
			font.animate({ opacity:0 }, 300, function(){
				font.html(firstDay.weekNO().cn()+'周').animate({ opacity:1 }, 300);
			});
			week.find('li').width(w+'%');
			day.find('li').width(w+'%').click(function(){
				if($.isFunction(options.click))options.click.call($(this));
			});
			return firstDay;
		}
	});
};

//显示菜单
$.fn.showmenu = function(options){
	if(typeof options=='undefined'){
		var operateControl = $(this).data('operateControl');
		if(!!operateControl && $.isFunction(operateControl))operateControl();
		return;
	};
	options = $.extend({
		html : '', //显示菜单的内容, string|对象
		cls : '', //显示菜单的样式
		effect : '', //显示|关闭效果, 'opacity'|'height'
		arrow : true, //显示箭头
		fixArrowCss : null, //一个参数[0:箭头在上面(菜单在下面)|1:箭头在下面(菜单在上面)], this代表箭头(eq(0):背面箭头,eq(1):表面箭头)
		hideEle : null, //绑定关闭菜单的对象, string|对象
		on : null, //显示后执行, this代表菜单
		off : null //关闭后执行, this代表菜单
	}, options);
	if(!options.html)return;
	return this.each(function(){
		var _this = $(this), list = $('<div></div>').addClass(options.cls).css({position:'absolute', 'z-index':555, display:'none', float:'left'});
		$(document.body).append(list);
		var object = $(options.html);
		if(object.parent().length){
			list.append(object);
			object.show();
		}else{
			list.html(object);
		}
		if(options.arrow){
			var arrow = '<em style="position:absolute;z-index:1;display:block;border-style:solid;border-color:#999 transparent;"></em>\
						<em style="position:absolute;z-index:2;display:block;border-style:solid;border-color:#fff transparent;"></em>';
			list.append(arrow);
		};
		$.registControl({
			menu : list,
			partner : _this,
			outside : function(){
				switch(options.effect){
					case 'opacity':list.fadeOut(200);break;
					case 'height':list.slideUp(200);break;
					default:list.hide();break;
				}
				if($.isFunction(options.off))options.off.call(list);
			}
		});
		_this.onclick(function(){
			if(!list.is(':visible')){
				var clientWidth = $.window().width, clientHeight = $.window().height,
				scrollLeft = $.scroll().left, scrollTop = $.scroll().top,
				position = _this.position(), left = 0, top = 0;
				if(position.left+list.outerWidth(false)>scrollLeft+clientWidth){
					left = position.left + _this.outerWidth(false) - list.outerWidth(true);
					list.children('em').css({left:'', right:30});
				}else{
					left = position.left;
					list.children('em').css({left:30, right:''});
				}
				if(position.top+_this.outerHeight(false)+list.outerHeight(true)>scrollTop+clientHeight){
					top = position.top - list.outerHeight(true);
					var em = list.children('em').css({'border-width':'6px 6px 0 6px', top:(list.outerHeight(false)-2)+'px'});
					em.eq(0).css({top:(list.outerHeight(false)-1)+'px'});
					if($.isFunction(options.fixArrowCss))options.fixArrowCss.call(em, 1);
				}else{
					top = position.top + _this.outerHeight(false);
					var em = list.children('em').css({'border-width':'0 6px 6px 6px', top:-5});
					em.eq(0).css({top:-6});
					if($.isFunction(options.fixArrowCss))options.fixArrowCss.call(em, 0);
				};
				list.css({left:left, top:top});
				switch(options.effect){
					case 'opacity':list.fadeIn(200);break;
					case 'height':list.slideDown(200);break;
					default:list.show();break;
				}
				if($.isFunction(options.on))options.on.call(list);
			}else{
				$.registControl(list);
			}
		});
		if(options.hideEle)$(options.hideEle).onclick(function(){
			$.registControl(list);
		});
	});
};

//延迟关闭菜单
$.fn.delayhidden = function(options){
	options = $.extend({
		list : '', //菜单, string|对象|function
		delay : 150, //延迟时间
		on : null, //鼠标移入后执行
		off : null //鼠标移出后执行
	}, options);
	return this.each(function(){
		var _this = $(this), list = $(_this.attr('list'));
		if(!list.length)list = $.isFunction(options.list) ? options.list.call(_this) : $(options.list);
		if(!list.length)return true;
		_this.add(list).hover(function(e){
			setTimeout(function(){
				var delayer = _this.data('delayer');
				clearTimeout(delayer);
				_this.removeData('delayer');
				list.show();
				if($.isFunction(options.on))options.on.call(_this, e);
			}, 0);
		}, function(e){
			var delayer = setTimeout(function(){
				list.hide();
				if($.isFunction(options.off))options.off.call(_this, e);
			}, options.delay);
			_this.data('delayer', delayer);
		});
	});
};

//绑定点击document.body执行操作
$.fn.control = function(options){
	if(!$.isPlainObject(options)){
		if($.isFunction(this.data('hide')))this.data('hide').call(this);
		return this;
	};
	options = $.extend({
		expr : '', //点击不会隐藏的对象
		show : null, //显示后执行
		hide : null //关闭后执行
	}, options);
	var ths = this;
	$(document).on('click', operateControlReg);
	function operateControlReg(e){
		var e = e||window.event, o = e.target||e.srcElement;
		do{
			if(options.expr.length && $(o).is(options.expr))return;
			if((/^(html|body)$/i).test(o.tagName)){
				operateControl();
				return;
			};
			o = o.parentNode;
		}while(o.parentNode);
	}
	function operateControl(){
		if($.isFunction(options.hide))options.hide.call(ths);
	};
	return this.each(function(){
		var _this = $(this);
		if(!!_this.data('control'))return true;
		_this.data('control', true).data('hide', options.hide);
		if($.isFunction(options.show))options.show.call(_this);
	});
};

//图片展示
$.fn.lightbox = function(options){
	var options = $.extend({
		auto : 0, //自动播放
		nobg : false, //没有背景
		bgopacity : 0.6, //背景透明度
		bgclose : true, //点击背景关闭
		animatebg : true, //动画显示背景
		zoom : true, //自动缩放(适配窗口)
		margin : 30, //距离窗口边缘(自动缩放有效)
		after : null //显示后执行
	}, options), _this = this, count = this.length, images = [], idx = 0, slides = null;
	function showBox(){
		var window = $.window(), isLoading = false, ph, pv, overlay = $('.lightbox_bg'), box = $('.lightbox_box');
		if($.browser.ie6)$('select:visible').attr('show','yes').css('visibility','hidden');
		if(!options.nobg){
			if(!overlay.length){
				overlay = $('<div class="lightbox_bg"></div>');
				$(document.body).append(overlay);
				overlay.css({opacity:options.bgopacity, width:'100%', height:'100%'});
				if(options.animatebg)overlay.css({opacity:0}).animate({opacity:options.bgopacity}, 200);
				if(options.bgclose)overlay.click(closeBox);
			}
		}
		if(!box.length){
			box = $('<div class="lightbox_box">\
						<div class="box"><span></span><div class="prev"></div><div class="next"></div></div>\
						<div class="area"><div class="close"></div><div class="font"><div class="title"></div><div class="num"></div></div><div class="clear"></div></div>\
					</div>');
			$(document.body).append(box);
			box.find('.box span').show().height(box.find('.box').height());
			box.css({left:(window.width-box.outerWidth(false))/2, top:(window.height-box.outerHeight(false))/2}).find('.box span').hide();
			ph = box.padding().left + box.padding().right; pv = box.padding().top + box.padding().bottom;
			if(count==1)box.find('.prev, .next').hide();
			box.find('.prev').hover(function(){$(this).addClass('prev_x')},function(){$(this).removeClass('prev_x')}).click(function(){
				if(isLoading)return;
				if(idx-1<0)return;
				isLoading = true;
				clearTimeout(slides); slides = null;
				box.find('.prev, .next').hide();
				box.stop(true, false).find('.box img').stop(true, false);
				var ieapt = 0, iepv = 0;
				if($.browser.msie){
					ieapt = box.find('.area').padding().top;
					iepv = pv;
				};
				box.animate({height:box.height()+iepv+ieapt-box.find('.area').outerHeight(false)}, 500, function(){
					box.find('.box img').animate({opacity:0}, 300, function(){
						box.find('.box img').remove();
						idx--;
						loadsrc(idx,1);
					});
				});
			});
			box.find('.next').hover(function(){$(this).addClass('next_x')},function(){$(this).removeClass('next_x')}).click(function(){
				if(isLoading)return;
				if(idx+1>count-1)return;
				isLoading = true;
				clearTimeout(slides); slides = null;
				box.find('.prev, .next').hide();
				box.stop(true, false).find('.box img').stop(true, false);
				var ieapt = 0, iepv = 0;
				if($.browser.msie){
					ieapt = box.find('.area').padding().top;
					iepv = pv;
				};
				box.animate({height:box.height()+iepv+ieapt-box.find('.area').outerHeight(false)}, 500, function(){
					box.find('.box img').animate({opacity:0}, 300, function(){
						box.find('.box img').remove();
						idx++;
						loadsrc(idx);
					});
				});
			});
			box.find('.close').click(closeBox);
		}else{
			ph = box.padding().left + box.padding().right;
			pv = box.padding().top + box.padding().bottom;
			box.find('.box img').remove();
		}
		if(count>1)$(document).on('keydown', keyChange);
		else $(document).off('keydown', keyChange);
		$(document).on('keydown', keyClose);
		loadsrc(idx);
		function closeBox(){
			clearTimeout(slides); slides = null;
			$('.lightbox_box').stop(true, false).find('img').stop(true, false);
			if(options.animatebg){
				$('.lightbox_bg').animate({opacity:0}, 200, function(){
					$('.lightbox_bg').remove();
				});
			}else{
				$('.lightbox_bg').remove();
			};
			$('.lightbox_box, .lightbox_bottom').remove();
			if($.browser.ie6)$('select[show=yes]').css('visibility','');
			if(count>1)$(document).off('keydown', keyChange);
			$(document).off('keydown', keyClose);
		}
		function keyChange(e){
			var e = e||window.event, code = e.which||e.keyCode;
			if(code==37 || code==80){
				box.find('.prev').simclick();
			}
			if(code==39 || code==78){
				box.find('.next').simclick();
			}
		}
		function keyClose(e){
			var e = e||window.event, code = e.which||e.keyCode;
			if(code==88)closeBox();
		}
		function loadsrc(index, dir){
			box.find('.prev, .next').hide();
			box.find('.box span').show().height(box.find('.box').height());
			var img = new Image(), src = images[index]; img.src = src;
			if($.browser.msie){
				if(img.readyState=="complete")anisrc();
				else img.onreadystatechange = function(){
					if(img.readyState=="complete")anisrc();
				}
			}else{
				$(img).on('load', anisrc);
			}
			function anisrc(){
				box.find('.box span').hide();
				var ow = img.width, oh = img.height, w = ow, h = oh, l, t,
					title = _this.eq(index).attr('alt')||_this.eq(index).attr('title'), areaH, margin = options.margin;
				img = null;
				if(!!!title){
					box.find('.area .title').hide();
				}else{
					box.find('.area .title').html(title).show();
				}
				if(count==1){
					box.find('.area .num').hide();
				}else{
					box.find('.area .num').html('IMAGE '+(index+1)+'/'+count);
				};
				areaH = box.find('.area').outerHeight(false);
				box.find('.area').hide();
				if(w>Number(window.width-margin*2-ph)){
					w = Number(window.width-margin*2-ph);
					h = Number((oh*w)/ow);
				}
				if(options.zoom && h>Number(window.height-margin*2-pv-areaH)){
					h = Number(window.height-margin*2-pv-areaH);
					w = Number((ow*h)/oh);
				};
				var m = $('<img src="'+src+'" width="'+w+'" height="'+h+'" border="0" />'), ieapt = 0, ieph = 0, iepv = 0;
				box.find('.box').prepend(m);
				box.find('.box img').on('dragstart', function(e){e.preventDefault()});
				m.css({opacity:0});
				l = Number((window.width-(w+ph))/2);
				if(options.zoom)t = Number((window.height-(h+pv+areaH))/2);
				else t = margin;
				if($.browser.msie){
					ieapt = box.find('.area').padding().top;
					ieph = ph;
					iepv = pv;
				}
				if(!options.zoom){
					var bottom = $('<div class="lightbox_bottom"></div>');
					$(document.body).append(bottom);
					bottom.css({width:1, height:margin, left:l, top:h+pv+ieapt+areaH+t});
				};
				box.animate({width:w+ieph, height:h+iepv, left:l, top:t}, 500, function(){
					if(!options.zoom && !options.nobg){
						var window = $.window();
						overlay.css({width:window.width, height:window.height});
					};
					box.find('.box').css({width:w, height:h});
					m.animate({opacity:1}, 300, function(){
						box.find('.area').show();
						if($.browser.msie)box.height(box.outerHeight(true)+pv);
						box.animate({height:h+ieapt+areaH}, 500, function(){
							isLoading = false;
							if(count>1){
								if(idx>0)box.find('.prev').show();
								if(idx<count-1)box.find('.next').show();
								box.find('.prev, .next').height(h);
							}
							if($.isFunction(options.after))options.after.call(_this, index, src);
							if(options.auto){
								slides = setTimeout(function(){
									isLoading = true;
									clearTimeout(slides); slides = null;
									if(dir){
										box.find('.prev, .next').hide();
										box.animate({height:box.height()+iepv+ieapt-box.find('.area').outerHeight(false)}, 500, function(){
											box.find('.box img').animate({opacity:0}, 300, function(){
												box.find('.box img').remove();
												idx--;
												if(idx<0)idx = count - 1;
												loadsrc(idx, 1);
											});
										});
									}else{
										box.find('.prev, .next').hide();
										box.animate({height:box.height()+iepv+ieapt-box.find('.area').outerHeight(false)}, 500, function(){
											box.find('.box img').animate({opacity:0}, 300, function(){
												box.find('.box img').remove();
												idx++;
												if(idx>count-1)idx = 0;
												loadsrc(idx);
											});
										});
									}
								}, options.auto);
							}
						});
					});
				});
			}
		}
	};
	return this.each(function(i){
		var _ths = $(this), url = _ths.attr('src')||_ths.attr('href');
		if(!!_ths.data('lightbox'))return true;
		_ths.data('lightbox', true);
		images.push(url);
		_ths.attr('i', i).click(function(e){
			idx = Number($(this).attr('i'));
			showBox();
			return false;
		});
	});
};

//分页滚动
$.fn.touchmove = function(options){
	options = $.extend({
		list : null, //滚动列表
		type : 0, //切换效果, 0:滚动切换, 1:渐显切换
		index : 0, //默认显示
		dir : 0, //拖拽(滚动)方向, 水平[0|x|left|right], 垂直[1|y|top|bottom]
		visible : 1, //显示个数, [visible<scroll ? scroll : visible]
		scroll : 1, //滚动个数
		mouseWheel : false, //使用鼠标滚轮
		autoWH : true, //自动设置宽高, 为了一页只显示一个列表元素
		autoW : true, //自动设置宽, autoWH为true时无效
		autoH : true, //自动设置高, autoWH为true时无效
		title : '', //显示list的title为标题的标题类名, 留空即不显示
		opacity : 0.7, //标题容器透明度
		titleAnimate : 'move', //标题容器显示动画, [move|opacity]
		hide : true, //标题是否隐藏(产生动画显示,否则一直显示)
		keydown : false, //箭头键控制滚动分页列表, [上:第一页|下:最后一页|左:上一页|右:下一页]
		prev : '', //滚动到上一个分页列表的expr按钮
		next : '', //滚动到下一个分页列表的expr按钮
		disprev : '', //已经没有上一分页即增加这个样式, unlimit:false 时有效
		disnext : '', //已经没有下一分页即增加这个样式, unlimit:false 时有效
		pager : '', //存放分页按钮的容器的expr, 留空即不显示
		curpager : 'this', //当前分页按钮类名
		pagerText : [], //按钮文字,为空即使用数字,若使用,元素数量必须与list数量相同
		autoPagerWH : true, //按钮容器自动宽高
		autoPager : true, //只有一个列表时自动隐藏按钮容器
		offset : '', //按钮容器位置, [left|center|right]
		offsetW : 10, //按钮容器距离左右边
		offsetH : 10, //按钮容器距离上下边
		section : true, //分页滚动
		act : 'click', //分页按钮的操作方式
		easing : 'easeout', //切换效果, 可使用 $.easing 扩展
		speed : 500, //切换速度
		auto : 0, //自动切换速度(0:不自动切换), auto==speed无限滚动
		autoWait : 0, //自动滚动前停留
		progress : '', //自动切换时在容器下面显示时间动画的样式, 为空即不显示
		progressPager : false, //自动切换时分页按钮显示时间动画
		unlimit : false, //无限滚动
		hoverStop : true, //自动切换时鼠标移到停止滚动
		drag : false, //可否拖拽
		bounces : true, //回弹效果
		stopBounces : false, //终止屏幕拖动, 使用后页面不能上下拖动动
		pagerAction : null, //分页按钮操作时执行
		beforeForLast : null, //滚动前执行, this:前一次的li对象
		before : null, //滚动前执行
		move : null, //滚动时执行
		afterLeft : null, //向左滚动后执行
		afterRight : null, //向右滚动后执行
		after : null, //滚动停止后执行
		complete : null //脚本执行完成后执行
	}, options);
	return this.each(function(){
		$(this).stopBounces(true).removePlug('touchmove');
		var _this = $(this), width = _this.width(), height = _this.height(), list = options.list?_this.find(options.list):_this.find('li'), count = list.length, wrapper, title,
		pager = [], index = -Math.abs(options.index), indexLast = null, hovering = false, moved = false, direction = 0, startD = 0, d, red, lw, lh, time = 0, touchpx, lastX = 0, lastY = 0,
		dirs = {'0':'x', 'left':'x', 'right':'x', '1':'y', 'top':'y', 'bottom':'y'}, dir = dirs[options.dir] ? dirs[options.dir] : options.dir, progress = null,
		scroll = options.scroll, visible = options.visible<scroll ? scroll : options.visible, draging = false, moving = false, auto = 0, autoHandle = null, autoWaitHandle = null;
		if(count<=0)return true;
		if(options.index<0)index = 0;
		if(Math.abs(options.index)>=count)index = -(count-1);
		_this.css({overflow:'hidden'}).data({width:width, height:height});
		if(_this.css('position')=='static')_this.css({position:'relative'});
		if(!list.parent().is('ul'))list.wrapAll('<div></div>');
		wrapper = list.parent().css({position:'relative', overflow:'hidden'});
		var padding = list.padding(), margin = list.margin();
		if(options.type==0){
			if(dir=='x'){
				wrapper.css({left:(options.unlimit?index-scroll:index)*(width/visible), top:0, width:(width/visible)*count});
				lw = (width/visible) - padding.left - padding.right - margin.left - margin.right;
				lh = height - padding.top - padding.bottom;
			}else{
				wrapper.css({left:0, top:(options.unlimit?index-scroll:index)*(height/visible), height:(height/visible)*count});
				lw = width - padding.left - padding.right;
				lh = (height/visible) - padding.top - padding.bottom - margin.top - margin.bottom;
			};
			if(options.autoWH)list.css({width:lw, height:lh});
			else{
				if(options.autoW)list.width(lw);
				if(options.autoH)list.height(lh);
			};
			list.css({float:'left'}).each(function(){
				if(!!$(this).attr('title')){
					$(this).data('title', $(this).attr('title'));
					$(this).removeAttr('title');
				}
			});
			if(options.unlimit){
				wrapper.prepend(list.last().clone()).append(list.eq(0).clone());
				if(dir=='x'){
					wrapper.width(wrapper.width()+(width/visible)*2);
				}else{
					wrapper.height(wrapper.height()+(height/visible)*2);
				}
			}
			if(dir=='x'){
				if(wrapper.width()==width)count = 1;
			}else{
				if(wrapper.height()==height)count = 1;
			}
		}else{
			wrapper.css({height:height});
			list.css({display:'none',position:'absolute',top:0,left:0}).eq(Math.abs(index)).css({display:'','z-index':505});
			list.each(function(){
				if(!!$(this).attr('title')){
					$(this).data('title', $(this).attr('title'));
					$(this).removeAttr('title');
				}
			});
		}
		if(options.title!=''){
			_this.find('.'+options.title).remove();
			title = $('<div></div>').addClass(options.title);
			wrapper.after(title);
			var tpadding = title.padding();
			title.css({
				position : 'absolute',
				'z-index' : 555,
				left : 0,
				top : height,
				width : width - tpadding.left - tpadding.right,
				opacity : options.opacity
			});
		}
		if(options.progress!=''){
			progress = $('<div></div>').addClass(options.progress);
			wrapper.after(progress);
			progress.css({
				position : 'absolute',
				'z-index' : 556,
				left : 0,
				width : 0,
				overflow : 'hidden'
			});
		}
		if(options.pager!='')pager = $(options.pager);
		if(pager.length){
			if(!pager.find('a').length){
				for(var i=0; i<count; i++){
					var text = options.pagerText.length ? options.pagerText[i] : i+1;
					if(i==Math.abs(index))pager.append('<a href="javascript:void(0)" class="'+options.curpager+'"><span></span><font>'+text+'</font></a>');
					else pager.append('<a href="javascript:void(0)"><span></span><font>'+text+'</font></a>');
				}
			}
			if(options.autoPagerWH){
				setTimeout(function(){
					var tm = 0, pagerW = 0, pagerH = 0;
					pager.find('a').each(function(){
						pagerW += $(this).outerWidth(true);
						tm = $(this).outerHeight(true);
						pagerH = tm>pagerH ? tm : pagerH;
					});
					pager.css({width:pagerW, height:pagerH});
					if(options.offset!=''){
						var offsetW = options.offsetW||0, offsetH = options.offsetH||0, uw = 0, uh = height - pagerH - offsetH;
						switch(options.offset){
							case 'left':uw = offsetW;break;
							case 'center':uw = Number((width-pagerW)/2);break;
							default:uw = width - pagerW - offsetW;
						}
						pager.css({position:'absolute', 'z-index':10, left:uw, top:uh});
					}
				}, 100);
			}
		}
		if(options.autoPager && count==1)pager.hide();
		var tit = list.eq(Math.abs(index)).data('title');
		if(options.title!='' && !!tit)title.html(tit).animate({top:height-title.outerHeight(false)}, 200);
		var clearEvent = function(){},
		startDrag = function(e){
			if(e.button>0)return;
			beforeMove();
			lastX = 0;
			lastY = 0;
			moved = false;
			moving = false;
			time = new Date().getTime();
			if(dir=='x'){
				startD = wrapper.position().left;
				d = $.touches(e).x;
				red = $.touches(e).y;
			}else{
				startD = wrapper.position().top;
				d = $.touches(e).y;
				red = $.touches(e).x;
			};
			wrapper.stop(true, false).on('mousemove', moveDrag).css('cursor', 'move');
			if(window.addEventListener)wrapper[0].addEventListener('touchmove', moveDrag, true);
			draging = true;
		},
		moveDrag = function(e){
			if(!!_this.data('stopMove'))return;
			var movepx, curpx, recurpx;
			if(options.stopBounces)e.preventDefault();
			moved = true;
			direction = 0;
			if(dir=='x'){
				curpx = $.touches(e).x;
				if(!moving){
					recurpx = $.touches(e).y;
					if(recurpx>red+5 || recurpx<red-5)return true;
					else e.preventDefault();
				};
				touchpx = movepx = lastX = curpx - d;
				if(movepx>0 && movepx>=(width/5))direction = 1;
				else if(movepx<0 && Math.abs(movepx)>=(width/5))direction = -1;
			}else{
				e.preventDefault();
				curpx = $.touches(e).y;
				if(!moving){
					recurpx = $.touches(e).x;
					if(recurpx>red+5 || recurpx<red-5)return true;
				};
				touchpx = movepx = lastY = curpx - d;
				if(movepx>0 && movepx>=(height/5))direction = 1;
				else if(movepx<0 && Math.abs(movepx)>=(height/5))direction = -1;
			};
			moving = true;
			if( (index>=0 && movepx>0) || ((Math.abs(index)+visible)>=count && movepx<0) )movepx *= 0.2;
			if( !options.bounces && ((index>=0 && movepx>0) || ((Math.abs(index)+visible)>=count && movepx<0)) )movepx = 0;
			if(dir=='x')wrapper.css('left', startD+movepx);
			else wrapper.css('top', startD+movepx);
			if($.isFunction(options.move))options.move.call(list.eq(Math.abs(index)), Math.abs(index));
			return true;
		},
		endDrag = function(e){
			if(e.button>0)return;
			if(draging){
				wrapper.off('mousemove', moveDrag).css('cursor', '');
				if(window.addEventListener)wrapper[0].removeEventListener('touchmove', moveDrag, true);
				if(direction==1)index += scroll;
				else if(direction==-1)index -= scroll;
				if(index>=0 && (direction==1))index = 0;
				if((Math.abs(index)+visible)>=count && (direction==-1))index = (-count + visible);
				draging = false;
				time = new Date().getTime() - time;
				moveWrapper();
			}
		},
		beforeMove = function(){
			if($.isFunction(options.beforeForLast) && indexLast!=null)options.beforeForLast.call(list.eq(Math.abs(indexLast)), Math.abs(indexLast));
			if($.isFunction(options.before))options.before.call(list.eq(Math.abs(index)), Math.abs(index));
			if(options.title!='' && options.hide){
				options.titleAnimate=='opacity' ? title.stop(true, false).animate({opacity:0}, 200) : title.stop(true, false).animate({top:height}, 200);
			}
		},
		moveWrapper = function(){
			if(options.type==0){
				var width = _this.data('width'), height = _this.data('height'), tit = list.eq(Math.abs(index)).data('title'), px, v = 1, lw = 0, lh = 0,
				prop = dir=='x' ? {left:index*(width/visible)} : {top:index*(height/visible)};
				if(!options.section){
					if(time<300)v = 1.5;
					if(dir=='x'){
						px = wrapper.position().left + touchpx * v;
						if(touchpx>0 && px>0)px = 0;
						else if(touchpx<0 && px<width-wrapper.width())px = width-wrapper.width();
						prop = {left:px};
					}else{
						px = wrapper.position().top + touchpx * v;
						if(touchpx>0 && px>0)px = 0;
						else if(touchpx<0 && px<height-wrapper.height())px = height-wrapper.height();
						prop = {top:px};
					}
				};
				touchpx = 0;
				wrapper.animate(prop, options.speed, options.easing, function(){
					if(options.section){
						if(pager.length){
							var idx = (options.unlimit?index+1:index), curA = pager.find('a');
							if(options.unlimit && Math.abs(idx)==count)idx = 0;
							curA.removeClass(options.curpager);
							curA.eq(Math.abs(idx)).addClass(options.curpager);
							if(options.auto>0 && options.progressPager && !hovering)progressPager((Math.abs(idx)+1>=count?0:Math.abs(idx)+1));
						}
						if(options.title!='' && !!tit){
							title.html(tit);
							if(options.hide){
								if(options.titleAnimate=='opacity'){
									title.stop(true, false).animate({opacity:options.opacity}, 200);
								}else{
									title.stop(true, false).animate({top:height-title.outerHeight(false)}, 200);
								}
							}
						}
						if(!options.unlimit){
							if(options.prev!='' && options.disprev!=''){
								if(index>=0)$(options.prev).addClass(options.disprev);
								else $(options.prev).removeClass(options.disprev);
							}
							if(options.next!='' && options.disnext!=''){
								if((Math.abs(index)+visible)>=count)$(options.next).addClass(options.disnext);
								else $(options.next).removeClass(options.disnext);
							}
						}
					}
					if(direction==-1){
						if($.isFunction(options.afterLeft))options.afterLeft.call(list.eq(Math.abs(index)), Math.abs(index));
					}else{
						if($.isFunction(options.afterRight))options.afterRight.call(list.eq(Math.abs(index)), Math.abs(index));
					}
					if($.isFunction(options.after))options.after.call(list.eq(Math.abs(index)), Math.abs(index));
					if(options.auto>0 && !hovering){
						clearInterval(autoHandle);
						autoHandle = setInterval(function(){autoMove()}, auto);
						if(options.progress!='')progressHandle();
					};
					_this.removeData('prev').removeData('next').removeData('mousewheel');
					indexLast = index;
					direction = 0;
				});
			}else{
				var height = _this.height(), tit = list.eq(Math.abs(index)).data('title'),
				curList = list.filter(':visible'), nextList = list.eq(Math.abs(index));
				nextList.css({display:'', 'z-index':504, opacity:1});
				curList.fadeOut(options.speed, function(){
					nextList.css({'z-index':505});
					if(pager.length){
						var idx = index, curA = pager.find('a');
						curA.removeClass(options.curpager);
						curA.eq(Math.abs(idx)).addClass(options.curpager);
						if(options.auto>0 && options.progressPager && !hovering)progressPager((Math.abs(idx)+1>=count?0:Math.abs(idx)+1));
					}
					if(options.title!='' && !!tit){
						title.html(tit);
						if(options.hide){
							if(options.titleAnimate=='opacity'){
								title.stop(true, false).animate({opacity:options.opacity}, 200);
							}else{
								title.stop(true, false).animate({top:height-title.outerHeight(false)}, 200);
							}
						}
					}
					if(direction==-1){
						if($.isFunction(options.afterLeft))options.afterLeft.call(list.eq(Math.abs(index)), Math.abs(index));
					}else{
						if($.isFunction(options.afterRight))options.afterRight.call(list.eq(Math.abs(index)), Math.abs(index));
					}
					if($.isFunction(options.after))options.after(Math.abs(index));
					if(options.auto>0 && !hovering){
						clearInterval(autoHandle);
						autoHandle = setInterval(function(){autoMove()}, auto);
						if(options.progress!='')progressHandle();
					};
					_this.removeData('prev').removeData('next').removeData('mousewheel');
					indexLast = index;
					direction = 0;
				});
			}
		};
		if($.isFunction(options.complete))options.complete.call(_this);
		if(options.keydown)$(document).keydown(function(e){
			var e = e||window.event, code = e.which||e.keyCode;
			if(code==37 || code==38){
				if(!!!_this.data('prev')){
					if(index>=0)return;
					else{
						cancelProgress();
						beforeMove();
						if(code==37){
							if(index+scroll>=0)index = 0;
							else index += scroll;
						}else index = 0;
					};
					_this.data('prev', true);
					direction = -1;
					moveWrapper();
					if(e.preventDefault)e.preventDefault();
					e.returnValue = false;
					return false;
				}
			}else if(code==39 || code==40){
				if(!!!_this.data('next')){
					if(Math.abs(index)+visible>=count)return;
					else{
						cancelProgress();
						beforeMove();
						if(code==39){
							if(Math.abs(index)+visible+scroll>=count)index -= count+index-visible;
							else index -= scroll;
						}else index = -count+1;
					};
					_this.data('next', true);
					direction = 1;
					moveWrapper();
					if(e.preventDefault)e.preventDefault();
					e.returnValue = false;
					return false;
				}
			}
		});
		if(!options.unlimit){
			if(options.prev!='' && options.disprev!=''){
				if(index>=0)$(options.prev).addClass(options.disprev);
			}
			if(options.next!='' && options.disnext!=''){
				if((Math.abs(index)+visible)>=count)$(options.next).addClass(options.disnext);
			}
		}
		if(options.prev!='')$(options.prev).click(function(e){
			if(!!!_this.data('prev')){
				if(index>=0)return false;
				else{
					cancelProgress();
					beforeMove();
					if(index+scroll>=0)index = 0;
					else index += scroll;
				};
				_this.data('prev', true);
				direction = -1;
				moveWrapper();
				//if(e.preventDefault)e.preventDefault();
				//e.returnValue = false;
				return false;
			}
		});
		if(options.next!='')$(options.next).click(function(e){
			if(!!!_this.data('next')){
				if(Math.abs(index)+visible>=count)return false;
				else{
					cancelProgress();
					beforeMove();
					if(Math.abs(index)+visible+scroll>=count)index -= count+index-visible;
					else index -= scroll;
				};
				_this.data('next', true);
				direction = 1;
				moveWrapper();
				//if(e.preventDefault)e.preventDefault();
				//e.returnValue = false;
				return false;
			}
		});
		if(options.mouseWheel)_this.mousewheel(function(e, d){
			if(!!!_this.data('mousewheel')){
				if(d>0){
					if(index>=0)return;
					else{
						cancelProgress();
						beforeMove();
						if(index+scroll>=0)index += -index;
						else index += scroll;
					}
					direction = 1;
				}else{
					if(Math.abs(index)+visible>=count)return;
					else{
						cancelProgress();
						beforeMove();
						if(Math.abs(index)+visible+scroll>=count)index -= count+index-visible;
						else index -= scroll;
					}
					direction = -1;
				};
				_this.data('mousewheel', true);
				moveWrapper();
			}
		});
		if(mobileDevice())options.act = 'click';
		if(pager.length && options.act!=''){
			pager.find('a').on(options.act, function(e){
				cancelProgress();
				beforeMove();
				index = -$(this).index();
				if(visible>1 && index<=-(count-visible))index = -(count-visible);
				if(options.unlimit){
					if(index==0)index = -1;
					else if(index==-count)index = -count-1;
					else index--;
				}
				if(indexLast!=null){
					if(indexLast<index)direction = -1;
					else direction = 1;
				};
				if($.isFunction(options.pagerAction))options.pagerAction.call($(this), Math.abs(index));
				moveWrapper();
				//if(e.preventDefault)e.preventDefault();
				//e.returnValue = false;
				return false;
			});
		}
		if(options.auto>0){
			auto = options.auto;
			if(options.dir=='right' || options.dir=='bottom')direction = 1;
			else direction = -1;
			if(auto<options.speed)auto = options.speed;
			if(options.progress!='' && options.autoWait>0)progressHandle();
			if(options.progressPager && pager.length){
				pager.find('a').css('position', 'relative');
				progressPager((Math.abs(index)+1>=count?0:Math.abs(index)+1));
			};
			autoWaitHandle = setTimeout(function(){
				autoMove();
				autoHandle = setInterval(function(){autoMove()}, auto);
			}, options.autoWait);
		}
		if(options.hoverStop){
			_this.hover(function(){hoverHandle(true)},function(){hoverHandle(false)});
			if(options.title!='')title.hover(function(){hoverHandle(true)},function(){hoverHandle(false)});
		}
		if(options.drag && options.type==0){
			_this.unselect().on('mouseup mouseleave', endDrag);
			wrapper.on('mousedown', startDrag).on('mouseup mouseleave', endDrag).on('click', clearEvent);
			wrapper.on('dragstart', 'img, a', function(e){e.preventDefault()});
			if(window.addEventListener){
				_this[0].addEventListener('touchend', endDrag, true);
				wrapper[0].addEventListener('touchstart', startDrag, true);
				wrapper[0].addEventListener('touchend', endDrag, true);
				wrapper[0].addEventListener('touchcancel', endDrag, true);
			}
		}
		function hoverHandle(bool){
			if(options.auto>0){
				hovering = bool;
				if(bool){
					clearInterval(autoWaitHandle);
					clearInterval(autoHandle);
					if(pager.length)pager.find('a span').stop(true, false).fadeOut(200);
					if(options.progress!='')progress.stop(true, false).fadeOut(200);
				}else{
					autoHandle = setInterval(function(){autoMove()}, auto);
					if(options.progressPager && pager.length)progressPager((Math.abs(index)+1>=count?0:Math.abs(index)+1));
					if(options.progress!='')progressHandle();
				}
			}
		}
		function autoMove(){
			beforeMove();
			if(Math.abs(index)+visible>=count+(options.unlimit?3:0)){
				index = 0;
			}else{
				if(Math.abs(index)+visible+scroll>=count+(options.unlimit?3:0)){
					if(options.unlimit){
						index = count+index-visible;
						wrapper.stop(true, false);
						if(dir=='x'){
							wrapper.css('left', -(width/visible));
						}else{
							wrapper.css('top', -(height/visible));
						}
					}else index -= count+index-visible;
				}else if(options.unlimit && index==0){
					index = -(count-1);
					wrapper.stop(true, false);
					if(dir=='x'){
						wrapper.css('left', -(wrapper.width()-(width/visible)*2));
					}else{
						wrapper.css('top', -(wrapper.height()-(height/visible)*2));
					}
				}else{
					if(options.unlimit){
						index = (direction==-1 ? index-scroll : index+scroll);
					}else index -= scroll;
				}
			}
			moveWrapper();
		}
		function cancelProgress(){
			clearInterval(autoWaitHandle);
			if(options.auto>0 && options.progress!='')progress.stop(true, false).hide();
		}
		function progressHandle(){
			progress.width(0).show().animate({width:width}, options.auto, 'linear');
		}
		function progressPager(idx){
			pager.find('a span').hide();
			var curA = pager.find('a').eq(idx), span = curA.find('span').width(0).show();
			span.animate({width:curA.width()+1}, options.auto, 'linear');
		}
		function mobileDevice(mark){
			var na = navigator.userAgent.toLowerCase();
			if(typeof mark!='undefined'){
				return na.match(new RegExp(mark,'i')) == mark;
			}else{
				if($.browser.ipad || $.browser.iphone || $.browser.android || $.browser.wm || $.browser.wince || $.browser.ucweb || $.browser.uc7 || $.browser.midp)return true;
				else return false;
			}
		}
	});
};

//上拉刷新
$.fn.pullRefresh = function(options){
	if(typeof options=='string'){
		if(!!this.data(options))this.data(options)();
		return this;
	};
	options = $.extend({
		header : false, //使用顶部, 如果使用html,即自定义
		footer : false, //使用底部
		dragFooter : false, //使用拖曳底部, 如果使用html,即自定义
		headerText : '下拉可以刷新', //顶部默认文字
		footerText : '上拉加载更多', //底部默认文字
		headerTipText : '松开立即刷新', //顶部下拉时的提示文字
		footerTipText : '松开加载更多', //底部上拉时的提示文字
		headerUpdatingText : '正在刷新中...', //顶部加载中的文字
		footerUpdatingText : '正在加载中...', //底部加载中的文字
		headerUpdateTime : '最后更新 %t', //顶部更新时间, %t将替换为当前时间
		footerUpdateTime : '最后加载 %t', //底部更新时间, %t将替换为当前时间
		headerView : '', //头部控件, html代码
		footerNoMoreText : '', //底部加载后滚动条高度没有变化时显示, 为空即不显示
		useTransform : !$.browser.android, //使用transform移动
		scroll : null, //滚动时执行, 接受两个参数:headerView, headerViewHolder
		start : null, //准备拖动时执行
		move : null, //拖动时执行, 接受一个参数:当前拖曳距离
		release : null, //松开时执行
		complete : null, //脚本执行完成后执行
		restore : null, //恢复正常状态时执行
		refresh : null, //下拉执行, 附带一个参数(该参数是一个函数,且执行完后需执行这个函数来关闭显示区域)
		load : null //上拉执行, 附带一个参数(同上)
	}, options);
	return this.each(function(){
		$(this).stopBounces(true).removePlug('pullRefresh');
		var _this = $(this), height = _this.height(), wrap = $('<div class="pullWrap"></div>'), headerArea = null, footerArea = null,
			headerView = null, headerViewHolder = null, headerCustom = false, footerCustom = false, scrollFooterArea = false, moved = false, originFooterTop = 0;
		_this.css('position', 'relative').parent().css('position', 'relative');
		if(!_this.children().length)return true;
		_this.children().wrapAll(wrap);
		wrap = _this.find('.pullWrap');
		wrap.unselect().on('dragstart', 'img, a', function(e){e.preventDefault()});
		if(options.header){
			if(typeof options.header=='boolean'){
				headerArea = $('<div class="pullHeader"><div><i></i><i class="x"></i><span><font>'+options.headerText+'</font><strong></strong></span></div></div>');
			}else{
				headerCustom = true;
				headerArea = $('<div class="pullHeader">'+options.header+'</div>');
				if(headerArea.children().length==1 && headerArea.children().is('.preloader'))headerArea.height(64);
			};
			wrap.prepend(headerArea);
		}
		if(options.footer || options.dragFooter){
			if(options.footer || typeof options.dragFooter=='boolean'){
				footerArea = $('<div class="pullFooter"><div><i></i><i class="x"></i><span><font>'+options.footerText+'</font><strong></strong></span></div><span>'+options.footerNoMoreText+'</span></div>');
			}else{
				footerCustom = true;
				footerArea = $('<div class="pullFooter">'+options.dragFooter+'</div>');
			}
			if(options.footer){
				scrollFooterArea = true;
				wrap.append(footerArea);
				footerArea.addClass('pullScrollFooter');
			}else{
				wrap.after(footerArea);
				originFooterTop = _this.offset().top + _this.height();
				footerArea.css('top', originFooterTop);
			}
		}
		if(options.headerView){
			var offset = _this.offset();
			headerView = $(options.headerView);
			_this.before(headerView);
			var headerHeight = headerView.css({position:'absolute', 'z-index':-1, left:offset.left, top:offset.top}).outerHeight(false);
			headerViewHolder = $('<div style="width:100%;height:'+headerHeight+'px;overflow:hidden;"></div>');
			if(headerArea){
				headerArea.after(headerViewHolder);
			}else{
				wrap.prepend(headerViewHolder);
			}
		};
		var scrollHeight = _this[0].scrollHeight, loading = false, d = 0, transformY = 0, scrollTop,
		startDrag = function(e){
			loading = false;
			moved = false;
			transformY = options.useTransform ? wrap.transform().y : (Number(wrap.css('top').replace(/px/,''))||0)*1;
			scrollTop = _this.scrollTop();
			d = $.touches(e).y;
			if(!$.browser.android){
				wrap.on('mousemove', moveDrag);
				if(window.addEventListener)wrap[0].addEventListener('touchmove', moveDrag, true);
			}
			if($.isFunction(options.start))options.start.call(_this);
		},
		moveDrag = function(e){
			scrollHeight = _this[0].scrollHeight;
			moved = true;
			var curTop, moveY, curY = $.touches(e).y;
			if( (scrollTop<=0 && curY<d) || (scrollTop>=scrollHeight-height && curY>d) ){unbind();return};
			e.preventDefault();
			moveY = ($.browser.mobile ? (curY-d)/2 : curY-d);
			curTop = transformY + moveY * ($.window().screenHeight<=500 ? 0.7 : 0.4);
			if(headerArea && scrollTop<=0 && curY>d){
				if(!headerCustom){
					var headerHeight = headerArea.height();
					if(Math.abs(curTop)>=headerHeight){
						headerArea.find('i:eq(0)').addClass('h');
						headerArea.find('font').html(options.headerTipText);
					}else{
						headerArea.find('i:eq(0)').removeClass('h');
						headerArea.find('font').html(options.headerText);
					}
				}
			}
			if(footerArea && scrollTop>=scrollHeight-height && curY<d){
				if(!footerCustom){
					var footerHeight = footerArea.height();
					if(Math.abs(curTop)>=footerHeight){
						footerArea.find('i:eq(0)').addClass('h');
						footerArea.find('font').html(options.footerTipText);
					}else{
						footerArea.find('i:eq(0)').removeClass('h');
						footerArea.find('font').html(options.footerText);
					}
				}
				if(options.useTransform){
					footerArea.css({transform:'translate3d(0,'+curTop+'px,0)', '-webkit-transform':'translate3d(0,'+curTop+'px,0)', 'transition-duration':'0s', '-webkit-transition-duration':'0s'});
				}else{
					footerArea.css({top:originFooterTop+curTop});
				}
			}
			if(options.useTransform){
				wrap.css({transform:'translate3d(0,'+curTop+'px,0)', '-webkit-transform':'translate3d(0,'+curTop+'px,0)', 'transition-duration':'0s', '-webkit-transition-duration':'0s'});
			}else{
				wrap.css({top:curTop});
			}
			if(headerView)headerView.height(headerViewHolder.height()+curTop);
			if($.isFunction(options.move))options.move.call(_this, curTop);
			return true;
		},
		endDrag = function(e){
			unbind();
			if(e.button>0)return;
			if($.isFunction(options.release))options.release.call(_this);
			if(!moved)return;
			var transform = options.useTransform ? wrap.transform().y : (Number(wrap.css('top').replace(/px/,''))||0)*1;
			if(headerArea){
				var headerHeight = headerArea.height();
				if(transform>=headerHeight){
					headerBegin();
				}else{
					if(headerView)headerView.heightAnimate(headerViewHolder.height());
				}
			}
			if(footerArea){
				var footerHeight = footerArea.height();
				if(transform<=-footerHeight){
					footerBegin();
				}
			}
			if(!loading)restore();
		}
		if(_this.scrollTop()==0)bind();
		setTimeout(function(){scrollHeight = _this[0].scrollHeight}, 10);
		//var aa = $.debugHTML();
		_this.scroll(function(){
			if($.isFunction(options.scroll))options.scroll.call(_this, headerView, headerViewHolder);
			if(loading)return;
			unbind();
			scrollHeight = _this[0].scrollHeight;
			if(headerArea && _this.scrollTop()<=0)bind();
			//aa.html(_this.scrollTop()+' '+(scrollHeight-height));
			if(footerArea && scrollHeight>height && !footerArea.hasClass('pullNoMore')){
				if(!scrollFooterArea){
					if(_this.scrollTop()>=(scrollHeight-height))bind();
				}else{
					if(_this.scrollTop()>=(scrollHeight-height-5))footerBegin();
				}
			}
		});
		if(headerArea){
			_this.data('headerBegin', headerBegin);
			_this.data('headerEnd', restore);
		}
		if(footerArea){
			_this.data('footerBegin', footerBegin);
			_this.data('footerEnd', restore);
		};
		var listHeight = wrap.height();
		if(headerArea)listHeight -= headerArea.height();
		if(footerArea)listHeight -= footerArea.height();
		if(listHeight<=_this.height() && footerArea)footerArea.addClass('pullNoMore').hide();
		if($.isFunction(options.complete))options.complete.call(_this);
		function headerBegin(){
			var headerHeight = headerArea.height();
			loading = true;
			if(options.useTransform){
				wrap.css({transform:'translate3d(0,'+headerHeight+'px,0)', '-webkit-transform':'translate3d(0,'+headerHeight+'px,0)', 'transition-duration':'', '-webkit-transition-duration':''});
			}else{
				wrap.animate({top:headerHeight}, 300);
			}
			if(headerView)headerView.heightAnimate(headerViewHolder.height()+headerHeight);
			if(!headerCustom){
				headerArea.find('i:eq(0)').removeClass('h').hide().next().show();
				headerArea.find('font').html(options.headerUpdatingText);
			}
			if($.isFunction(options.refresh)){
				options.refresh.call(_this, function(){
					if(!headerCustom)headerArea.find('strong').html(options.headerUpdateTime.replace(/%t/ig, getNow()));
					if(footerArea){
						footerArea.removeClass('pullNoMore').show();
						if(options.footerNoMoreText.length){
							footerArea.children('span').hide();
							footerArea.children('div').show();
						}else{
							footerArea.slideDown(300);
						};
						var listHeight = wrap.height();
						listHeight -= headerArea.height();
						if(footerArea)listHeight -= footerArea.height();
						if(listHeight<=_this.height() && footerArea)footerArea.addClass('pullNoMore').hide();
					}
					if(headerView)headerView.heightAnimate(headerViewHolder.height());
					restore();
				});
			}else{
				if(headerView)headerView.heightAnimate(headerViewHolder.height());
				restore();
			}
		}
		function footerBegin(){
			var footerHeight = footerArea.height();
			loading = true;
			if(!scrollFooterArea){
				if(options.useTransform){
					wrap.css({transform:'translate3d(0,-'+footerHeight+'px,0)', '-webkit-transform':'translate3d(0,-'+footerHeight+'px,0)', 'transition-duration':'', '-webkit-transition-duration':''});
					footerArea.css({transform:'translate3d(0,-'+footerHeight+'px,0)', '-webkit-transform':'translate3d(0,-'+footerHeight+'px,0)', 'transition-duration':'', '-webkit-transition-duration':''});
				}else{
					wrap.animate({top:-footerHeight}, 300);
					footerArea.animate({top:originFooterTop-footerHeight}, 300);
				}
			}
			if(!footerCustom){
				footerArea.find('i:eq(0)').removeClass('h').hide().next().show();
				footerArea.find('font').html(options.footerUpdatingText);
			}
			if($.isFunction(options.load)){
				options.load.call(_this, function(){
					if(!footerCustom)footerArea.find('strong').html(options.footerUpdateTime.replace(/%t/ig, getNow()));
					if(scrollHeight>=_this[0].scrollHeight){
						footerArea.addClass('pullNoMore');
						if(options.footerNoMoreText.length){
							footerArea.children('span').css('display', 'block');
							footerArea.children('div').hide();
						}else{
							footerArea.slideUp(300);
						}
					}else{
						footerArea.removeClass('pullNoMore').show();
						if(options.footerNoMoreText.length){
							footerArea.children('span').hide();
							footerArea.children('div').show();
						}
					};
					restore();
				});
			}else{
				restore();
			}
		}
		function restore(){
			setTimeout(function(){
				loading = false;
				if(options.useTransform){
					wrap.css({transform:'', '-webkit-transform':'', 'transition-duration':'', '-webkit-transition-duration':''});
				}else{
					wrap.animate({top:0}, 300);
				}
				if(headerArea){
					if(!headerCustom){
						headerArea.find('i:eq(0)').show().next().hide();
						headerArea.find('font').html(options.headerText);
					}
					if(_this.scrollTop()==0)bind();
				}
				if(footerArea){
					if(options.useTransform){
						footerArea.css({transform:'', '-webkit-transform':'', 'transition-duration':'', '-webkit-transition-duration':''});
					}else{
						footerArea.animate({top:originFooterTop}, 300);
					};
					if(!footerCustom){
						footerArea.find('i:eq(0)').show().next().hide();
						footerArea.find('font').html(options.footerText);
					};
					setTimeout(function(){
						if(_this.scrollTop()==scrollHeight-height && scrollHeight>height)bind();
					}, 210);
				}
				if($.isFunction(options.restore))options.restore.call(_this);
			}, 10);
		}
		function bind(){
			if(!wrap.length)return;
			wrap.on('mousedown', startDrag).on('mouseup', endDrag);
			if(window.addEventListener){
				wrap[0].addEventListener('touchstart', startDrag, true);
				wrap[0].addEventListener('touchend', endDrag, true);
			}
			if($.browser.android){
				wrap.on('mousemove', moveDrag);
				if(window.addEventListener)wrap[0].addEventListener('touchmove', moveDrag, true);
			}
		}
		function unbind(){
			if(!wrap.length)return;
			wrap.off('mousedown', startDrag).off('mouseup', endDrag).off('mousemove', moveDrag);
			if(window.addEventListener){
				wrap[0].removeEventListener('touchstart', startDrag, true);
				wrap[0].removeEventListener('touchend', endDrag, true);
				wrap[0].removeEventListener('touchmove', moveDrag, true);
			}
		}
		function getNow(){
			var now = new Date();
			return now.getFullYear()+'-'+$.fillZero(now.getMonth()+1,2)+'-'+$.fillZero(now.getDate(),2)+' '+$.fillZero(now.getHours(),2)+':'+$.fillZero(now.getMinutes(),2)+':'+$.fillZero(now.getSeconds(),2);
		}
	});
};

//滚筒select
$.fn.selectpicker = function(opt){
	//arguments.callee: 正被执行的Function对象
	if(!$.isPlainObject(opt) && arguments.length<2){alert('Missing Parameters');return}
	var args = arguments, nopt = false, options = {
		cls : '', //附加类名
		autoWidth : true, //各列自动宽度
		debug : false, //不隐藏select
		select : null, //选择后执行, this:当前选择的select, 接受两个参数, component:滚筒, row:行
		complete : null //生成后执行
	};
	if($.isPlainObject(opt)){
		options = $.extend(options, opt);
	}else{
		nopt = true;
	};
	return this.each(function(){
		$(this).removePlug('selectpicker');
		var _this= $(this), s = nopt ? 0 : 1, objs = [], selects = [];
		for(var i=s; i<args.length; i++){
			if(typeof args[i]=='string'){
				objs.push(args[i]);
			}else{
				if($.isPlainObject(args[i]))continue;
				var obj = $(args[i]);
				if(!obj.length)continue;
				options.debug ? obj.show() : obj.hide();
				if(!obj.is('select'))continue;
				selects.push(obj);
				objs.push(obj);
			}
		}
		if(!selects.length)return true;
		var picker = $('<div class="selectpicker '+(options.cls.length?options.cls:'')+'"><div></div></div>'),
			highlight = $('<strong class="highlight"></strong>');
		_this.append(picker);
		picker.append(highlight);
		var width = picker.width(), pickerDiv = picker.find('div'), top = highlight.position().top + highlight.margin().top;
		//var aa = $.debugHTML();
		$.each(objs, function(k){
			if(typeof objs[k]=='string'){
				var component = $('<ul class="component'+(k+1)+'"><div class="text">'+objs[k]+'</div></ul>');
				pickerDiv.append(component);
				return true;
			};
			var _select = objs[k], component = $('<ul class="component'+(k+1)+'"><div></div></ul>');
			pickerDiv.append(component);
			var div = component.find('div'), html = '', selectIndex = 0;
			_select.data('index', k).change(function(){
				if(!!!$(this).data('selecting')){
					selectRow(picker.find('ul').eq($(this).data('index')).find('li').eq($(this).find('option').index($(this).selected())), true, true);
				}
			}).find('option').each(function(i){
				if(!!$(this).attr('selected'))selectIndex = i;
				html += '<li'+(!!$(this).attr('selected')?' class="selected"':'')+'>'+$(this).text()+'</li>';
			});
			div.html(html);
			div.on('click', 'li', function(){selectRow($(this), true)});
			_select.attr('selectIndex', selectIndex).data('html', _select.outerHTML());
			var selected = div.find('.selected'), d, startY, lastY, dragY, timer = null, timeout = null, moved = false;
			if(!selected.length)selected = div.find('li:eq(0)').addClass('selected');
			var height = selected.height();
			selectRow(selected);
			div.inertia({
				lockX : true,
				fixed : function(){
					return {start:top, end:picker.height()-top-highlight.height()};
				},
				move : function(x, y){
					//changeRow(y); //拖动立即设置class="selected"
				},
				end : function(x, y){
					var index = ((top - y) / height).round(), children = this.children();
					if(index<0)index = 0;
					if(index>children.length-1)index = children.length - 1;
					selectRow(children.eq(index), true);
				}
			});
			function changeRow(nowY){
				var index = ((top - nowY) / height).round(), children = div.children();
				if(index<0)index = 0;
				if(index>children.length-1)index = children.length - 1;
				if(children.filter('.selected').index()==index)return;
				children.removeClass('selected').eq(index).addClass('selected');
			}
			function selectRow(row, animate, notChange){
				if(!row.length)return;
				var index = row.index();
				animateRow(row, animate);
				clearTimeout(timeout); timeout = null;
				timeout = setTimeout(function(){
					clearTimeout(timeout); timeout = null;
					if(!!!picker.data('component'))return;
					_select.attr('selectIndex', index).find('option').each(function(i){
						if(i!=index){
							$(this).removeProp('selected').removeAttr('selected');
						}else{
							$(this).prop('selected', 'selected').attr('selected', 'selected');
						}
					});
					_select.data('html', _select.outerHTML());
					if(!notChange && $._data(_select[0], 'events') && $._data(_select[0], 'events')['change']){
						_select.data('selecting', true).trigger('change');
						setTimeout(function(){_select.removeData('selecting')}, 1000);
					};
					setTimeout(function(){
						if($.isFunction(options.select))options.select.call(_select, row.parent().parent(), row);
						setWidth();
						$.each(objs, function(j){
							var _s = objs[j], html = '';
							if(typeof _s=='string')return true;
							if(_s.is(_select))return true;
							_s.attr('selectIndex', _s.find('option').index(_s.selected()));
							if(_s.data('html') == _s.outerHTML())return true;
							var div = pickerDiv.find('ul:eq('+j+')').find('div').html('');
							_s.data('html', _s.outerHTML()).find('option').each(function(){
								html += '<li'+(!!$(this).attr('selected')?' class="selected"':'')+'>'+$(this).text()+'</li>';
							});
							div.html(html);
							var row = div.children().eq(_s.find('option').index(_s.selected()));
							row.addClass('selected');
							animateRow(row, true);
						});
					}, 10);
				}, 300);
			}
			function animateRow(row, animate){
				var y = top - row.position().top, duration = animate ? '' : '0s', index = row.index(), parent = row.parent();
				parent.css({transform:'translate3d(0,'+y+'px,0)', '-webkit-transform':'translate3d(0,'+y+'px,0)', 'transition-duration':duration, '-webkit-transition-duration':duration});
				parent.children().removeClass('selected');
				row.addClass('selected');
				setTimeout(function(){
					parent.css({'transition-duration':'', '-webkit-transition-duration':''});
				}, 300);
			}
		});
		setTimeout(function(){
			picker.data('component', true);
			if($.isFunction(options.complete))options.complete.apply(_this, objs);
		}, 300);
		setTimeout(setWidth, 0);
		function setWidth(){
			if(!options.autoWidth)return;
			var ul = picker.find('ul'), totalWidth = 0, parent = ul.parent();
			ul.css('width', 'auto').each(function(){
				totalWidth += $(this).width();
			}).each(function(){
				var percent = $(this).width() / totalWidth;
				$(this).width(Math.floor(parent.width()*percent));
			});
		}
	});
};

//动画显示标题
$.fn.showtitle = function(options){
	options = $.extend({
		title : '', //标题容器类名
		opacity : 0.7, //标题容器透明度
		titleAnimate : 'move', //标题容器显示动画, [move|opacity]
		hide : true, //标题是否隐藏(产生动画显示,否则一直显示)
		speed : 200, //标题出现速度
		complete : null //生成后执行
	}, options);
	return this.each(function(){
		if(!!!$(this).attr('title'))return true;
		if(!!$(this).data('showtitle'))return true;
		var _this = $(this).data('showtitle', true), title = _this.attr('title'), parent = _this.parent().is('a') ? _this.parent().parent() : _this.parent(),
		tag = $('<div><div></div></div>').addClass(options.title);
		parent.css({position:'relative', overflow:'hidden'}).append(tag);
		_this.removeAttr('title');
		tag.css({position:'absolute', 'z-index':'555', overflow:'hidden'});
		var div = tag.find('div').html(title), w = parent.width(), h = parent.height(), height = tag.outerHeight(false), tpadding = tag.padding(),
		width = w - tpadding.left - tpadding.right;
		tag.removeClass(options.title).css({opacity:options.opacity, width:w, left:0, top:h-height});
		div.addClass(options.title).css({width:width, position:'relative', top:0, left:0});
		if(options.hide){
			if(options.titleAnimate=='opacity'){
				tag.css({opacity:0});
			}else{
				tag.css({top:h});
			}
			parent.hover(function(e){
				if(options.titleAnimate=='opacity'){
					tag.stop(true, false).animate({opacity:options.opacity}, options.speed);
				}else{
					var sl = $.scroll().left, st = $.scroll().top, x = sl + e.clientX, y = st + e.clientY, position = parent.position(),
					top = position.top, left = position.left, right = left + w, bottom = top + h;
					div.css({left:0,top:0});
					if(x<=left+20)tag.css({left:-w,top:h-height}).animate({left:0}, options.speed);
					else if(x>=right-20)tag.css({left:w,top:h-height}).animate({left:0}, options.speed);
					else if(y<=top+20)tag.css({left:0,top:h-height}).find('div').css({top:-height}).animate({top:0}, options.speed);
					else if(y>=bottom-20)tag.css({left:0,top:h}).animate({top:h-height}, options.speed);
				}
			},function(e){
				if(options.titleAnimate=='opacity'){
					tag.stop(true, false).animate({opacity:0}, options.speed);
				}else{
					var sl = $.scroll().left, st = $.scroll().top, x = sl + e.clientX, y = st + e.clientY, position = parent.position(),
					top = position.top, left = position.left, right = left + w, bottom = top + h;
					div.css({left:0,top:0});
					if(x<=left)tag.animate({left:-w}, options.speed);
					else if(x>=right)tag.animate({left:w}, options.speed);
					else if(y<=top)div.animate({top:-height}, options.speed, function(){tag.css({top:h})});
					else if(y>=bottom)tag.animate({top:h}, options.speed);
				}
			});
		}
		if($.isFunction(options.complete))options.complete.call(_this);
	});
};

//拖拽显示
$.fn.dragshow = function(options){
	options = $.extend({
		list : 'li', //拖动列表
		title : '删除', //显示区域内的内容(支持html代码)(支持函数,接受一个参数:当前行)
		cls : '.title', //显示区域的类名称
		useTransform : true, //使用CSS3特性来移动, list的css样式需要增加transform:translate3d(0,0,0);transition-duration:200ms;
		click : null, //点击显示区域时执行, this:显示区域, 接受两个参数:当前行, 显示区域点击的element(为区别显示区域内有多个html标签)
		before : null //拖动前执行
	}, options);
	return this.each(function(){
		$(this).stopBounces(true);
		var _this = $(this).css('position', 'relative'), width = _this.width(), originalX, dx, dy, moved = false, btnWidth = 0, editingFix = 0, curX = 0,
		list = _this.find(options.list), btn = _this.children(options.cls), useTransform = options.useTransform;
		if(list.css('transform')=='none')useTransform = false;
		if(!btn.length){
			btn = $('<div class="'+options.cls.replace('.','').replace('#','')+'"></div>');
			_this.append(btn);
			if($.isFunction(options.click)){
				btn.onclick(function(e){
					var _b = $(this), e = e||window.event, o = e.target||e.srcElement;
					options.click.call(_b, _b.data('curRow'), $(o));
					_b.removeData('curRow');
					_this.removeData('lastEdit');
				});
			}
		};
		btnWidth = btn.width();
		btn.hide();
		list.css({position:'relative', 'z-index':2}).each(function(){
			var _l = $(this);
			if(!!_l.data('dragshow'))return true;
			_l.data('dragshow', true);
			var startDrag = function(e){
				var o = e.target || e.srcElement;
				if($(o).is('input'))$(o).focus();
				if(!!_this.data('lastEdit') && _this.data('lastEdit')[0]!==_l[0]){cancelLast(e);return false}
				if($.isFunction(options.before)){
					var before = options.before.call(_l);
					if(typeof before=='boolean' && !before)return;
				};
				moved = false;
				_this.find('a').data('hasbind', true);
				var title = options.title;
				if($.isFunction(title))title = title.call(btn, _l);
				btn.html(title).css({top:_l.position().top, height:_l.outerHeight(false), 'line-height':_l.outerHeight(false)+'px'}).data('curRow', _l);
				btnWidth = btn.width();
				originalX = useTransform ? _l.transform().x : _l.position().left;
				dx = $.touches(e).x;
				dy = $.touches(e).y;
				editingFix = !!_this.data('lastEdit') ? btnWidth : 0;
				_l.stop(true, false);
				if(!$.browser.android){
					_l.on('mousemove', moveDrag).css('cursor', 'move');
					if(window.addEventListener)_l[0].addEventListener('touchmove', moveDrag, true);
				};
				return false;
			},
			moveDrag = function(e){
				var newPosition = 0, moveX = $.touches(e).x, moveY = $.touches(e).y;
				if(moveY-dy>10 || moveY-dy<-10)return false;
				if(moveX-dx>10 || moveX-dx<-10)e.preventDefault(); //终止屏幕拖动, 使用后页面不能上下拖动动
				btn.css({display:''});
				moveX -= dx;
				moved = true;
				if(!_l.find('.overlay-div').length){
					list.each(function(){
						$(this).append('<div class="overlay-div" style="position:absolute;z-index:999;top:0;left:0;width:100%;height:100%;"></div>');
					});
				}
				if (moveX < 0) {
					if (moveX <= -(width-btnWidth)/3-btnWidth+editingFix) {
						newPosition = curX + (moveX+(width-btnWidth)/3+btnWidth-editingFix) * 0.2;
					} else {
						newPosition = curX = originalX + moveX;
					}
				} else {
					if (moveX <= 0+editingFix) {
						newPosition = originalX + moveX;
					} else {
						
					}
				}
				if(useTransform){
					_l.css({transform:'translate3d('+newPosition+'px,0,0)', '-webkit-transform':'translate3d('+newPosition+'px,0,0)', 'transition-duration':'0s', '-webkit-transition-duration':'0s'});
				}else{
					_l.css('left', newPosition);
				}
				return false;
			},
			endDrag = function(e){
				_l.off('mousemove', moveDrag).css('cursor', '');
				if(window.removeEventListener)_l[0].removeEventListener('touchmove', moveDrag, true);
				if(!!_this.data('lastEdit')){
					if(!moved){cancelLast(e);return false}
				}
				if(!moved){
					_this.find('a').removeData('hasbind');
					return;
				};
				var left = useTransform ? _l.transform().x : _l.position().left;
				if(left<=-btnWidth/2){
					if(useTransform){
						_l.css({transform:'translate3d('+(-btnWidth)+'px,0,0)', '-webkit-transform':'translate3d('+(-btnWidth)+'px,0,0)', 'transition-duration':'', '-webkit-transition-duration':''});
					}else{
						_l.animate({left:-btnWidth}, 200, 'easeout');
					};
					_this.data('lastEdit', _l);
				}else{
					if(useTransform){
						_l.css({transform:'', '-webkit-transform':'', 'transition-duration':'', '-webkit-transition-duration':''});
						setTimeout(function(){btn.hide().removeData('curRow')}, 200);
					}else{
						_l.animate({left:0}, 200, 'easeout', function(){btn.hide().removeData('curRow')});
					};
					_this.removeData('lastEdit');
					list.find('.overlay-div').remove();
				};
				return false;
			};
			_l.unselect().on('mousedown', startDrag).on('mouseup', endDrag)
			.on('dragstart', 'img, a', function(e){e.preventDefault()});
			if(window.addEventListener){
				this.addEventListener('touchstart', startDrag, true);
				this.addEventListener('touchend', endDrag, true);
				this.addEventListener('touchcancel', endDrag, true);
			}
			if($.browser.android){
				_l.on('mousemove', moveDrag).css('cursor', 'move');
				if(window.addEventListener)this.addEventListener('touchmove', moveDrag, true);
			}
			function cancelLast(e){
				e.preventDefault();
				var lastEdit = _this.data('lastEdit');
				_this.removeData('lastEdit');
				if(useTransform){
					lastEdit.css({transform:'', '-webkit-transform':'', 'transition-duration':'', '-webkit-transition-duration':''});
					setTimeout(function(){btn.hide().removeData('curRow')}, 200);
				}else{
					lastEdit.animate({left:0}, 200, 'easeout', function(){btn.hide().removeData('curRow')});
				};
				list.find('.overlay-div').remove();
			}
		});
	});
};

//惯性拖拽
$.fn.inertia = function(options){
	var options = $.extend({
		lockX : false, //锁定水平位置
		lockY : false, //锁定垂直位置
		fixed : null, //摩擦与缓动的前后固定的位置(例如selectpicker),如果设置必须返回{start:数值, end:数值}
		start : null, //滚动前执行
		move : null, //滚动时执行
		end : null, //滚动停止后执行
		complete : null //脚本执行完成后执行
	}, options);
	return this.each(function(){
		if(!!$(this).data('inertia'))return true;
		$(this).data('inertia', true);
		var _this = $(this), mouseStart = {}, startPos = {}, lastPos = {}, dragSpeed = {x:0, y:0}, timer = null, moveX = 0, moveY = 0, flg = false,
		scrollWidth = 0, scrollHeight = 0, fixedStart = 0, fixedEnd = 0, stop = function(e){
			if(e.preventDefault)e.preventDefault();
			e.returnValue = false;
			return false;
		},
		startDrag = function(e){
			stop(e);
			_this.find('a').on('click dragstart', stop);
			flg = true;
			moveX = 0;
			moveY = 0;
			var x = _this.transform().x, y = _this.transform().y;
			mouseStart.x = $.touches(e).x;
			mouseStart.y = $.touches(e).y;
			startPos.x = lastPos.x = x;
			startPos.y = lastPos.y = y;
			if(timer)clearInterval(timer);
			_this.on('mousemove', moveDrag).css('cursor', 'move');
			if(window.addEventListener)_this[0].addEventListener('touchmove', moveDrag, true);
			if($.isFunction(options.start))options.start.call(_this, x, y);
		},
		moveDrag = function(e){
			if(flg){
				stop(e);
				var x = $.touches(e).x, y = $.touches(e).y;
				moveX = x - mouseStart.x;
				moveY = y - mouseStart.y;
				x = x - mouseStart.x + startPos.x;
				y = y - mouseStart.y + startPos.y;
				if(options.lockX)x = startPos.x;
				if(options.lockY)y = startPos.y;
				dragSpeed.x = x - lastPos.x;
				dragSpeed.y = y - lastPos.y;
				lastPos.x = x;
				lastPos.y = y;
				if(x>fixedStart)x = fixedStart + moveX*0.2;
				if(x<scrollWidth-fixedEnd)x = scrollWidth - fixedEnd + moveX*0.2;
				if(y>fixedStart)y = fixedStart + moveY*0.2;
				if(y<scrollHeight-fixedEnd)y = scrollHeight - fixedEnd + moveY*0.2;
				_this.css({transform:'translate3d('+x+'px,'+y+'px,0)', '-webkit-transform':'translate3d('+x+'px,'+y+'px,0)',
					'transition-duration':'0s', '-webkit-transition-duration':'0s'});
				if($.isFunction(options.move))options.move.call(_this, lastPos.x, lastPos.y);
			}
		},
		endDrag = function(e){
			stop(e);
			flg = false;
			_this.off('mousemove', moveDrag).css('cursor', '');
			if(window.addEventListener)_this[0].removeEventListener('touchmove', moveDrag, true);
			if(moveX==0 && moveY==0){
				_this.find('a').off('click dragstart', stop);
				return;
			}
			if(timer)clearInterval(timer);
			timer = setInterval(function(){
				var x = _this.transform().x, y = _this.transform().y;
				if(Math.abs(dragSpeed.x)<=1 && Math.abs(dragSpeed.y)<=1){
					clearInterval(timer);timer = null;
					if($.isFunction(options.end))options.end.call(_this, x, y);
				}else{
					_this.css({
						transform:'translate3d('+(x+dragSpeed.x)+'px,'+(y+dragSpeed.y)+'px,0)',
						'-webkit-transform':'translate3d('+(x+dragSpeed.x)+'px,'+(y+dragSpeed.y)+'px,0)',
						'transition-duration':'0s',
						'-webkit-transition-duration':'0s'
					});
					if(x>fixedStart || x<scrollWidth-fixedEnd || y>fixedStart || y<scrollHeight-fixedEnd){
						dragSpeed.x *= 0.4;
						dragSpeed.y *= 0.4;
					};
					dragSpeed.x *= 0.95;
					dragSpeed.y *= 0.95;
				}
			}, $.browser.mobile ? 30 : 10);
		};
		if($.isFunction(options.fixed)){
			var fixed = options.fixed.call(_this);
			if($.isPlainObject(fixed) && !isNaN(fixed.start) && !isNaN(fixed.end)){
				fixedStart = Number(fixed.start);
				fixedEnd = Number(fixed.end);
			}
		}
		if($.isFunction(options.complete))options.complete.call(_this);
		setTimeout(function(){
			scrollWidth = _this.parent().width() - _this.width();
			scrollHeight = _this.parent().height() - _this.height();
		}, 300);
		_this.unselect().on('mousedown', startDrag).on('mouseup mouseleave', endDrag)
		.find('img').on('dragstart', function(e){e.preventDefault()});
		if(window.addEventListener){
			_this[0].addEventListener('touchstart', startDrag, true);
			_this[0].addEventListener('touchend', endDrag, true);
			_this[0].addEventListener('touchcancel', endDrag, true);
		}
	});
};

//百度地图
/*
需要先在调用页面添加
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=8dCDnV31Xg1QBbrWyrHmquR3"></script>
百度地图api申请的密钥, http://lbsyun.baidu.com/apiconsole/key
http://developer.baidu.com/map/jsdemo.htm
*/
$.fn.baiduMap = function(options){
	options = $.extend({
		longitude : '', //经度, 113.440685
		latitude : '', //纬度, 23.136588
		level : 16, //默认地图级别
		lngObj : '', //存放经度数值的控件expr, 一般为输出用
		latObj : '', //存放纬度数值的控件expr
		centerCity : '广州', //默认显示城市, options.longitude,options.latitude两个值为空才有效
		getCoordinate : null, //点击地图获取坐标且返回经度与纬度, function
		isNav : false, //使用导航功能
		autoNav : true, //自动使用地点导航, 以当前位置导航到 (options.longitude, options.latitude)
		navType : -1 //非自动导航时的驾车策略, 0:最少时间, 1:最短距离, 2:避开高速
	}, options);
	return this.each(function(k){
		var _this = $(this), map = null;
		if(!!_this.data('baiduMap')){
			map = _this.data('map');
			if(!isNaN(options.longitude) && !isNaN(options.latitude) && options.longitude*1>0 && options.latitude*1>0){
				if(!!_this.data('lngObj'))$(_this.data('lngObj')).val(options.longitude);
				if(!!_this.data('latObj'))$(_this.data('latObj')).val(options.latitude);
				setOverlays(options.longitude, options.latitude);
				if(!!_this.data('getCoordinate'))_this.data('getCoordinate').call(_this, options.longitude, options.latitude);
			}
		}else{
			_this.data('baiduMap', true);
			_this.data('level', options.level);
			if(options.lngObj.length)_this.data('lngObj', options.lngObj);
			if(options.latObj.length)_this.data('latObj', options.latObj);
			if($.isFunction(options.getCoordinate))_this.data('getCoordinate', options.getCoordinate);
			var _id = _this.attr('id');
			if(!!!_id){
				var td = new Date();
				_id = td.getFullYear()+''+(td.getMonth()+1)+''+td.getDate()+''+td.getHours()+''+td.getMinutes()+''+td.getSeconds()+''+k;
				_this.attr('id', _id);
			};
			map = new BMap.Map(_id);
			//map.addControl(new BMap.NavigationControl());
			_this.data('map', map);
			if(!isNaN(options.longitude) && !isNaN(options.latitude) && options.longitude*1>0 && options.latitude*1>0){
				if(options.lngObj.length)$(options.lngObj).val(options.longitude);
				if(options.latObj.length)$(options.latObj).val(options.latitude);
				map.centerAndZoom(new BMap.Point(options.longitude, options.latitude), options.level); //初始化地图,设置中心点坐标和地图级别
				setOverlays(options.longitude, options.latitude);
			}else{
				map.centerAndZoom(options.centerCity, options.level);
			}
			if($.isFunction(options.getCoordinate) || (options.lngObj.length && options.latObj.length))map.addEventListener('click', function(e){
				if(options.lngObj.length)$(options.lngObj).val(e.point.lng);
				if(options.latObj.length)$(options.latObj).val(e.point.lat);
				setOverlays(e.point.lng, e.point.lat);
				if($.isFunction(options.getCoordinate))options.getCoordinate.call(_this, e.point.lng, e.point.lat);
			});
			if(options.isNav){
				if(options.autoNav){
					var geolocation = new BMap.Geolocation();
					geolocation.getCurrentPosition(function(r){
						if(this.getStatus() == BMAP_STATUS_SUCCESS){
							var longitude = r.point.lng, latitude = r.point.lat;
							var p1 = new BMap.Point(longitude, latitude), p2 = new BMap.Point(options.longitude, options.latitude);
							var driving = new BMap.DrivingRoute(map, {renderOptions:{map:map, autoViewport:true}});
							driving.search(p1, p2);
						}else{
							$.overloadError('failed:'+this.getStatus());
						}
					}, {enableHighAccuracy:true});
				}else{
					var navView = $('<div class="navView">\
						<select><option value="0">最少时间</option><option value="1">最短距离</option><option value="2">避开高速</option></select>\
						<input type="text" placeholder="起始地" /><input type="text" placeholder="目的地" />\
						<a href="javascript:void(0)">查询</a>\
						<div style="height:0;font-size:0;clear:both;overflow:hidden;box-sizing:border-box;"></div>\
					</div>');
					_this.css({position:'relative'}).append(navView);
					navView.css({position:'absolute', 'z-index':1000, left:0, bottom:0, width:'100%', height:'auto', background:'rgba(255,255,255,0.95)', padding:'10px 10px 7px 10px'})
					.find('select,input').css({float:'left', width:'80px', height:'24px', 'font-size':'12px', border:'1px #ccc solid', 'border-radius':'3px', 'margin-right':'10px', 'margin-bottom':'3px'});
					navView.find('input').css({width:'100px', 'padding-left':'5px', 'box-sizing':'border-box'});
					navView.find('a').css({float:'left', display:'block', width:'50px', height:'24px', 'line-height':'24px', 'font-size':'12px', background:'#d60000', color:'#fff', 'border-radius':'3px', 'text-align':'center', 'text-decoration':'none', 'margin-bottom':'3px'});
					var routePolicy = [BMAP_DRIVING_POLICY_LEAST_TIME, BMAP_DRIVING_POLICY_LEAST_DISTANCE, BMAP_DRIVING_POLICY_AVOID_HIGHWAYS],
						navType = Math.floor(options.navType);
					if(navType>-1 && navType<3)navView.find('select').hide();
					navView.find('a').click(function(){
						var i = (navType>-1 && navType<3) ? navType : navView.find('select').val(),
							start = navView.find('input:eq(0)').val(), end = navView.find('input:eq(1)').val();
						if(!start.length || !end.length){
							$.overloadError('请输入起始地与目的地');
							return;
						};
						map.clearOverlays();
						var driving = new BMap.DrivingRoute(map, {renderOptions:{map:map, autoViewport:true}, policy:routePolicy[i]});
						driving.search(start, end);
					});
				}
			}
		}
		function setOverlays(longitude, latitude){
			map.clearOverlays();
			var newPoint = new BMap.Point(longitude, latitude), marker = new BMap.Marker(newPoint); //创建标注
			map.addOverlay(marker); //将标注添加到地图中
			map.panTo(newPoint);
		}
	});
};

//提示窗链接
$.fn.alertURL = function(options){
	var options = $.extend({
		width : 630, //宽度
		height : 400 //高度
	}, options);
	return this.each(function(){
		var _this = $(this), href = _this.attr('href'), width = options.width, height = options.height;
		if(!!_this.data('alertURL'))return true;
		if(href.substring(0,11)=='javascript:' || href.substring(0,1)=='#')return true;
		_this.data('alertURL', true);
		_this.click(function(e){
			var opt, title = _this.attr('title')||_this.attr('alt')||_this.html()||_this.val()||'Title';
			if(/\bwidth=(\d+)/i.test(href))width = href.replace(/^.+?\bwidth=(\d+)\b.*$/i,'$1');
			if(/\bheight=(\d+)/i.test(href))height = href.replace(/^.+?\bheight=(\d+)\b.*$/i,'$1');
			opt = $.extend({}, options, {html:href, width:width, height:height, title:title});
			alertUI(opt);
			//e.preventDefault();
			return false;
		});
	});
};

//HTML5拖拽上传
$.fn.html5upload = function(options){
	var options = $.extend({
		url : '/upload', //文件数据处理URL
		name : 'filename', //上传字段标示
		once : false, //不需要经过change之类的操作,立刻上传(控件已选择了文件)(不支持非file控件)
		data : null, //附加参数
		dataType : 'json', //请求类型
		dragover : null, //拖动到对象上时执行
		dragleave : null, //拖动到对象外面时执行
		before : null, //上传之前执行,返回false即取消上传
		progress : null, //数据正在被post的过程中周期性执行
		success : null //post操作成功时执行
	}, options),
	crlf = '\r\n', dashes = '--', ie8 = $.browser.ie8;
	return this.each(function(){
		var _this = $(this), dat = {}, boundary = 'WebKitFormBoundary'+$.randomCode(16);
		if(!!options.data){
			if($.isPlainObject(options.data)){
				dat = options.data;
			}else if($.isFunction(options.data)){
				dat = options.data();
			}else{
				eval("s="+options.data);
				if($.isPlainObject(s)){
					dat = s;
				}else if($.isFunction(s)){
					dat = s();
				}else{
					dat = {};
				}
			}
		}
		if(_this.is(':file')){
			_this.on('change', function(){once.call(this)});
			if(options.once)once.call(_this[0]);
			function once(){
				if(ie8){_this.ajaxupload({url:options.url, data:dat, before:options.before, callback:options.success});return false};
				if($.isFunction(options.before)){
					var result = options.before.call(_this, this.files.length);
					if(typeof(result)=='boolean' && !result)return false;
				}
				var files = this.files;
				for(var i=0; i<files.length; i++){
					fileHandler(files[i]);
				}
			}
		}else{
			_this.on('dragover', function(e){
				e.preventDefault();
				if($.isFunction(options.dragover))options.dragover.call(_this);
				return false;
			}).on('dragleave', function(e){
				e.preventDefault();
				if($.isFunction(options.dragleave))options.dragleave.call(_this);
				return false;
			}).on('drop', function(e){
				e.preventDefault();
				var files = e.originalEvent.dataTransfer.files;
				if($.isFunction(options.before)){
					var result = options.before.call(_this, files.length);
					if(typeof(result)=='boolean' && !result)return false;
				}
				for(var i=0; i<files.length; i++){
					fileHandler(files[i]);
				};
				return false;
			});
		}
		function uploadHttpData(text, type){
			var data = text;
			if(type=='xml')data = text;
			// If the type is 'script', eval it in global context
			if(type=='script')$.globalEval(data);
			// Get the JavaScript object, if JSON is used.
			if(type=='json')eval('data='+data);
			// evaluate scripts within html
			if(type=='html')$('<div></div>').html(data).evalScripts();
			return data;
		}
		function fileHandler(file){
			var fr = new FileReader();
			fr.readAsDataURL(file);
			var xml = new XMLHttpRequest();
			xml.addEventListener('load', function(e){
				if($.isFunction(options.success))options.success.call(_this, uploadHttpData(e.target.responseText, options.dataType));
			}, false);
			xml.upload.addEventListener('progress', function(e){
                if(e.lengthComputable){
					if($.isFunction(options.progress))options.progress.call(_this, e.loaded, e.total);
					/*
					var loaded = Math.ceil((e.loaded / e.total) * 100);
					$(expr).css({width:loaded*2});
					*/
                }
			}, false);
			xml.open('POST', options.url, true);
			if(file.getAsBinary){
				var data = dashes + boundary + crlf + 'Content-Disposition: form-data;' + 'name="' + options.name + '";' + 'filename="' + unescape(encodeURIComponent(file.name)) + '"' + crlf + 'Content-Type: application/octet-stream' + crlf+crlf + file.getAsBinary() + crlf;
				for(var d in dat)data += dashes + boundary + crlf + 'Content-Disposition: form-data;' + 'name="' + d + '"' + crlf+crlf + dat[d] + crlf;
				data += dashes + boundary + dashes;
				xml.setRequestHeader('Content-Type', 'multipart/form-data;boundary=' + boundary);
				xml.sendAsBinary(data);
			}else if(window.FormData){
				var fd = new FormData();
				fd.append(options.name, file);
				for(var d in dat)fd.append(d, dat[d]);
				xml.send(fd);
			};
			return false;
		}
	});
};

//选项卡, options为数字时即选项卡切换到该数字索引的选项卡
$.fn.tabs = function(options){
	var idx = false;
	if(typeof options=='number'){
		idx = options;
	}else{
		options = $.extend({
			list : 'li', //选项卡内容列表的标签
			tab : 'li', //生成的选项卡标题列表容器的标签
			tabcls : 'tab-title', //生成的选项卡标题列表容器的类名
			tabhovercls : 'hover', //选项卡标题列表的鼠标悬停类名
			tabactcls : 'this', //选项卡标题列表的选中后类名
			tabdiscls : 'disabled', //选项卡标题列表的禁用后类名
			tabdis : [], //禁用的选项卡的索引,或在内容列表的标签加上disabled
			index : 0, //默认显示的选项卡
			effect : '', //切换动画(需导入css),[空|movex|movey|opacity|scale|scaleout|skew|left|up|rotate|flip]
			act : 'click', //切换选项卡的操作
			auto : 0, //自动转换选项卡时间间隔,0:不自动
			after : null, //切换后执行,一个参数(当前点击的选项卡的索引)
			complete : null //代码完成后执行
		}, options);
	};
	return this.each(function(){
		var _this = $(this), titleparent, list, ani = false;
		if(typeof idx=='number'){
			options = _this.data('options');
			if(!!!options)return true;
			titleparent = $('.'+options.tabcls);
			list = _this.children(options.list).not('.clear');
			var length = titleparent.find(options.tab).length;
			if(!length || idx<0 || idx>=length || titleparent.find(options.tab).eq(idx).hasClass(options.tabdiscls))return true;
			trantab(idx);
		}else{
			var index = options.index, tabdis = options.tabdis;
			list = _this.children(options.list).not('.clear');
			if(!list.length || !options.tabcls.length)return true;
			_this.data({ 'options':options, 'tabindex':index });
			list.hide();
			titleparent = options.tab=='li' ? $('<ul></ul>') : $('<div></div>');
			_this.before(titleparent);
			titleparent.addClass(options.tabcls);
			if(tabdis.length>0)tabdis = $.map(tabdis, function(n){return n*1});
			list.each(function(i){
				var el = $(this), tab = $('<'+options.tab+' />');
				titleparent.append(tab);
				if(index==i){
					el.css('display','');
					tab.addClass(options.tabactcls);
				}
				if(!!el.attr('title')){
					tab.html('<span>'+el.attr('title')+'</span>');
					el.removeAttr('title');
				}else{
					tab.html('<span>tab'+(i+1)+'</span>');
				}
				if($.inArray(i, tabdis)>-1 || !!el.attr('disabled'))tab.addClass(options.tabdiscls);
				el.attr('lh', el.outerHeight(true));
				tab.on(options.act, function(){
					if($(this).hasClass(options.tabdiscls))return false;
					trantab(tab.index());
				}).hover(function(){
					if(!$(this).hasClass(options.tabdiscls))tab.addClass(options.tabhovercls);
				},function(){
					if(!$(this).hasClass(options.tabdiscls))tab.removeClass(options.tabhovercls);
				});
			});
			titleparent.append('<p style="clear:both;font-size:0;width:auto;height:0;overflow:hidden;margin:0;padding:0;"></p>');
			if($.browser.ie7){
				var titwidth = 0;
				titleparent.find(options.tab).each(function(){
					titwidth += $(this).outerWidth(true);
				});
				titleparent.width(titwidth);
			}
			if(options.effect!=''){
				_this.css({width:_this.innerWidth(), height:list.eq(index).attr('lh'), overflow:'hidden'});
				if(options.effect=='movex' || options.effect=='movey'){
					_this.css('position', 'relative');
					list.css('position', 'absolute');
				}else if(options.effect=='opacity'){
					list.css('opacity', 0).eq(index).css('opacity', 1);
				}else{
					if($.browser.ie9){
						list.css('opacity', 0).eq(index).css('opacity', 1);
					}else{
						list.addClass('transition').addClass('hide'+options.effect).eq(index).addClass('show'+options.effect);
					}
				}
			}
			if(options.auto>0){
				_this.hover(function(){_this.data('tabs',true)},function(){_this.removeData('tabs')});
				titleparent.hover(function(){titleparent.data('tabs',true)},function(){titleparent.removeData('tabs')});
				setInterval(function(){
					if(!!_this.data('tabs') || !!titleparent.data('tabs'))return;
					var i = titleparent.find('.'+options.tabactcls).index() + 1;
					if(i>=list.length)i = 0;
					while(!!list.eq(i).attr('disabled')){
						i++;
						if(i>=list.length)i = 0;
					};
					trantab(i);
				}, options.auto);
			}
			if($.isFunction(options.complete))options.complete.call(_this);
		}
		function trantab(i){
			if(ani)return;
			var index = _this.data('tabindex') * 1;
			if(index==i)return;
			ani = true;
			var tab = titleparent.find(options.tab);
			tab.removeClass(options.tabactcls).removeClass(options.tabhovercls);
			tab.eq(i).addClass(options.tabactcls);
			if(options.effect==''){
				list.hide();
				list.eq(i).css('display','');
				ani = false;
				_this.data('tabindex', i);
				if($.isFunction(options.after))options.after.call(_this, i);
			}else{
				var lh = list.eq(i).attr('lh') * 1;
				if(options.effect=='movex' || options.effect=='movey'){
					var n = index<i ? -1 : 1, mh;
					_this.animate({height:lh}, 400);
					if(options.effect=='movex'){
						list.eq(index).animate({left:_this.innerWidth()*n}, 400);
						list.eq(i).css({left:_this.innerWidth()*n*-1, top:0, display:''}).animate({left:0}, 400);
					}else{
						mh = Math.max(lh, list.eq(index).attr('lh')*1);
						list.eq(index).animate({top:mh*n}, 400);
						list.eq(i).css({left:0, top:mh*n*-1, display:''}).animate({top:0}, 400);
					}
				}else if(options.effect=='opacity'){
					list.eq(index).animate({opacity:0}, 300, function(){
						_this.animate({height:lh}, 300);
						list.eq(i).css('display','').animate({opacity:1}, 300);
					});
				}else{
					if($.browser.ie9){
						list.eq(index).animate({opacity:0}, 300, function(){
							_this.animate({height:lh}, 300);
							list.eq(i).css('display','').animate({opacity:1}, 300);
						});
					}else{
						list.removeClass('show'+options.effect);
						setTimeout(function(){
							list.eq(i).css('display','');
							_this.animate({height:lh}, 400);
						}, 500);
						setTimeout(function(){list.eq(i).addClass('show'+options.effect)}, 502);
					}
				};
				setTimeout(function(){
					list.eq(index).hide();
					_this.data('tabindex', i);
					ani = false;
					if($.isFunction(options.after))options.after.call(_this, i);
				}, 502);
			}
		}
	});
};

//拖拽
$.fn.drag = function(options){
	options = $.extend({
		target : null, //被移动的对象
		exceptEl : null, //不会触发移动的对象
		area : null, //只能在area区域移动, 否则在body
		lockX : false, //锁定水平位置
		lockY : false, //锁定垂直位置
		lockRange : true, //锁定区域(不能移出area区域)
		lockRangeRelax : null, //放松锁定区域的位置,{left:true, top:true, right:true, bottom:true}
		useTransform : false, //使用CSS3特性来移动
		debug : false, //显示当前移动的位置数据
		before : null, //按下鼠标前执行
		start : null, //按下鼠标后执行
		move : null, //移动时执行
		stop : null, //停止时执行
		complete : null //调用完插件后执行
	}, options);
	return this.each(function(){
		if(!!$(this).stopBounces(true).data('drag'))return true;
		var _this = $(this), x, y, right, bottom, debug = [], range = {top:-9999, left:-9999, right:9999, bottom:9999}, startX, startY, dirX = 0, dirY = 0,
			curX, curY, target = !options.target ? _this : ($.isFunction(options.target) ? options.target.call(_this) : $(options.target));
		_this.data('drag', true);
		if($.browser.mozilla)_this.css('-moz-user-select', 'none');
		if(options.debug){
			if(!$('#drag_debug').length){
				var tranform = target.transform();
				debug = $('<div id="drag_debug" style="position:fixed;margin:0 10px;top:10px;z-index:999;border:1px solid #666;padding:5px;background:rgba(255,255,255,0.95);"></div>');
				$('body').append(debug);
				debug.html('<b>left:</b>'+target.position().left+' / <b>top:</b>'+target.position().top+' / <b>dirX:</b>'+dirX+' / <b>dirY:</b>'+dirY+' / <b>range.left:</b>'+range.left+' / <b>range.top:</b>'+range.top+' / <b>translateX:</b>'+transform.x+' / <b>translateY:</b>'+transform.y);
				if($.browser.ie6){
					debug.css('position', 'absolute');
					$(window).scroll(function(){debug.css('top', $.scroll().top+10)});
				}
			}else{
				debug = $('#drag_debug');
			}
		};
		_this.unselect().hover(function(){$(this).css('cursor','move')}, function(){$(this).css('cursor','')}).on('mousedown', start);
		if(window.addEventListener){
			_this[0].addEventListener('touchstart', start, true);
			_this[0].addEventListener('touchend', stop, true);
			_this[0].addEventListener('touchcancel', stop, true);
		}
		if($.isFunction(options.complete))options.complete.call(_this);
		function start(e){
			if(options.exceptEl!=null){
				var e = e||event, o = e.target||e.srcElement;
				if($(o).is(options.exceptEl))return false;
			}
			if($.isFunction(options.before))options.before.call(_this, e);
			target.css({position:'absolute', 'transition-duration':'0s', '-webkit-transition-duration':'0s'});
			var doc = $.document(), sl = $.scroll().left, st = $.scroll().top, tmargin = target.margin(),
			ml = tmargin.left, mt = tmargin.top, mr = tmargin.right, mb = tmargin.bottom,
			left = target.position().left, top = target.position().top;
			if($.isFunction(options.start))options.start.call(_this, e, {left:left, top:top});
			if(target.parent().css('position')=='relative'){
				left = target.position().left;
				top = target.position().top;
			}
			if( ($.browser.ie7) && (target.is('tr') || target.is('td')) ){
				top = target.parents('table').eq(0).position().top;
				var tar = target.parents('tr').eq(0);
				tar.parent().children().each(function(){
					if($(this).is(tar))return false;
					else top += $(this).height();
				});
			};
			startX = $.touches(e).x;
			startY = $.touches(e).y;
			curX = startX;
			curY = startY;
			x = startX - left + ml;
			y = startY - top + mt;
			if(options.lockRange){
				if(options.area){
					var area = $(options.area);
					range.top = area.offset().top + (Number(area.css('border-top-width').replace(/px/,''))||0)*1;
					range.left = area.offset().left + (Number(area.css('border-left-width').replace(/px/,''))||0)*1;
					range.right = range.left + area.outerWidth(false);
					range.bottom = range.top + area.outerHeight(false);
				}else{
					range = {top:st, left:sl, right:sl+doc.clientWidth, bottom:st+doc.clientHeight};
				}
				if($.isPlainObject(options.lockRangeRelax)){
					if(!options.lockRangeRelax.top)range.top = -9999;
					if(!options.lockRangeRelax.left)range.left = -9999;
					if(!options.lockRangeRelax.right)range.right = 9999;
					if(!options.lockRangeRelax.bottom)range.bottom = 9999;
				}
			};
			right = range.right - target.width() - ml - mr;
			bottom = range.bottom - target.height() - mt - mb;
			target[0].setCapture && target[0].setCapture();
			if(debug.length){
				var transform = target.transform();
				debug.html('<b>left:</b>'+left+' / <b>top:</b>'+top+' / <b>dirX:</b>'+dirX+' / <b>dirY:</b>'+dirY+' / <b>range.left:</b>'+range.left+' / <b>range.top:</b>'+range.top+' / <b>translateX:</b>'+transform.x+' / <b>translateY:</b>'+transform.y);
			};
			$(document).on('mousemove', move).on('mouseup', stop).on('blur', stop);
			if(window.addEventListener)_this[0].addEventListener('touchmove', move, true);
			return false;
		}
		function move(e){
			if(e.preventDefault)e.preventDefault();
			var dx = $.touches(e).x, dy = $.touches(e).y;
			var left = dx - x, top = dy - y;
			if(dx>curX){dirX = 1}else if(dx<curX){dirX = -1}else{dirX = 0}
			if(dy>curY){dirY = 1}else if(dy<curY){dirY = -1}else{dirY = 0};
			curX = dx;
			curY = dy;
			if(debug.length){
				var transform = target.transform();
				debug.html('<b>left:</b>'+left+' / <b>top:</b>'+top+' / <b>dirX:</b>'+dirX+' / <b>dirY:</b>'+dirY+' / <b>range.left:</b>'+range.left+' / <b>range.top:</b>'+range.top+' / <b>translateX:</b>'+transform.x+' / <b>translateY:</b>'+transform.y);
			};
			left = Math.min(Math.max(left, range.left), right);
			top = Math.min(Math.max(top, range.top), bottom);
			if(options.lockX){
				if(options.useTransform){
					left = target.transform().x;
				}else{
					left = target.position().left;
				}
			}
			if(options.lockY){
				if(options.useTransform){
					top = target.transform().y;
				}else{
					top = target.position().top;
				}
			}
			if(options.useTransform){
				target.css({transform:'translate('+left+'px, '+top+'px)', '-webkit-transform':'translate('+left+'px, '+top+'px)'});
			}else{
				target.css({left:left, top:top});
			}
			if($.isFunction(options.move))options.move.call(_this, e, {left:left, top:top, dirX:dirX, dirY:dirY});
			//left:当前水平, top:当前垂直, dirX|dirY:[1(向右|下), -1(向左|上), 0(原位)]
			//Prevent drag selected
			if(document.selection){ //IE, Opera
				if(document.selection.empty)document.selection.empty(); //IE
				else document.selection = null; //Opera
			}else if(window.getSelection){ //FF, Safari
				window.getSelection().removeAllRanges();
			}
		}
		function stop(e){
			target.css({'transition-duration':'', '-webkit-transition-duration':''});
			$(document).off('mousemove', move).off('mouseup', stop).off('blur', stop);
			if(window.removeEventListener)_this[0].removeEventListener('touchmove', move, true);
			target[0].releaseCapture && target[0].releaseCapture();
			var left = 0, top = 0;
			if(options.useTransform){
				left = target.transform().x;
				top = target.transform().y;
			}else{
				left = target.position().left;
				top = target.position().top;
			}
			if($.isFunction(options.stop))options.stop.call(_this, e, {left:left, top:top});
		}
	});
};

//拖曳排序
$.fn.dragsort = function(options){
	options = $.extend({
		dragList : 'li', //移动对象
		dragItem : '', //移动对象内的拖动手柄, 默认为移动对象自己
		dragItemExcept : 'input, textarea, select, a[href]', //不会触发移动的对象
		opacity : 0.9, //鼠标按下后移动对象半透明
		lockRange : false, //锁定区域(只能在this内移动)
		placeHolder : '', //占位符html, 默认值 '<li class="placeHolder"></li>'
		scrollSpeed : 5, //自动滚动的速度, 0为禁止滚动
		before : null, //鼠标按下前执行
		start : null, //鼠标按下后执行
		move : null, //鼠标拖动时执行
		stop : null, //鼠标放开后执行
		after : null, //鼠标放开后执行(位置产生改变才执行)
		complete : null //调用完插件后执行
	}, options);
	return this.each(function(){
		var _this = $(this), scrollX, scrollY, curList = null, placeHolder = null,
			items = _this.find(options.dragList), dragItem = items, scrollSpeed = options.scrollSpeed,
			tagName = items[0].tagName.toLowerCase(), isTr = false,
			placeHolderName = options.placeHolder=='' ? '<'+tagName+' class="placeHolder"></'+tagName+'>' : options.placeHolder;
		if(options.dragItem!='')dragItem = items.find(options.dragItem);
		items.each(function(){
			if(!!$(this).attr('style'))$(this).data('style', $(this).attr('style'));
			$(this).attr('data-width', $(this).outerWidth(false));
			$(this).attr('data-height', $(this).outerHeight(false));
			$(this).attr('data-left', $(this).position().left);
			$(this).attr('data-top', $(this).position().top);
		});
		if(items.is('tr'))options.lockRange = true;
		var area = options.lockRange ? _this : null;
		dragItem.drag({
			target : function(){return this.parents(tagName).eq(0)},
			area : area,
			lockRange : options.lockRange,
			exceptEl : options.dragItemExcept,
			before : function(e){
				curList = this.parents(tagName).eq(0);
				if($.isFunction(options.before))options.before.call(curList);
				isTr = curList.is('tr');
				if(isTr)curList.children().each(function(){
					$(this).css('width', $(this).outerWidth(false));
				});
			},
			start : function(e){
				var offset = curList.offset();
				curList.attr('data-position', offset.left+'|'+offset.top).css({
					position:'absolute', 'z-index':888, left:offset.left, top:offset.top,
					opacity:options.opacity, width:curList.attr('data-width'), height:curList.attr('data-height')
				});
				placeHolder = $(placeHolderName);
				curList.after(placeHolder);
				if($.isFunction(options.start))options.start.call(curList);
			},
			move : function(e, d){
				var currentX = $.scroll().left + $.touches(e).x, currentY = $.scroll().top + $.touches(e).y,
					offset = curList.offset(), width = curList.outerWidth(false), height = curList.outerHeight(false);
				items.not(curList).each(function(){
					var item = $(this), left1 = item.offset().left, top1 = item.offset().top,
						left2 = left1 + item.outerWidth(false), top2 = top1 + item.outerHeight(false);
					if((isTr ? 1 : (left1<currentX && currentX<left2)) && top1<currentY && currentY<top2){
						if(((isTr ? 1==0 : offset.left<left1+width/2) && d.dirX<0) || (offset.top<top1+height/2 && d.dirY<0)){
							item.before(placeHolder);
						}else if(((isTr ? 1==0 : offset.left+width>left2-width/2) && d.dirX>0) || (offset.top+height>top2-height/2 && d.dirY>0)){
							item.after(placeHolder);
						}
						return false;
					}
				});
				if(scrollSpeed){
					clearInterval(scrollX);
					clearInterval(scrollY);
					var left1 = _this.offset().left, left2 = left1 + _this.outerWidth(false), top1 = _this.offset().top, top2 = top1 + _this.outerHeight(false);
					if(currentX<left1)scrollX = setInterval(function(){_this.scrollLeft(_this.scrollLeft()-3)}, scrollSpeed);
					if(currentX>left2)scrollX = setInterval(function(){_this.scrollLeft(_this.scrollLeft()+3)}, scrollSpeed);
					if(currentY<top1)scrollY = setInterval(function(){_this.scrollTop(_this.scrollTop()-3)}, scrollSpeed);
					if(currentY>top2)scrollY = setInterval(function(){_this.scrollTop(_this.scrollTop()+3)}, scrollSpeed);
				}
				if($.isFunction(options.move))options.move.call(curList, d);
			},
			stop : function(e){
				var offset = placeHolder.offset();
				curList.animate({left:offset.left, top:offset.top}, 200, function(){
					clearInterval(scrollX);
					clearInterval(scrollY);
					curList.removeAttr('style');
					if(!!curList.data('style'))curList.attr('style', curList.data('style'));
					if(curList.attr('data-position')!=offset.left+'|'+offset.top && $.isFunction(options.after))options.after.call(curList);
					placeHolder.after(curList);
					placeHolder.remove();
					placeHolder = null;
					if(isTr)curList.children().each(function(){
						$(this).css('width', '');
					});
					if(curList.attr('data-position')!=offset.left+'|'+offset.top && $.isFunction(options.stop))options.stop.call(curList);
				});
			}
		});
		if($.isFunction(options.complete))options.complete.call(_this);
	});
};

//占位符
$.fn.placeholder = function(string){
	var _this = this;
	if(!!_this.data('placeholder.skip'))return fn();
	if('placeholder' in document.createElement('input')){ //如果原生支持placeholder属性, 则返回对象本身
		if(typeof string=='undefined')return _this;
		else return _this.attr('placeholder', string);
	}else{
		_this.on('placeholder', function(){_this.val('').data('placeholder.label').show()});
		return fn();
	}
	function fn(){
		if(!string || !!!_this.attr('id'))return _this;
		return _this.each(function(){
			if(!!$(this).data('placeholder'))return true;
			var _this = $(this).data('placeholder', true), position = _this.position(), padding = _this.padding(), label = $('label[for="'+_this.attr('id')+'"]');
			if(label.length){
				_this.data('placeholder.label', label)
			}else{
				label = $('<label class="placeholder" for="'+_this.attr('id')+'" style="display:block;cursor:text;">'+string+'</label>');
				_this.data('placeholder.label', label).before(label);
				label.css({position:'absolute', top:position.top+padding.top, left:position.left+padding.left+3, height:_this.height(), 'line-height':_this.height()+'px'});
			}
			if(_this.val().length)label.hide();
			_this.blur(function(){
				if(!_this.val().length)label.show();
			}).keyup(function(){
				if(!_this.val().length)label.show();
				else label.hide();
			});
		});
	}
};

//鼠标经过且隔行变色
$.fn.interlace = function(options){
	options = $.extend({
		el : 'tr', //变色对象
		even : 'even', //双数行样式名
		odd : 'odd', //单数行样式名
		hover : '', //鼠标经过样式名, 变色对象增加skip自定义属性后以下参数无效
		click : '', //鼠标点击样式名
		one : null, //第一次点击执行
		two : null //第二次点击执行
	}, options);
	return this.each(function(){
		var _this = $(this), el = _this.find(options.el);
		if(options.even!='')el.filter(':even').addClass(options.even);
		if(options.odd!='')el.filter(':odd').addClass(options.odd);
		el.each(function(){
			var el = $(this);
			if(!!!el.attr('skip')){
				el.hover(function(){
					if(options.hover!='')$(this).addClass(options.hover);
				}, function(){
					if(options.hover!='')$(this).removeClass(options.hover);
				});
				if(options.click!='' || $.isFunction(options.one) || $.isFunction(options.two)){
					el.click(function(){
						if(!!!$(this).data('interlace')){
							$(this).data('interlace', true);
							if(options.click!='')$(this).addClass(options.click);
							if($.isFunction(options.one))options.one.call(_this);
						}else{
							$(this).removeData('interlace');
							if(options.click!='')$(this).removeClass(options.click);
							if($.isFunction(options.two))options.two.call(_this);
						}
					});
				}
			}
		});
	});
};

//浮动提示
$.fn.tips = function(options){
	var options = $.extend({
		cls : 'tips', //使用类名
		w : 250, //图片宽
		h : 250, //图片高
		p : '' //图片路径
	}, options);
	return this.each(function(){
		var _this = $(this), id = _this.attr('id')||_this.attr('name')||new Date().getTime()+''+Math.ceil(Math.random()*999+100),
		p = _this.attr('p')||options.p, w = _this.attr('w')||options.w, h = _this.attr('h')||options.h, tips = $('#'+id+'_tips');
		_this.attr('id', id);
		if(!!_this.attr('alt'))_this.attr('tips',_this.attr('alt'));
		if(!!_this.attr('title')){_this.attr('tips',_this.attr('title'));_this.removeAttr('title')};
		if(!!!p && !!!_this.attr('tips'))return true;
		_this.hover(function(){
			if(!$('#'+id+'_tips').length){
				tips = $('<div id="'+id+'_tips"></div>').addClass(options.cls);
				$(document.body).append(tips);
				if(!!p){
					tips.css({width:w, height:h});
					tips.append('<img src="'+p+'" border="0" />');
				}
			}
			if(!!!p)tips.html(_this.attr('tips'));
			if(!!!_this.data('tipsFn')){
				_this.data('tipsFn', true);
				var fn = _this.attr('fn');
				if(!!fn){
					var func;
					eval("func = "+fn);
					if($.isFunction(func))func.call(_this, tips);
				}
			};
			tips.show();
		},function(){
			tips.hide();
		}).mousemove(function(e){
			var doc = $.document(),
			sl = $.scroll().left, st = $.scroll().top, x = e.clientX, y = e.clientY, mx = x+10+sl, my = y+10+st;
			if((x+10+tips.outerWidth(false))>doc.clientWidth){mx = x-tips.outerWidth(false)+sl}
			if((y+10+tips.outerHeight(false))>doc.clientHeight){my = y-tips.outerHeight(false)+st};
			tips.css({left:mx, top:my});
			if($.browser.ie6){
				var l1 = mx, t1 = my, l2 = mx+tips.outerWidth(false), t2 = my+tips.outerHeight(false), selects = $("select");
				selects.each(function(){
					var x = $(this).position().left, y = $(this).position().top, w = $(this).width(), h = $(this).height();
					if(l2>=x && x+w>=l1 && t2>=y && y+h>=t1){
						$(this).css('visibility','hidden');
						$(this).data('visibility');
					}else{
						if(!!$(this).data('visibility')){
							$(this).css('visibility','');
							$(this).removeData('visibility');
						}
					}
				});
			}
			/*x: 设置或得到鼠标相对于目标事件的父元素的外边界在x坐标上的位置
			clientX: 相对于客户区域的x坐标位置, 不包括滚动条
			offsetX: 设置或得到鼠标相对于目标事件的父元素的内边界在x坐标上的位置
			screenX: 相对于屏幕*/
		});
	});
};

//动画滚动
$.fn.scrollto = function(options){
	options = $.extend({
		el : null, //滚动位置
		speed : 800, //滚动速度
		easing : 'easeout', //滚动效果
		before : null, //滚动前执行
		after : null //滚动后执行
	}, options);
	return this.each(function(){
		var _this = $(this), el = options.el, left = 0, top = 0, doc = $.document();
		if($.isFunction(options.before))options.before.call(_this);
		left = getEl(el, 1);
		top = getEl(el, 0);
		_this.stop(true, false).animate({scrollLeft:left, scrollTop:top}, options.speed, options.easing, function(){
			if($.isFunction(options.after))options.after.call(_this);
		});
		function getEl(ele, d){
			var n = 0;
			switch(typeof ele){
				case 'number':
					n = ele;
					break;
				case 'string':
					if(ele.length){
						var d, re = /^(([+-])?\d+(\.\d+)?)(px|%)?$/;
						if(re.test(ele)){
							var num, t = d ? _this.scrollLeft() : _this.scrollTop();
							while( (r=re.exec(ele))!=null ){
								num = r[1] * 1;
								if(typeof r[4]=='undefined' || r[4]!='px'){
									n = (d ? doc.scrollWidth-doc.clientWidth : doc.scrollHeight-doc.clientHeight) * (num/100);
								}else{
									n = typeof r[2]!='undefined' ? t+num : num;
								}
								break;
							}
						}else if(ele=='max'){
							n = d ? doc.scrollWidth-doc.clientWidth : doc.scrollHeight-doc.clientHeight;
						}else{
							n = d ? $(ele).offset().left : $(ele).offset().top;
						}
					}
					break;
				case 'object':
					if(ele){
						if($.isPlainObject(ele)){
							if(d){
								n = typeof ele.left!='undefined' ? getEl(ele.left, d) : _this.scrollLeft();
							}else{
								n = typeof ele.top!='undefined' ? getEl(ele.top, d) : _this.scrollTop();
							}
						}else{
							n = d ? $(ele).offset().left : $(ele).offset().top;
						}
					}
					break;
			}
			return n;
		}
	});
};

//固定浮动
$.fn.fixed = function(options){
	options = $.extend({
		dir : 'top', //保持方向
		stay : 0, //保持所在位置, 可以是函数(需返回数值)
		fixed : null, //固定飘浮后执行
		normal : null, //取消固定飘浮后执行
		scroll : null //滚动时执行
	}, options);
	return this.each(function(){
		var _this = $(this), dir = options.dir, stay = $.isFunction(options.stay)?options.stay():$.fixedUnit(options.stay), lt = _this.position()[dir] - stay;
		function position(){
			var scroll = document.documentElement.scrollTop + document.body.scrollTop;
			if(scroll>lt){
				if(!$.browser.ie6)_this.css('position', 'fixed');
				_this.css(dir, ($.browser.ie6 ? scroll+stay : stay)).removeData('normal');
				if($.isFunction(options.fixed) && !!!_this.data('fixed')){
					_this.data('fixed', true);
					options.fixed.call(_this);
				}
			}else{
				_this.css('position', '').css(dir, '').removeData('fixed')
				if($.isFunction(options.normal) && !!!_this.data('normal')){
					options.normal.call(_this);
				}
			}
			if($.isFunction(options.scroll))options.scroll.call(_this);
		}
		function restay(){
			stay = $.isFunction(options.stay)?options.stay():$.fixedUnit(options.stay);
			
		};
		$(window).resize(restay);
		$(window).scroll(position);
		position();
	});
};

//等比例缩放加载图片
$.fn.loadpic = function(options){
	options = $.extend({
		src : null, //直接指定图片的路径
		width : 0, //最大宽度, [数字|百分比(根据调用的元素的实际宽度)]
		height : 0, //最大高度, [数字|百分比(根据调用的元素的实际高度)]
		fill : 0, //最大宽高(填充容器,宽高为0有效)
		range : false, //expr容器内所有图片缩放(设置后只有width、height、load、error、after有效,其他参数无效)
		lazyload : false, //图片延迟加载
		centerX : true, //图片自动水平居中
		centerY : true, //图片自动垂直居中
		imgW : 0, //强制设置图片宽
		imgH : 0, //强制设置图片高
		parentM : true, //容器是否自动居中
		parentW : 0, //设置容器宽
		parentH : 0, //设置容器高
		autoWH : true, //不使用脚本设置宽高
		fillAll : false, //填充容器时强制填满
		resize : true, //false:只使用载入特效,不改变图片大小
		overflow : true, //容器防溢出
		fadeIn : 0, //使用fadeIn渐显
		load : '', //加载动画的[图片|样式],为空即使用内置
		error : 'images/nopic.png', //加载失败显示的图片
		after : null, //完全显示后执行
		complete : null //所有图片载入后执行
	}, options);
	var total = 0, count = this.find('img').length;
	return this.each(function(){
		var _this = $(this), width = options.width, height = options.height, fill = options.fill, range = options.range, lazyload = options.lazyload,
		centerX = options.centerX, centerY = options.centerY, imgW = options.imgW, imgH = options.imgH, parentM = options.parentM,
		parentW = options.parentW, parentH = options.parentH, autoWH = options.autoWH, fillAll = options.fillAll,
		overflow = options.overflow, fadeIn = options.fadeIn, load = options.load, error = options.error, after = options.after,
		complete = options.complete, doc = $.document();
		if(!load.length)load = '.preloader preloader-gray';
		function transrc(img, src, rangeImg, rangeLoad){
			var dem = {}, w = width, h = height;
			if(!imgW || !imgH){
				dem.w = img.width;
				dem.h = img.height;
				if(dem.w==0 || dem.h==0){errimg();return}
				if((width+'').indexOf('%')!=-1)w = (width.replace('%','')/100) * _this.width();
				if((height+'').indexOf('%')!=-1)h = (height.replace('%','')/100) * _this.height();
				if(w && h && !fill){
					if(dem.w/dem.h >= w/h){
						if(dem.w>w){
							dem.h = (dem.h*w)/dem.w;
							dem.w = w;
						}
					}else{
						if(dem.h>h){
							dem.w = (dem.w*h)/dem.h;
							dem.h = h;
						}
					}
				}else if(w!=0 && h==0){
					if(dem.w>w){
						dem.h = (dem.h*w)/dem.w;
						dem.w = w;
					}
				}else if(w==0 && h!=0){
					if(dem.h>h){
						dem.w = (dem.w*h)/dem.h;
						dem.h = h;
					}
				}else{ //填满容器
					_this.css('overflow', 'hidden');
					if(dem.w/dem.h > 1){
						if(dem.h>fill){
							dem.w = (dem.w*fill)/dem.h;
							dem.h = fill;
						}else{
							if(fillAll){
								dem.w = (dem.w*fill)/dem.h;
								dem.h = fill;
							}
						}
					}else{
						if(dem.w>fill){
							dem.h = (dem.h*fill)/dem.w;
							dem.w = fill;
						}else{
							if(fillAll){
								dem.h = (dem.h*fill)/dem.w;
								dem.w = fill;
							}
						}
					}
				};
				dem.w = Number(dem.w);
				dem.h = Number(dem.h);
			}else{
				dem.w = imgW;
				dem.h = imgH;
			}
			if(!range){
				if((width+'').indexOf('%')!=-1)_this.width(dem.w);
				if((height+'').indexOf('%')!=-1)_this.height(dem.h);
				if(options.resize){
					tp.css({width:dem.w, height:dem.h, margin:0, 'text-align':'center'});
					if(centerX)tp.css('margin-left', Number((tp.parent().width()-dem.w)/2)+'px');
					if(centerY)tp.css('margin-top', Number((tp.parent().height()-dem.h)/2)+'px');
					t.removeAttr('width');
					t.removeAttr('height');
					t.css({width:dem.w, height:dem.h});
				};
				t.attr('src', src);
				loading.remove();
				if(fadeIn){
					tp.fadeIn(fadeIn, function(){
						total++;
						if($.isFunction(after))after(t);
						if($.isFunction(complete) && count==total)complete(_this);
					});
				}else{
					tp.show();
					total++;
					if($.isFunction(after))after(t);
					if($.isFunction(complete) && count==total)complete(_this);
				}
			}else{
				if(options.resize){
					rangeImg.removeAttr('width');
					rangeImg.removeAttr('height');
					rangeImg.css({width:dem.w, height:dem.h});
				};
				rangeImg.attr('src', src);
				rangeLoad.remove();
				if(fadeIn){
					rangeImg.fadeIn(fadeIn, function(){
						total++;
						if($.isFunction(after))after(rangeImg);
						if($.isFunction(complete) && count==total)complete(_this);
					});
				}else{
					rangeImg.show();
					total++;
					if($.isFunction(after))after(rangeImg);
					if($.isFunction(complete) && count==total)complete(_this);
				}
			}
		}
		function errimg(rangeImg, rangeLoad){
			var tmp = new Image();
			if($.browser.ie8){
				tmp.onload = function(){transrc(tmp, error, rangeImg, rangeLoad)};
				tmp.onerror = function(){errimg()};
				tmp.src = error;
			}else{
				tmp.src = error;
				$(tmp).on('load', function(){
					transrc(this, error, rangeImg, rangeLoad);
				});
			}
		}
		if(!width && !height && !fill)fill = 120;
		if(!range){
			if(_this.removePlug('loadpic'))_this.removeData('loadpiced');
			var t = _this.find('img:eq(0)'), src = options.src||t.attr('src'), img = new Image(), tp, loading;
			t.attr('url', src).removeAttr('src');
			_this.css({'text-align':'left'});
			if(parentM)_this.css({'margin-left':'auto', 'margin-right':'auto'});
			if(autoWH && (width+'').indexOf('%')==-1)_this.css({width:(imgW||parentW||width||fill)});
			if(autoWH && (height+'').indexOf('%')==-1)_this.css({height:(imgH||parentH||height||fill)});
			if(overflow)_this.css('overflow','hidden');
			if(t.parent().is('a')){t.parent().wrap('<p></p>')}else{t.wrap('<p></p>')};
			tp = t.parents('p');
			tp.hide();
			var loadType = 'class="loadpic" style="background-image:url('+load+');"';
			if(load.charAt(0)=='.')loadType = 'class="loadpic '+load.substring(1)+'"';
			loading = $('<div '+loadType+'></div>');
			tp.before(loading);
			loading.css({
				'margin-left' : Number((_this.width()-loading.width())/2)+'px',
				'margin-top' : Number((_this.height()-loading.height())/2)+'px'
			});
			if(!src){errimg();return true}
			function startLoad(){
				if($.browser.ie8){
					img.onload = function(){transrc(img, src)};
					img.onerror = function(){errimg()};
					img.src = src;
				}else{
					img.src = src;
					$(img).on('load', function(){
						transrc(this, src);
					}).on('error', function(){
						errimg();
					});
				}
			}
			if(lazyload){
				var offsetY = _this.position().top;
				$(window).on('scroll', function(){
					if(_this.data('loadpiced'))return;
					if(document.documentElement.scrollTop+document.body.scrollTop+doc.clientHeight>=offsetY){
						_this.data('loadpiced', true);
						startLoad();
					}
				});
				$(window).trigger('scroll');
			}else{
				startLoad();
			}
		}else{
			if(!width && !height){$.overloadError('When use range parameter must be set the width or height');return true};
			_this.find('img').each(function(){
				var loadType = 'class="loadpic" style="background-image:url('+load+');"';
				if(load.charAt(0)=='.')loadType = 'class="loadpic '+load.substring(1)+'"';
				var rangeImg = $(this), rangeSrc = rangeImg.attr('src'), img = new Image(), rangeLoad = $('<div '+loadType+'></div>');
				if(rangeImg.removePlug('loadpic'))rangeImg.removeData('loadpiced');
				rangeImg.hide().attr('url', rangeSrc).removeAttr('src').before(rangeLoad);
				if(!rangeSrc){errimg(rangeImg, rangeLoad);return true}
				function rangeStartLoad(){
					if($.browser.ie8){
						img.onload = function(){transrc(img, rangeSrc, rangeImg, rangeLoad)};
						img.onerror = function(){errimg(rangeImg, rangeLoad)};
						img.src = rangeSrc;
					}else{
						img.src = rangeSrc;
						$(img).on('load', function(){
							transrc(this, rangeSrc, rangeImg, rangeLoad);
						}).on('error', function(){
							errimg(rangeImg, rangeLoad);
						});
					}
				}
				if(lazyload){
					var offsetY = rangeImg.position().top;
					$(window).on('scroll', function(){
						if(rangeImg.data('loadpiced'))return;
						if(document.documentElement.scrollTop+document.body.scrollTop+doc.clientHeight>=offsetY){
							rangeImg.data('loadpiced', true);
							rangeStartLoad();
						}
					});
					$(window).trigger('scroll');
				}else{
					rangeStartLoad();
				}
			});
		}
	});
};

//等比例缩放加载背景图
$.fn.loadbackground = function(options){
	options = $.extend({
		src : '', //直接指定图片的路径
		load : '', //加载动画的[图片|样式],为空即使用内置
		error : 'images/nopic.png', //加载失败显示的图片
		fadeIn : 300, //使用fadeIn渐显
		complete : null //图片载入后执行
	}, options);
	return this.each(function(){
		var _this = $(this), loading, src = options.src||_this.css('background-image'),
			load = options.load, error = options.error, fadeIn = options.fadeIn, complete = options.complete;
		if(!!_this.data('loadbackground'))return true;
		_this.data('loadbackground', true);
		if(!load.length)load = '.preloader preloader-gray';
		function transrc(src){
			loading.remove();
			_this.css({'background-image':'url('+src+')'});
			if(src == error){
				var size = 'cover', wh = $.browser.mobile ? 115 : 230;
				if(wh>_this.width() || wh>_this.height()){
					size = _this.width()>_this.height() ? 'auto 100%' : '100% auto';
				}else{
					size = wh+'px '+wh+'px';
				};
				_this.css('background-size', size);
			}
			if(fadeIn){
				_this.css('opacity', 0);
				_this.animate({opacity:1}, fadeIn);
			}
			if($.isFunction(complete))complete.call(_this);
		}
		if(!src.length){transrc(error);return true}
		if(/url\(['"]?(.+?)['"]?\)/i.test(src))src = src.replace(/url\(['"]?(.+?)['"]?\)/i, '$1');
		var loadType = ' style="background-image:url('+load+');"';
		if(load.charAt(0)=='.')loadType = ' class="'+load.substring(1)+'"';
		loading = $('<div'+loadType+'></div>'), img = new Image();
		if(_this.css('position')=='static')_this.css('position', 'relative');
		_this.css('background-image', 'none').append(loading);
		if(loading.width()>_this.width() || loading.height()>_this.height()){
			var wh = (_this.width()>_this.height() ? _this.height() : _this.width()) / 3*2;
			loading.css({width:wh, height:wh});
		}
		function startLoad(){
			if($.browser.ie8){
				img.onload = function(){transrc(src)};
				img.onerror = function(){transrc(error)};
				img.src = src;
			}else{
				img.src = src;
				$(img).on('load', function(){
					transrc(src);
				}).on('error', function(){
					transrc(error);
				});
			}
		};
		setTimeout(function(){
			loading.css({
				'background-repeat' : 'no-repeat',
				'background-position' : 'center center',
				'background-size' : 'cover',
				'z-index' : 9999,
				position : 'absolute',
				left : Number((_this.width()-loading.width())/2),
				top : Number((_this.height()-loading.height())/2)
			});
			startLoad();
		}, 0);
	});
};

//选项卡头
$.fn.switchView = function(options){
	options = $.extend({
		list : 'li', //列表
		index : 0, //默认选中
		cls : 'this', //选中的样式
		column : '', //底部滚动条样式, 为空即不显示滚动条
		selectFn : null //选中后执行, 设置后选项卡头里面的所有a连接将return false, this指向列表的每个a链接
	}, options);
	return this.each(function(){
		$(this).removePlug('switchView');
		var _this = $(this), width = _this.width(), height = _this.height(), list = options.list?_this.find(options.list):_this.find('li'),
			index = options.index>-1?options.index:0, totalWidth = 0, wrap = null, div = null;
		if(!list.length)return true;
		wrap = list.wrapAll('<div></div>').parent().css({height:'100%', float:'left'});
		div = wrap.wrap('<div></div>').parent().css({width:'100%', height:'100%', overflow:'auto'});
		list.each(function(){totalWidth += $(this).outerWidth(true)+0.1});
		if(totalWidth<=width){
			var padding = list.padding(), w = width / list.length - padding.left - padding.right;
			list.width(w);
			totalWidth = '100%';
		}else{
			var left = $('<div class="left"></div>').height(height), right = $('<div class="right"></div>').height(height);
			_this.append(left).append(right);
			div.scroll(function(){
				setTimeout(function(){
					var scrollLeft = div.scrollLeft();
					if(scrollLeft>0){
						left.show();
						setTimeout(function(){left.addClass('x')}, 0);
					}else{
						left.removeClass('x');
						setTimeout(function(){left.hide()}, 310);
					}
					if(scrollLeft<div[0].scrollWidth-width){
						right.show();
						setTimeout(function(){right.addClass('x')}, 0);
					}else{
						right.removeClass('x');
						setTimeout(function(){right.hide()}, 310);
					}
				}, 0);
			});
			if(index>0){
				setTimeout(function(){
					var w = 0;
					list.each(function(k){
						var li = $(this);
						if(index==k){
							if(w+li.outerWidth(true) > width)div.scrollLeft(w+li.outerWidth(true)-width);
							return false;
						};
						w += li.outerWidth(true);
					});
					div.scroll();
				}, 0);
			}else{
				div.scroll();
			}
		};
		wrap.width(totalWidth);
		var ele = list.eq(index).addClass(options.cls);
		if(options.column.length){
			var column = $('<div class="'+options.column+'"></div>').css({
				position:'absolute', left:0, bottom:0, 'z-index':1, width:0,
				transform:'translate3d(0,0,0)', '-webkit-transform':'translate3d(0,0,0)',
				'-webkit-transition':'-webkit-transform 300ms ease-out', transition:'transform 300ms ease-out'
			});
			wrap.append(column);
			var x = ele.position().left;
			column.css({width:ele.outerWidth(false), transform:'translate3d('+x+'px,0,0)', '-webkit-transform':'translate3d('+x+'px,0,0)'});
		}
		if($.isFunction(options.selectFn)){
			list.find('a').click(function(){
				list.removeClass(options.cls);
				var ele = $(this).parent().addClass(options.cls), x = ele.position().left;
				if(options.column.length)column.css({width:ele.outerWidth(false), transform:'translate3d('+x+'px,0,0)', '-webkit-transform':'translate3d('+x+'px,0,0)'});
				options.selectFn.call(ele);
				return false;
			});
		}
	});
};

//抛物线动画
$.fn.parabola = function(options){
	options = $.extend({
		maxTop : 20, //默认顶点高度top值
		speed : 1.0, //移动速度
		start : {}, //top, left, width, height
		end : {}, //参数同上
		after : null //动画完成后执行
	}, options);
	return this.each(function(){
		var _this = $(this), settings = $.extend({}, options), start = settings.start, end = settings.end;
		if(!_this.parent().length)$(document.body).append(_this);
		//运动过程中有改变大小
		if(start.width==null || start.height==null)start = $.extend(start, {width:_this.width(), height:_this.height()});
		_this.css({margin:'0', position:'fixed', 'z-index':9999, width:start.width, height:start.height});
		//运动轨迹最高点top值
		var vertex_top = Math.min(start.top, end.top) - Math.abs(start.left - end.left) / 3;
		//可能出现起点或者终点就是运动曲线顶点的情况
		if(vertex_top < settings.maxTop)vertex_top = Math.min(settings.maxTop, Math.min(start.top, end.top));
		else vertex_top = Math.min(start.top, $(window).height()/2);
		//运动轨迹在页面中的top值可以抽象成函数 a = curvature, b = vertex_top, y = a * x*x + b;
		var distance = Math.sqrt(Math.pow(start.top - end.top, 2) + Math.pow(start.left - end.left, 2)),
			steps = Math.ceil(Math.min(Math.max(Math.log(distance) / 0.05 - 75, 30), 100) / settings.speed), //元素移动次数
			ratio = start.top == vertex_top ? 0 : -Math.sqrt((end.top - vertex_top) / (start.top - vertex_top)),
			vertex_left = (ratio * start.left - end.left) / (ratio - 1),
			//特殊情况, 出现顶点left==终点left, 将曲率设置为0, 做直线运动
			curvature = end.left == vertex_left ? 0 : (end.top - vertex_top) / Math.pow(end.left - vertex_left, 2);
		settings = $.extend(settings, {
			count : -1, //每次重置为-1
			steps : steps,
			vertex_left : vertex_left,
			vertex_top : vertex_top,
			curvature : curvature
		});
		move();
		function move(){
			var opt = settings, start = opt.start, count = opt.count, steps = opt.steps, end = opt.end;
			//计算left top值
			var left = start.left + (end.left - start.left) * count / steps,
				top = opt.curvature == 0 ? start.top + (end.top - start.top) * count / steps : opt.curvature * Math.pow(left - opt.vertex_left, 2) + opt.vertex_top;
			//运动过程中有改变大小
			if(end.width!=null && end.height!=null){
				var i = steps / 2,
					width = end.width - (end.width - start.width) * Math.cos(count < i ? 0 : (count - i) / (steps - i) * Math.PI / 2),
					height = end.height - (end.height - start.height) * Math.cos(count < i ? 0 : (count - i) / (steps - i) * Math.PI / 2);
				_this.css({width:width, height:height, 'font-size':Math.min(width, height)+'px'});
			};
			_this.css({left:left, top:top});
			opt.count++;
			var time = window.requestAnimationFrame(move);
			if (count == steps) {
				window.cancelAnimationFrame(time);
				if($.isFunction(opt.after))opt.after.call(_this);
			}
		};
	});
};

//长文章分页
$.fn.splitPage = function(m, l, np){
	if(!np)np = '[#nextpage#]';
	function InsertPageBreak(strText, maxPagesize){
		var strPagebreak, s, ss, ns, i, j, c, IsCount, iCount, strTemp, Temp_String, Temp_Array;
		strPagebreak = np;
		s = strText;
		if(s.length<=maxPagesize)return s;
		//s = s.replace(new RegExp(np.replace(/\[/,'\\[').replace(/\]/,'\\]'), 'ig'), '');
		s = s.replace(/&nbsp;/ig, '<&nbsp;>');
		s = s.replace(/&gt;/ig, '<&gt;>');
		s = s.replace(/&lt;/ig, '<&lt;>');
		s = s.replace(/&quot;/ig, '<&quot;>');
		s = s.replace(/&#39;/ig, '<&#39;>');
		if(s!='' && maxPagesize!=0 && s.indexOf(strPagebreak)==-1){
			IsCount = true;
			Temp_String = '';
			iCount = 0;
			for(i=0; i<s.length; i++){
				c = s.substring(i, i+1);
				if(c=='<'){
					IsCount = false;
				}else if(c=='>'){
					IsCount = true;
				}else{
					if(IsCount){
						if(s.charCodeAt(i)>256){
							iCount+=2;
						}else{
							iCount++;
						}
						if(iCount>=maxPagesize && i<s.length){
							strTemp = s.substring(0,i);
							if(CheckPagination(strTemp, 'table|a|b>|i>|strong|div|span')){
								Temp_String = Temp_String + i + ',';
								iCount = 0;
							}
						}
					}
				}
			}
			if(Temp_String.length>1)Temp_String = Temp_String.substring(0,Temp_String.length-1);
			Temp_Array = Temp_String.split(',');
			ns = s;
			s = '';
			for(i=0; i<=Temp_Array.length; i++){
				ss = ns.substring((!i?0:Number(Temp_Array[i-1])+1), (i==Temp_Array.length?ns.length:Number(Temp_Array[i])+1));
				iCount = 0;
				for(j=0; j<ss.length; j++){
					c = ss.substring(j, j+1);
					if(c=='<'){
						IsCount = false;
					}else if(c=='>'){
						IsCount = true;
					}else{
						if(IsCount){
							if(ss.charCodeAt(j)>256){
								iCount+=2;
							}else{
								iCount++;
							}
						}
					}
				}
				if(iCount>=maxPagesize){
					s += strPagebreak + ss;
				}else{
					s += ss;
				}
			}
			if(s.substring(0,strPagebreak.length)==strPagebreak)s = s.substring(strPagebreak.length, s.length);
		};
		s = s.replace(/<&nbsp;>/ig, '&nbsp;');
		s = s.replace(/<&gt;>/ig, '&gt;');
		s = s.replace(/<&lt;>/ig, '&lt;');
		s = s.replace(/<&quot;>/ig, '&quot;');
		s = s.replace(/<&#39;>/ig, '&#39;');
		return s;
	}
	function CheckPagination(strTemp, strFind){
		var f, i, n, m_ingBeginNum, m_intEndNum, m_strBegin, m_strEnd, FindArray;
		strTemp = strTemp.toLowerCase();
		strFind = strFind.toLowerCase();
		f = false;
		if(strTemp!='' && strFind!=''){
			FindArray = strFind.split('|');
			for(i=0; i<FindArray.length; i++){
				m_strBegin = '<'+FindArray[i];
				m_strEnd   = '</'+FindArray[i];
				m_ingBeginNum = 0;
				m_intEndNum = 0;
				n = -1;
				while(strTemp.indexOf(m_strBegin,n+1)>-1){
					n = strTemp.indexOf(m_strBegin,n+1);
					m_ingBeginNum++;
				};
				n = -1;
				while(strTemp.indexOf(m_strEnd,n+1)>-1){
					n = strTemp.indexOf(m_strEnd,n+1);
					m_intEndNum++;
				};
				if(m_ingBeginNum==m_intEndNum){
					f = true;
				}else{
					f = false;
					break;
				}
			}
			return f;
		}else{
			return f;
		}
	};
	this.each(function(){
		var _this = $(this), html = _this.html().replace(/(^\s*)|(\s*$)/g,'').replace(/(^\n*)|(\n*$)/g,''),
		id = _this.attr('id'), strContent, arrContent, i, n, div, numarea, num;
		strContent = InsertPageBreak(html, m);
		if(strContent.indexOf(np)==-1)return;
		arrContent = strContent.split(np);
		_this.html('');
		n = arrContent.length;
		for(i=0; i<n; i++){
			div = $('<div class="'+id+'_splitpage splitpage">'+arrContent[i]+'</div>');
			_this.append(div);
			if(i)div.hide();
		};
		_this.append('<div><table width="100%" border="0" cellspacing="0" cellpadding="0" class="splitpagearea"><tr><td align="center"><table border="0" cellspacing="0" cellpadding="0"><tr><td id="'+id+'_pagearea"></td></tr></table></td></tr></table></div>');
		var pagearea = $('#'+id+'_pagearea'), prev, next;
		prev = $('<a class="splitnav_d" href="javascript:void(0)">'+(l?'Prev':'上一页')+'</a>');
		next = $('<a class="splitnav" href="javascript:void(0)">'+(l?'Next':'下一页')+'</a>');
		pagearea.append(prev);
		pagearea.append(next);
		for(var i=0; i<n; i++){
			num = $('<a class="'+id+'_splitnum" href="javascript:void(0)">'+(i+1)+'</a>');
			pagearea.find('a:last').before(num);
			num.addClass(i==0?'splitnum_x':'splitnum');
			num.click(function(){
				var splitpage = $('.'+id+'_splitpage'), splitnum = $('.splitnum_x'), i;
				if(this===splitnum[0])return;
				i = $('.'+id+'_splitnum').index($(this));
				splitpage.hide().eq(i).show();
				splitnum.removeClass('splitnum_x').addClass('splitnum');
				$(this).removeClass('splitnum').addClass('splitnum_x');
				if(i==0){prev.removeClass('splitnav').addClass('splitnav_d')}
				else{prev.removeClass('splitnav_d').addClass('splitnav')}
				if(i==splitpage.length-1){next.removeClass('splitnav').addClass('splitnav_d')}
				else{next.removeClass('splitnav_d').addClass('splitnav')}
			});
		};
		prev.click(function(){
			var splitpage = $('.'+id+'_splitpage'), splitnum = $('.'+id+'_splitnum'), i, k = 0;
			for(i=0; i<splitpage.length; i++){
				if(splitpage.eq(i).css('display')!='none'){
					if(i==0)return;
					k = i-1;
					splitpage.eq(i).hide();
					break;
				}
			}
			splitnum.removeClass('splitnum_x').addClass('splitnum');
			splitpage.eq(k).show();
			splitnum.eq(k).addClass('splitnum_x');
			if(k==0)$(this).removeClass('splitnav').addClass('splitnav_d');
			if(k==splitpage.length-1){next.removeClass('splitnav').addClass('splitnav_d')}
			else{next.removeClass('splitnav_d').addClass('splitnav')}
		});
		next.click(function(){
			var splitpage = $('.'+id+'_splitpage'), splitnum = $('.'+id+'_splitnum'), i, k = splitpage.length;
			for(i=0; i<splitpage.length; i++){
				if(splitpage.eq(i).css('display')!='none'){
					if(i==splitpage.length-1)return;
					k = i+1;
					splitpage.eq(i).hide();
					break;
				}
			}
			splitnum.removeClass('splitnum_x').addClass('splitnum');
			splitpage.eq(k).show();
			splitnum.eq(k).addClass('splitnum_x');
			if(k==0){prev.removeClass('splitnav').addClass('splitnav_d')}
			else{prev.removeClass('splitnav_d').addClass('splitnav')}
			if(k==splitpage.length-1)$(this).removeClass('splitnav').addClass('splitnav_d');
		});
	});
};


//又拍云上传图片
$.fn.upyun = function(options){
	options = $.extend({
		folder : 'uploadfiles', //图片在又拍云上的保存路径
		name : '', //指定图片名
		bucket : 'yidian2015', //又拍云bucket
		secret : 'cUY7LDcWu03f5/xaK0lJxHSb3oQ=', //又拍云secret
		imgurl : 'http://yidian2015.b0.upaiyun.com', //上传后的图片网址前缀
		complete : null //上传完后执行, file选择的每个文件上传完都会调用,三个参数 url(该文件上传后的完整图片网址), name(当前该文件上传的名称,不含后缀名), json(又拍云返回的数据)
	}, options);
	return this.each(function(){
		if(!$(this).is(':file'))return true;
		var _this = $(this);
		for(var i=0; i<this.files.length; i++){
			var file = this.files[i], name = options.name.length ? options.name+''+i : $.datetimeAndRandom();
			if(!file.type.match(/image.*/))continue;
			var suffix = (file.type.split('/'))[1];
			if(suffix=='jpeg')suffix = 'jpg';
			var opt = {'bucket' : options.bucket,
						'expiration' : Date.parse(new Date()) + 600,
						'save-key' : '/'+options.folder+'/{year}/{mon}/{day}/'+name+'.'+suffix,
						'allow-file-type' : 'jpg,jpeg,gif,png,bmp',
						'content-length-range' : '0,10240000',
						'image-width-range' : '0,1024000',
						'image-height-range' : '0,1024000'
						};
			var json = $.jsonString(opt);
			var policy = $.base64Encode(json);
			var sign = $.md5(policy+'&'+options.secret);
			var dat = {'policy':policy, 'signature':sign};
			fileHandler(file, 'file', dat, function(responseText){
				var json = $.formatJSON(responseText);
				if($.isPlainObject(json)){
					if(json.code==200){
						if($.isFunction(options.complete)){
							options.complete.call(_this, options.imgurl+json.url, name, json);
						}
					}else{
						$.overloadError(json.message);
					}
				}else{
					console.log(responseText);
				}
			});
		}
		function fileHandler(file, name, dat, load){
			var crlf = '\r\n', boundary = 'html5upload', dashes = '--';
			var fr = new FileReader();
			fr.readAsDataURL(file);
			var xml = new XMLHttpRequest();
			xml.addEventListener('load', function(e){
				if($.isFunction(load))load(e.target.responseText);
			}, false);
			xml.open('POST', 'http://v0.api.upyun.com/'+options.bucket, true);
			if(file.getAsBinary){
				var data = dashes + boundary + crlf + 'Content-Disposition: form-data;' + 'name="' + name + '";' + 'filename="' + unescape(encodeURIComponent(file.name)) + '"' + crlf + 'Content-Type: application/octet-stream' + crlf+crlf + file.getAsBinary() + crlf;
				for(var d in dat)data += dashes + boundary + crlf + 'Content-Disposition: form-data;' + 'name="' + d + '"' + crlf+crlf + dat[d] + crlf;
				data += dashes + boundary + dashes;
				xml.setRequestHeader('Content-Type', 'multipart/form-data;boundary=' + boundary);
				xml.sendAsBinary(data);
			}else if(window.FormData){
				var fd = new FormData();
				fd.append(name, file);
				for(var d in dat)fd.append(d, dat[d]);
				xml.send(fd);
			}else{
				$.overloadError('浏览器不支持html5');
			}
		}
	});
};

//Canvas
$.fn.canvas = function(options){
	var options = $.extend({
		x : 0, //x坐标
		y : 0, //y坐标
		width : 0, //矩形宽度
		height : 0 //矩形高度
	}, options);
	return this.each(function(){
		var context = this.getContext('2d');
		//绘制图像前设置绘图样式
		//context.fillStyle = '#ff0000'; //填充样式或rgba(255,0,0,0.6)
        //context.strokeStyle = '#ff0000'; //边框样式
		//context.lineWidth = 1; //边框宽度
		//绘制图像方法
		//矩形
		//context.fillRect(x, y, width, height); //填充矩形
		//context.strokeRect(x, y, width, height); //边框矩形
		//圆弧, anticlockwise:逆时针(true),顺时针(false), 0:3点、90:6点(Math.PI/2)、180:9点(Math.PI)、270:12点(Math.PI*3/2)
		//context.beginPath();
		//context.arc(x, y, radius, starAngle, endAngle, anticlockwise);
		//context.closePath(); //关闭路径
		//线段
		//context.moveTo(x, y);
		//context.lineTo(x, y);
		//贝塞尔曲线
		//context.bezierCurveTo(第一个控制点x坐标, 第一个控制点y坐标, 第二个控制点x坐标, 第二个控制点y坐标, 终点x坐标, 终点y坐标);
		//线性渐变, lg.addColorStop(offset[0~1], color)
		//var lg = context.createLinearGradient(xStart, yStart, xEnd, yEnd);
		//lg.addColorStop(0, 'rgb(255,0,0)'); //红  
		//lg.addColorStop(0.5, 'rgb(0,255,0)'); //绿
		//lg.addColorStop(1, 'rgb(0,0,255)'); //蓝
		//context.fillStyle = lg;
		//径向渐变(发散),颜色使用与线性渐变一样, 开始圆圆心x,开始圆圆心y,开始圆半径,结束圆圆心x,结束圆圆心y,结束圆半径, addColorStop的offset为开始圆心到结束圆心
		//相同圆心画球形渐变,不同圆心射线渐变
		//var rg = context.createRadialGradient(xStart, yStart, radiusStart, xEnd, yEnd, radiusEnd);
		//绘制阴影
		//context.shadowOffsetX :阴影的横向位移量（默认值为0）
		//context.shadowOffsetY :阴影的纵向位移量（默认值为0）
		//context.shadowColor :阴影的颜色
		//context.shadowBlur :阴影的模糊范围（值越大越模糊）
		//开始绘制
		//context.fill(); //填充
        //context.stroke(); //边框
		//清除矩形区域
		//context.clearRect(x, y, width, height);
		//绘制图像
		//var image = new Image();
		//image.src = 'xxx.png';
		//context.drawImage(image, 图像上的x坐标, 图像上的y坐标, 矩形宽度, 矩形高度, 画在canvas的x坐标, 画在canvas的y坐标, 画出来的宽度, 画出来的高度); //除image其他可选
		//context.createPattern(image, type); //平铺, type:['no-repeat'|'repeat-x'|'repeat-y'|'repeat']
		//context.clip(); //裁剪, 在closePath后使用, 最后路径内的图像才会出现
		//绘制文字
		//context.fillText(text, x, y); //填充文字
		//context.strokeText(text, x, y); //绘制文字轮廓
		//context.font //设置字体样式
		//context.textAlign = align; //水平对齐方式, align:['start'|'end'|'right'|'center']
		//context.textBaseline = baseline; //垂直对齐方式, baseline:['top'|'hanging'|'middle'|'alphabetic'|'ideographic'|'bottom']
		//var length = context.measureText(text); //计算字体长度(px), 长 length.width(px)
		//状态转换, clip前使用了save,之后restore才能跳出clip限制
		//context.save(); //保存当前context状态、属性(游戏存档)
		//context.restore(); //恢复到save时候context的状态、属性(游戏回档)
		//导出图像
		//window.open(canvas.toDataURL('image/jpeg'));
	});
};

//Canvas绘制弧线
$.fn.angle = function(options){
	options = $.extend({
		x : 0, //圆心x轴坐标值
		y : 0, //y轴坐标值
		radius : 0, //半径
		startRad : 0, //起始弧度
		endRad : 0, //结束弧度,如Math.PI/2(四分之一圆)
		anticlockwise : false, //方向,true:逆时针,false:顺时针
		color : '#000000', //颜色
		fill : false //是否实心
	}, options);
	return this.each(function(){
		if(!$(this).is('canvas'))return true;
		if(this.getContext){
			var ctx = this.getContext('2d'); //获取对应的CanvasRenderingContext2D对象(画笔)
			ctx.beginPath(); //开始一个新的绘制路径
			//arc(x, y, radius, startRad, endRad, anticlockwise)
			ctx.arc(options.x, options.y, options.radius, options.startRad, options.endRad, options.anticlockwise);
			//ctx.moveTo(x, y); //开始点
			//ctx.lineTo(x, y); //直线
			//ctx.arcTo(x1,y1, x2,y2, r); //创建弧,如 150,20,150,70,50
			//按照指定的路径绘制弧线
			if(options.fill){
				ctx.fillStyle = options.color; //填充颜色
				ctx.fill();
			}else{
				ctx.strokeStyle = options.color; //弧线颜色
				ctx.stroke();
			}
		}
	});
};

//四位加一个空格
$.fn.creditCard = function(){
	function changeValue(_this){
		var value = _this.val();
		/\S{5}/.test(value) && _this.val(value.replace(/\s/g, '').replace(/(.{4})/g, '$1 '));
	};
	changeValue(this);
	return this.on('keyup mouseout input', function(){
		changeValue($(this));
	});
};

//获取:file选择的文件对象
$.fn.getFile = function(callback){
	this.each(function(){
		var _this = $(this);
		if(!_this.is(':file') || !$.isFunction(callback))return true;
		var obj = getFile(this);
		if($.inArray(obj.type, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])>-1){
			var img = new Image();
			img.onload = function(){
				obj = $.extend(obj, {width:this.width, height:this.height});
				callback.call(_this, obj);
			};
			img.src = obj.url;
		}else{
			callback.call(_this, obj);
		}
	});
	return getFile(this[0]);
	function getFile(_this){
		if(!$(_this).is(':file'))return {};
		var file = _this.files[0];
		if(!file)return {};
		var url = '';
		if(window.createObjectURL){
			url = window.createObjectURL(file);
		}else if(window.URL){
			url = window.URL.createObjectURL(file);
		}else if(window.webkitURL){
			url = window.webkitURL.createObjectURL(file);
		}
		return {name:file.name, size:file.size, url:url, type:(file.type.split('/'))[1], mimeType:file.type, lastModifiedDate:file.lastModifiedDate};
	}
};

//获取选中的radio或checkbox/选中指定值的radio或checkbox(val:[字符|数字(索引选中)|数组|有返回值的函数])(isTrigger:自动执行change操作,默认true)
$.fn.checked = function(val, isTrigger){
	if(typeof val=='undefined'){
		if(!this.length)return $([]);
		var name = this.attr('name');
		if(!!!name)name = this.attr('id');
		if(!!!name)return $([]);
		var box = this.parents('body').find('[name="'+name.replace(/\[\]/,'\\[\\]')+'"]:checked');
		if(!box.length)box = _this.parents('body').find('[id="'+name.replace(/\[\]/,'\\[\\]')+'"]:checked');
		return box;
	}else{
		if(typeof isTrigger=='undefined')isTrigger = true;
		if(val===null || (typeof val=='string' && !val.length))return this;
		return this.each(function(){
			var _this = $(this), vals = [];
			var name = _this.attr('name');
			if(!!!name)name = _this.attr('id');
			if(!!!name)return true;
			if($.isFunction(val)){
				var s = val.call(_this);
				$.isArray(s) ? vals = s : vals.push(s);
			}else{
				$.isArray(val) ? vals = val : vals.push(val);
			};
			var box = _this.parents('body').find('[name="'+name.replace(/\[\]/,'\\[\\]')+'"]');
			if(!box.length)box = _this.parents('body').find('[id="'+name.replace(/\[\]/,'\\[\\]')+'"]');
			box.removeAttr('checked').removeProp('checked');
			$.each(vals, function(i, v){
				if(typeof v == 'number'){
					box.filter(':eq('+v+')').attr('checked', 'checked').prop('checked', 'checked');
				}else if(typeof v == 'string'){
					box.filter('[value="'+v.replace(/"/g,'\"')+'"]').attr('checked', 'checked').prop('checked', 'checked');
				}
			});
			if(isTrigger)box.trigger('change');
		});
	}
};

//获取选中的option/选中指定值的option(val:[字符|数字(索引选中)|数组|有返回值的函数])(isTrigger:自动执行change操作,默认true)
$.fn.selected = function(val, isTrigger){
	if(typeof val=='undefined'){
		if(!this.find('option').length)return $([]);
		var option = this.find('option:selected');
		if(!option.length)option = this.find('option[selected]');
		if(!option.length)option = this.find('option:eq(0)');
		return option;
	}else{
		if(typeof isTrigger=='undefined')isTrigger = true;
		if(val===null || (typeof val=='string' && !val.length))return this;
		return this.each(function(){
			var _this = $(this), multiple = _this.is('[multiple]'), vals = [];
			if($.isFunction(val)){
				var s = val.call(_this);
				$.isArray(s) ? vals = s : vals.push(s);
			}else{
				$.isArray(val) ? vals = val : vals.push(val);
			};
			$.each(vals, function(i, v){
				if(!multiple)_this.find('option').removeAttr('selected').removeProp('selected');
				if(typeof v == 'number'){
					_this.find('option:eq('+v+')').attr('selected', 'selected').prop('selected', 'selected');
				}else if(typeof v == 'string'){
					_this.find('option[value="'+v.replace(/"/g,'\"')+'"]').attr('selected', 'selected').prop('selected', 'selected');
				}
			});
			if(isTrigger)_this.trigger('change');
		});
	}
};

//获取填充
$.fn.padding = function(){
	if(!this.length)return {top:0, left:0, right:0, bottom:0};
	var top = (Number(this.css('padding-top').replace(/px/,''))||0)*1, left = (Number(this.css('padding-left').replace(/px/,''))||0)*1,
	right = (Number(this.css('padding-right').replace(/px/,''))||0)*1, bottom = (Number(this.css('padding-bottom').replace(/px/,''))||0)*1;
	return {top:top, left:left, right:right, bottom:bottom};
};

//获取间距
$.fn.margin = function(){
	if(!this.length)return {top:0, left:0, right:0, bottom:0};
	var top = (Number(this.css('margin-top').replace(/px/,''))||0)*1, left = (Number(this.css('margin-left').replace(/px/,''))||0)*1,
	right = (Number(this.css('margin-right').replace(/px/,''))||0)*1, bottom = (Number(this.css('margin-bottom').replace(/px/,''))||0)*1;
	return {top:top, left:left, right:right, bottom:bottom};
};

//获取边宽
$.fn.border = function(){
	if(!this.length)return {top:0, left:0, right:0, bottom:0};
	var top = (Number(this.css('border-top-width').replace(/px/,''))||0)*1, left = (Number(this.css('border-left-width').replace(/px/,''))||0)*1,
	right = (Number(this.css('border-right-width').replace(/px/,''))||0)*1, bottom = (Number(this.css('border-bottom-width').replace(/px/,''))||0)*1;
	return {top:top, left:left, right:right, bottom:bottom};
};

//获取/设置透明度
$.fn.opacity = function(opacity){
	if(!this.length)return $([]);
	if(typeof opacity=='undefined'){
		return this.css('opacity');
	}else{
		return this.each(function(){
			$(this).css('opacity', opacity);
		});
	}
};

//动画渐显
$.fn.opacityIn = function(speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).css('opacity', 0).animate({ opacity:1 }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
		});
	});
};

//动画渐隐
$.fn.opacityOut = function(speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).animate({ opacity:0 }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
		});
	});
};

//动画渐隐/渐显到指定透明度
$.fn.opacityTo = function(opacity, speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).animate({ opacity:opacity }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
		});
	});
};

//渐隐且执行后渐显
$.fn.opacityFn = function(speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).animate({ opacity:0 }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
			_this.stop(true, false).animate({ opacity:1 }, (isFn || !$.isNumeric(speed)) ? 400 : speed);
		});
	});
};

//渐隐后删除自身
$.fn.removeOut = function(speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.opacityOut(speed, function(){
			if($.isFunction(callback))callback.call(_this);
			this.remove();
		});
	});
};

//动画水平移动
$.fn.left = function(num, speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).animate({ left:num }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
		});
	});
};

//动画垂直移动
$.fn.top = function(num, speed, callback){
	var isFn = $.isFunction(speed);
	return this.each(function(){
		var _this = $(this);
		_this.stop(true, false).animate({ top:num }, (isFn || !$.isNumeric(speed)) ? 400 : speed, function(){
			if(isFn)speed.call(_this);
			else if($.isFunction(callback))callback.call(_this);
		});
	});
};

//动画设定宽度
$.fn.widthAnimate = function(width, callback){
	return this.each(function(){
		$(this).animate({width:width}, 300, callback);
	});
};

//动画设定高度
$.fn.heightAnimate = function(height, callback){
	return this.each(function(){
		$(this).animate({height:height}, 350, 'easeout', callback);
	});
};

//按原宽高的比例自动设定宽度
$.fn.autoWidth = function(originWidth, originHeight, screenWidth){
	return this.each(function(){
		var _this = $(this), width = _this.outerWidth(false);
		//if(!_this.is(':visible'))return true;
		if(typeof screenWidth == 'undefined')screenWidth = $.window().width;
		var percent = width / screenWidth;
		if(!!_this.attr('percent')){
			percent = _this.attr('percent') * 1;
		}else{
			_this.attr('percent', percent);
		};
		_this.width( $.window().width * percent );
		_this.height( _this.width() * originHeight / originWidth );
	});
};

//按原宽高的比例自动设定高度, percent是按屏幕宽度作为参考对象
$.fn.autoHeight = function(originWidth, originHeight, percent){
	return this.each(function(){
		var _this = $(this);
		//if(!_this.is(':visible'))return true;
		var width = _this.outerWidth(false);
		if(typeof percent != 'undefined'){
			width = $.window().width * percent;
			_this.width(width);
		};
		_this.height( width * originHeight / originWidth );
	});
};

//按原宽高的比例自动设定宽高
$.fn.autoSize = function(originWidth, originHeight, screenWidth){
	return this.each(function(){
		var _this = $(this), width = _this.outerWidth(false);
		//if(!_this.is(':visible'))return true;
		if(typeof screenWidth == 'undefined')screenWidth = $.window().width;
		var percent = width / screenWidth;
		if(!!_this.attr('percent')){
			percent = _this.attr('percent') * 1;
		}else{
			_this.attr('percent', percent);
		};
		_this.width( $.window().width * percent );
		_this.height( _this.width() * originHeight / originWidth );
	});
};

//absolute时自动水平居中
$.fn.centerX = function(){
	return this.each(function(){
		var _this = $(this), parent = _this.parent().css('position', 'relative');
		if(!_this.is(':visible'))return true;
		_this.css( 'left', (parent.width() - _this.width()) / 2);
	});
};

//absolute时自动垂直居中
$.fn.centerY = function(){
	return this.each(function(){
		var _this = $(this), parent = _this.parent().css('position', 'relative');
		if(!_this.is(':visible'))return true;
		_this.css( 'top', (parent.height() - _this.height()) / 2);
	});
};

//获取按键的code
$.fn.onkey = function(options){
	if(typeof options=='boolean'){
		return this.off('keydown', function(e){
			keydown(e, $(this));
		});
	}else if($.isFunction(options)){
		options = {
			ctrl : false,
			alt : false,
			shift : false,
			callback : options
		};
	}else{
		options = $.extend({
			ctrl : false,
			alt : false,
			shift : false,
			callback : null //接受两个参数:按键的code,e
		}, options);
	}
	function keydown(e, _this){
		var e = e||window.event, code = e.which||e.keyCode, comboKeys = {}, comboDown = true;
		if(!$.isFunction(options.callback)){alert(code);return false}
		if(options.ctrl)comboKeys.ctrl = e.ctrlKey;
		if(options.alt)comboKeys.alt = e.altKey;
		if(options.shift)comboKeys.shift = e.shiftKey;
		$.each(comboKeys, function(key, val){
			if(!val){comboDown = false;return false}
		});
		if(comboDown)options.callback.call(_this, code, e);
	}
	return this.each(function(){
		var _this = $(this);
		if(_this.data('onkey'))return true;
		_this.data('onkey', true).on('keydown', function(e){
			keydown(e, $(this));
		});
	});
};

//兼容安卓设置position:sticky
$.fn.sticky = function(options){
	options = $.extend({
		scroller : $(window), //监控滚动的控件
		parent : true //滚动到父控件底部后本控件往上顶
	}, options);
	return this.each(function(k){
		var _this = $(this);
		if(_this.css('position')=='sticky' || _this.css('position')=='-webkit-sticky')return true;
		var scroller = $(options.scroller), offsetY = 0, top = $.washUnit(_this.css('top')), left = $.washUnit(_this.css('left')),
			width = _this.width(), height = _this.height(), parent, placeTop = 0, parentBottom = 0, scroll = false;
		_this.css({position:'relative', top:0, left:0, width:width, height:height});
		setTimeout(function(){
			parent = _this.parent();
			offsetY = _this.offset().top;
			if(!scroller.is($(window))){
				offsetY = _this.position().top;
				scroll = true;
			};
			placeTop = parent.offset().top - parent.parent().offset().top - _this.outerHeight(true);
			if(options.parent)parentBottom = placeTop + parent.outerHeight(true);
		}, 0);
		scroller.scroll(function(){
			var scrollTop = scroller.scrollTop();
			if(scrollTop>offsetY-top){
				if(options.parent)parentBottom = place + parent.outerHeight(true);
				if(parentBottom>0 && scrollTop>parentBottom){
					if(_this.parent().is('.sticky')){
						parent.css({position:'relative'});
						_this.css({position:'absolute', top:'auto', bottom:0});
					}
				}else{
					if(parentBottom>0)parent.css({position:''});
					if(!_this.parent().is('.sticky')){
						_this.wrap('<div class="sticky"></div>');
						var div = _this.css({position:'fixed', top:top+(scroll?scrollTop:0), left:left}).parent().css({
							width:_this.outerWidth(true), height:_this.outerHeight(true), 'float':_this.css('float')
						});
					}else{
						_this.css({position:'fixed', top:top+(scroll?scrollTop:0), left:left, bottom:'auto'});
					}
				}
			}else{
				if(_this.parent().is('.sticky'))_this.css({position:'relative', top:0, left:0, bottom:'auto'}).unwrap();
			}
		});
	});
};

//获取transform
$.fn.transform = function(property){
	if(typeof property == 'undefined'){
		var x = 0, y = 0, matcher;
		if(this.css('transform') != 'none'){
			matcher = this.css('transform').match(/matrix\(\-?\d+[\.\d+]*, \-?\d+[\.\d+]*, \-?\d+[\.\d+]*, \-?\d+[\.\d+]*, (\-?\d+(\.\d+)?), (\-?\d+(\.\d+)?)\)/);
			if($.isArray(matcher)){
				x = Number(matcher[1]);
				y = Number(matcher[3]);
			}
		};
		return {x:x, y:y};
	}else{
		return this.each(function(){
			var _this = $(this);
			_this.css({'-webkit-transform':property, 'transform':property});
			/*
			if($.browser.chrome || $.browser.safari){
				//if($.browser.android && property.indexOf('scale')>-1){
				_this.css({'-webkit-transform':property});
			}else{
				_this.css({'transform':property});
			}
			*/
		});
	}
};

//把body宽度用screenWidth作为基础对HTML元素进行CSS3缩放, screenWidth为空即默认为320
$.fn.scale = function(screenWidth){
	if(typeof screenWidth == 'undefined')screenWidth = 320;
	return this.each(function(){
		$(this).transform('scale('+($(document.body).width()/screenWidth)+')');
	});
}

//获取outerHTML
$.fn.outerHTML = function(){
	return this.prop('outerHTML');
};

//不能选中
$.fn.unselect = function(){
	return this.attr('unselectable','on').css({
		'-moz-user-select':'-moz-none', '-moz-user-select':'none', '-o-user-select':'none', '-khtml-user-select':'none',
		'-webkit-user-select':'none', '-ms-user-select':'none', 'user-select':'none'
	}).on('selectstart', function(){return false});
};

//模拟点击
$.fn.simclick = function(){
	return this.trigger('click');
	/*
	return this.each(function(){
		if($.browser.msie){this.click()}
		else{
			var e = document.createEvent('MouseEvent');
			e.initEvent('click', true, true);
			this.dispatchEvent(e);
		}
	});
	*/
};

//兼容移动端点击事件
var onclick_moved = false;
$.fn.onclick = function(fn, stopBounces){
	return this.each(function(){
		$(this).click(fn);
	});
	//--20160127开始不使用下面,发现移动端的非A标签也能使用click
	/*//
	if(typeof stopBounces == 'undefined')stopBounces = true;
	return this.each(function(){
		var _this = $(this), startX, startY;
		var start = function(e){
			onclick_moved = false;
			startX = $.touches(e).x;
			startY = $.touches(e).y;
			_this.on('mousemove', move);
			if(window.addEventListener)_this[0].addEventListener('touchmove', move, true);
		},
		move = function(e){
			if(stopBounces)e.preventDefault();
			var curX = $.touches(e).x, curY = $.touches(e).y;
			if(curX-startX!=0 || curY-startY!=0)onclick_moved = true;
		},
		stop = function(e){
			_this.off('mousemove', move);
			if(window.addEventListener)_this[0].removeEventListener('touchmove', move, true);
			if(!onclick_moved)setTimeout(function(){fn.call(_this[0], e)}, 10);
		};
		_this.on('mousedown', start).on('mouseup', stop).click(function(){return false}).on('dragstart', function(e){e.preventDefault()});
		if(window.addEventListener){
			this.addEventListener('touchstart', start, true);
			this.addEventListener('touchend', stop, true);
		}
	});
	//*/
};

//手机端禁止内容区域滚到顶/底后引起页面整体的滚动, remove取消禁止
$.fn.stopBounces = function(remove){
	return this.each(function(){
		var _this = $(this), startX, startY,
		start = function(e){
			startX = $.touches(e).x;
			startY = $.touches(e).y;
		},
		move = function(e){
			//高位表示向上滚动, 底位表示向下滚动, 1容许 0禁止
			var status = '11', ele = this, currentX = $.touches(e).x, currentY = $.touches(e).y;
			if(currentX-startX>=8 || currentX-startX<=-8){e.preventDefault();return false}
			if(ele.scrollTop === 0){ //如果内容小于容器则同时禁止上下滚动
				status = ele.offsetHeight >= ele.scrollHeight ? '00' : '01';
			}else if(ele.scrollTop + ele.offsetHeight >= ele.scrollHeight){ //已经滚到底部了只能向上滚动
				status = '10';
			}
			if(status != '11'){
				var direction = currentY - startY > 0 ? '10' : '01'; //判断当前的滚动方向
				//操作方向和当前允许状态求与运算, 运算结果为0, 就说明不允许该方向滚动, 则禁止默认事件, 阻止滚动
				if(!(Number(status, 2) & Number(direction, 2)))e.preventDefault();
			}
		};
		if(remove){
			_this.removeData('stopBounces');
			this.removeEventListener('touchstart', start, true);
			this.removeEventListener('touchmove', move, true);
			return;
		}
		if(!$.browser.mobile || !!_this.data('stopBounces') || !!_this.data('drag') || !!_this.data('dragshow') || !!_this.data('touchmove') || !!_this.data('pullRefresh'))return;
		_this.data('stopBounces', true);
		this.addEventListener('touchstart', start, true);
		this.addEventListener('touchmove', move, true);
	});
};

//禁止滚动
$.fn.stopScroll = function(disable){
	return this.each(function(){
		var _this = $(this).stopBounces(!disable), html = _this.parents('html').eq(0), body = _this.parents('body').eq(0), p = html.add(body);
		if(disable){
			p.css('overflow', 'hidden');
			body.css({width:html.width()-$.scrollBar(body).width, margin:0});
		}else{
			p.css('overflow', '');
			body.css({width:'', margin:''});
		}
	});
};

//增加resize
var elems = $([]), jq_resize = $.resize = $.extend($.resize, {}), timeout_id,
	str_setTimeout = 'setTimeout', str_resize = 'resize', str_data = str_resize+'-special-event', str_delay = 'delay', str_throttle = 'throttleWindow';
jq_resize[str_delay] = 250;
jq_resize[str_throttle] = true;
$.event.special[str_resize] = {
	setup : function(){
		if(!jq_resize[str_throttle] && this[str_setTimeout])return false;
		var elem = $(this);
		elems = elems.add(elem);
		$.data(this, str_data, {w:elem.width(), h:elem.height()});
		if(elems.length === 1)loopresize();
	},
	teardown : function(){
		if(!jq_resize[str_throttle] && this[str_setTimeout])return false;
		var elem = $(this);
		elems = elems.not(elem);
		elem.removeData(str_data);
		if(!elems.length)clearTimeout(timeout_id);
	},
	add : function(handleObj){
		if(!jq_resize[str_throttle] && this[str_setTimeout])return false;
		var old_handler;
		function new_handler(e, w, h){
			var elem = $(this), data = $.data(this, str_data);
			data.w = typeof w!=='undefined' ? w : elem.width();
			data.h = typeof h!=='undefined' ? h : elem.height();
			old_handler.apply(this, arguments);
		}
		if($.isFunction(handleObj)){
			old_handler = handleObj;
			return new_handler;
		}else{
			old_handler = handleObj.handler;
			handleObj.handler = new_handler;
		}
	}
};
function loopresize(){
	timeout_id = window[str_setTimeout](function(){
		elems.each(function(k){
			try{
				var elem = $(this), width = elem.width(), height = elem.height(), data = $.data(this, str_data);
				if(width!==data.w || height!==data.h)elem.trigger(str_resize, [data.w=width, data.h=height]);
			}catch(e){
				elems.splice(k, 1);
			}
		});
		loopresize();
	}, jq_resize[str_delay]);
}

//3D Touch
$.fn.touch3d = function(callback){
	return this.each(function(){
		var _this = $(this), touch = null;
		if(window.addEventListener){
			this.addEventListener('touchstart', touchstart, false);
			this.addEventListener('touchmove', touchmove, false);
			this.addEventListener('touchend', touchend, false);
		}
		function touchstart(e){
			e.preventDefault();
			checkForce(e);
		}
		function touchmove(e){
			e.preventDefault();
			checkForce(e);
		}
		function touchend(e){
			e.preventDefault();
			touch = null;
		}
		function checkForce(e){
			touch = e.touches[0];
			var data = [];
			for(var i=0; i<e.touches.length; i++){
				//screenX:e.touches[i].screenX, screenY:e.touches[i].screenY
				data.push(e.touches[i].force);
			};
			setTimeout(refreshForceValue.call(data), 10);
		}
		function refreshForceValue(){
			renderElement(this);
		}
		function renderElement(forceValue){
			if($.isFunction(callback))callback.call(_this, forceValue);
		}
	});
};

//解除插件
$.fn.removePlug = function(dataName){
	if(!!this.data(dataName)){
		//this.off();
		if(!!this.data('replacehtml'))this.removeData('replacehtml');
		else{
			this.find('*').remove();
			this.empty().html('').html(this.data(dataName+'-html'));
		}
		return true;
	}else{
		this.data(dataName, true).data(dataName+'-html', this.html());
		return false;
	}
};

//提示框
$.fn.alert = function(options){
	if($.isPlainObject(options))options = $.extend(options, {html:this.html()});
	alertUI(options);
	return this;
};

//仿iOS的UIActionSheet
$.fn.actionView = function(title, btns, e){
	var _this = this, tablet = $.window().width>=1024, overlay = $('.load-overlay', _this), dialog = $('.dialog-action', _this), group;
	if(!title){
		var height = dialog.height();
		dialog.removeClass('dialog-action-x');
		if(!dialog.hasClass('dialog-action-popover'))dialog.css({transform:'translate3d(0,'+height+'px,0)', '-webkit-transform':'translate3d(0,'+height+'px,0)'});
		setTimeout(function(){dialog.remove()}, 400);
		setTimeout(function(){
			if($('.load-face, .load-view, .dialog-action, .dialog-alert, .dialog-popover', _this).length)return;
			overlay.removeClass('load-overlay-in');
			setTimeout(function(){overlay.remove()}, 400);
		}, 400);
		return;
	}
	if(!$.isArray(btns) || !btns.length)return dialog;
	if(!overlay.length){
		overlay = $('<div class="load-overlay"></div>');
		if(!!!_this.data('overlay.no'))_this.append(overlay.css({background:'rgba(0,0,0,0.6)'}));
		if(tablet && e){
			overlay.onclick(function(){_this.popoverView(false)});
		}else{
			overlay.onclick(function(){_this.actionView(false)});
		}
	};
	setTimeout(function(){overlay.addClass('load-overlay-in')}, 0);
	dialog = $('<div class="dialog-action"></div>').css('z-index', 9999);
	group = $('<div class="dialog-action-group"><div class="dialog-action-box"></div></div>');
	dialog.append(group);
	var inner = group.find('.dialog-action-box').stopBounces();
	inner.append('<div class="dialog-action-label">'+title+'</div>');
	for(var i=0; i<btns.length; i++){
		var text = btns[i].text||'btn'+(i+1), btn = $('<a href="javascript:void(0)" class="dialog-action-button">'+text+'</a>');
		inner.append(btn);
		if($.isFunction(btns[i].click)){
			btn.data('click', btns[i].click);
			(function(j){
			btn.click(function(){
				$(this).data('click').call(dialog, j);
				_this.actionView(false);
			});
			})(i);
		}else{
			if(tablet && e){
				btn.click(function(){_this.popoverView(false)});
			}else{
				btn.click(function(){_this.actionView(false)});
			}
		}
	};
	group = $('<div class="dialog-action-group"><div class="dialog-action-box"><a href="javascript:void(0)" class="dialog-action-button dialog-action-bold">取消</a></div></div>');
	dialog.append(group);
	if(tablet && e){
		group.find('a').click(function(){_this.popoverView(false)});
		_this.popoverView(e, dialog.addClass('dialog-action-popover'));
	}else{
		group.find('a').click(function(){_this.actionView(false)});
		_this.append(dialog);
		var height = dialog.height();
		dialog.css({transform:'translate3d(0,'+height+'px,0)', '-webkit-transform':'translate3d(0,'+height+'px,0)', 'transition-duration':'0s', '-webkit-transition-duration':'0s'});
	};
	setTimeout(function(){
		dialog.css({transform:'', '-webkit-transform':'', 'transition-duration':'', '-webkit-transition-duration':''}).addClass('dialog-action-x');
	}, 10);
	return dialog;
};

//popView
$.fn.popoverView = function(e, target){
	var _this = $(this), window = $.window(), overlay = $('.load-overlay', _this), dialog = $('.dialog-popover', _this);
	if(!target){
		dialog.removeClass('dialog-popover-x');
		setTimeout(function(){
			var child = dialog.find('.dialog-popover-box > *:eq(0)');
			if(!!child.data('parent'))child.data('parent').append(child);
			if(!!child.data('originnext'))child.data('originnext').before(child);
			dialog.remove();
		}, 400);
		setTimeout(function(){
			if($('.load-face, .load-view, .dialog-action, .dialog-alert, .dialog-popover', _this).length)return;
			overlay.removeClass('load-overlay-in');
			setTimeout(function(){overlay.remove()}, 400);
		}, 400);
		return;
	}
	if(!e)return;
	var e = e||window.event, o = $(e.target||e.srcElement);
	if(!overlay.length){
		overlay = $('<div class="load-overlay"></div>');
		if(!!!_this.data('overlay.no'))_this.append(overlay.css({background:'rgba(0,0,0,0.6)'}));
		overlay.onclick(function(){_this.popoverView(false)});
	};
	setTimeout(function(){overlay.addClass('load-overlay-in')}, 0);
	dialog = $('<div class="dialog-popover"><div class="dialog-popover-inner"><div class="dialog-popover-box"></div></div><div class="dialog-popover-angle"></div></div>');
	_this.append(dialog);
	var inner = dialog.find('.dialog-popover-box').stopBounces();
	if(typeof target!='object'){
		var htm = target+'', object = $(htm);
		if(object.length){
			if(object.next().length)object.data('originnext', object.next());
			else object.data('parent', object.parent());
			inner.append(object);
		}else{
			inner.html(htm);
		}
	}else{
		target = $(target);
		if(target.length){
			if(target.next().length)target.data('originnext', target.next());
			else target.data('parent', target.parent());
			inner.append(target);
		}
	};
	var ge = 4, width = dialog.width(), height = inner.height()>44*6-10 ? inner.height(44*6-10).height() : inner.height(), offset = o.offset(),
		angle = inner.parent().next(), angleWidth = angle.width(), angleHeight = angle.height(), left, top, scrollTop = $.scroll().top;
	left = offset.left + (o.width() - width) / 2;
	if(left < ge)left = ge;
	if(left+width > window.width-ge)left = window.width - width - ge;
	top = offset.top - height - angleHeight/2;
	if(top < ge){
		top = offset.top + o.height() + angleHeight/2;
		if(top+height > scrollTop+window.height-ge){
			var halfTop = offset.top - scrollTop - ge - angleHeight/2,
				halfBottom = scrollTop + window.height - offset.top - o.height() - ge - angleHeight/2;
			if(halfTop > halfBottom){
				inner.height(halfTop);
				top = offset.top - halfTop - angleHeight/2;
				angle.addClass('on-bottom');
			}else{
				inner.height(halfBottom);
				top = offset.top + o.height() + angleHeight/2;
				angle.addClass('on-top');
			}
		}else{
			angle.addClass('on-top');
		}
	}else{
		angle.addClass('on-bottom');
	};
	angle.css('left', offset.left + (o.width()-angleWidth)/2 - left);
	dialog.css({left:left, top:top});
	setTimeout(function(){
		dialog.addClass('dialog-popover-x');
	}, 10);
	return dialog;
};

//遮罩层与展示层, target:expr|对象|html代码(内容)|空字符串只显示背景遮罩层|false(删除),
//type:浮动控件位置类型(0:居中|1:底部|2:全屏居中(不随滚动)|3:居中(不自动opacity)|funciton:自定义)
//调用前 调用者.data('overlay.no', true) 可不添加遮罩层
//target可增加以下自定义属性, overlay-opacity:遮罩层背景色透明度, no-close:点击遮罩层不关闭, show-close:显示右上角关闭按钮, add-class:增加样式到face, delay-class:延迟增加样式到face, delay-close:指定关闭时长(默认300), close-class:关闭前增加样式到face
$.fn.overlay = function(target, type, callback, closeCallback){
	var _this = this;
	if(typeof target=='boolean' && !target){
		var overlay = $('.load-overlay', _this), face = $('.load-face', _this);
		setTimeout(function(){
			if($('.load-face, .load-view, .dialog-action, .dialog-alert, .dialog-popover', _this).length)return;
			overlay.removeClass('load-overlay-in');
			setTimeout(function(){overlay.remove()}, 400);
		}, 400);
		if(face.length){
			var closeCallback = face.data('overlay.callback'), target = face.data('overlay.target'),
				origin = target.removeData('overlay.overlay').data('overlay.origin'),
				cls = face.data('overlay.closeClass'), delay = face.data('overlay.delayClose')||300;
			if(typeof type == 'undefined' || (!$.isNumeric(type) && !$.isFunction(type)))type = face.data('overlay.type');
			if(!!cls)face.addClass(cls);
			if($.isFunction(type)){
				type.call(face);
			}else{
				if(!!!type || type!=1){
					if(type!=3)face.opacityOut(delay);
					setTimeout(function(){
						if(!!origin){origin.after(target.css('display', target.data('overlay.display')));origin.remove()}
						if($.isFunction(closeCallback))closeCallback.call(target);
						face.remove();
					}, delay);
				}else{
					face.animate({top:$.window().height}, delay, function(){
						if(!!origin){origin.after(target.css('display', target.data('overlay.display')));origin.remove()}
						if($.isFunction(closeCallback))closeCallback.call(target);
						face.remove();
					});
				}
			}
		};
		return;
	};
	var t = $([]);
	if(typeof target!='undefined' && typeof target!='boolean' && target.length){
		var t = $(target);
		if(t.parent().length){
			var display = t.css('display'),
				origin = $('<div style="display:'+display+';opacity:0;width:'+t.width()+'px;height:'+t.height()+'px;"></div>');
			t.after(origin);
			t.data({'overlay.origin':origin, 'overlay.display':display});
		}
	};
	var window = $.window(), winHeight = window.height, overlay = $('.load-overlay', _this), face = $('.load-face', _this);
	if(!overlay.length)overlay = $('<div class="load-overlay"></div>');
	if(!face.length)face = $('<div class="load-face"></div>');
	else{
		face.removeClass(face.data('overlay.addClass')).removeClass(face.data('overlay.delayClass'));
		var tar = face.data('overlay.target'), origin = tar.data('overlay.origin');
		if(!!origin){origin.after(tar.css('display', tar.data('overlay.display')));origin.remove()}
		face.html('');
	}
	if(!!!_this.data('overlay.no'))_this.append(overlay.css({background:'rgba(0,0,0,'+(t.attr('overlay-opacity')||0.6)+')'}));
	setTimeout(function(){overlay.addClass('load-overlay-in')}, 0);
	if(!!!t.attr('no-close'))overlay.onclick(function(){_this.overlay(false)});
	if(!t.length)return;
	if(t.parent().length)t.after(face);
	else _this.append(face);
	if(typeof type=='undefined' || (!$.isNumeric(type) && !$.isFunction(type)))type = 0;
	face.data({'overlay.target':t.data('overlay.overlay', true), 'overlay.type':type, 'overlay.callback':closeCallback}).append(t.css('display', 'block'));
	face.css({position:'fixed', 'z-index':9999});
	setTimeout(function(){face.css({width:t.outerWidth(true), height:t.outerHeight(true)})}, 0);
	if(!!t.attr('add-class'))face.data({'overlay.addClass':t.attr('add-class')}).addClass(t.attr('add-class'));
	if(!!t.attr('delay-class'))setTimeout(function(){face.data({'overlay.delayClass':t.attr('delay-class')}).addClass(t.attr('delay-class'))}, 100);
	if(!!t.attr('delay-close'))face.data({'overlay.delayClose':Number(t.attr('delay-close'))});
	if(!!t.attr('close-class'))face.data({'overlay.closeClass':t.attr('close-class')});
	if(!!t.attr('show-close')){
		var close = $('<a href="javascript:void(0)">×</a>').css({position:'absolute', right:0, top:0, 'z-index':9999, width:30, height:30, 'line-height':'24px', overflow:'hidden', background:'rgba(0,0,0,0.6)', color:'#fff', 'font-size':'22px', 'text-align':'center', 'padding-left':'5px', 'box-sizing':'border-box', 'font-family':'arial', 'text-decoration':'none', '-moz-border-radius-bottomleft':'30px', '-webkit-border-bottom-left-radius':'30px', 'border-bottom-left-radius':'30px'}).click(function(){_this.overlay(false)});
		face.append(close);
	}
	if($.isFunction(callback))callback.call(t);
	if($.isFunction(type)){
		type.call(face);
	}else{
		if(type!=1){
			if(type==2){
				winHeight = window.maxHeight;
				face.css({position:'absolute'});
			};
			face.css({top:(winHeight-face.height())/2, left:(window.width-face.width())/2});
			if(type==0)face.css({opacity:0}).opacityIn(300);
		}else{
			face.css({top:winHeight, left:0, width:window.width}).animate({top:winHeight-face.height()}, 300);
		}
	};
	return face;
};

//加载动画遮罩层
//text:[false(关闭)|null(只显示菊花)|string(提示文字)], image:[数字(默认)|null(不显示菊花)|.类名(使用类名)|string(图片路径)], auto:自动关闭时间(毫秒), callback:消失后执行
$.fn.overload = function(text, image, auto, callback){
	var _this = this;
	if(typeof text=='boolean' && !text){
		var view = $('.load-view', _this);
		if(!view.length)return;
		setTimeout(function(){
			if(!!view.data('overload.timer'))return;
			view.removeClass('load-view-in').addClass('load-view-out');
			setTimeout(function(){
				callback = view.data('overload.callback');
				if(!!callback)callback();
				view.remove();
				if($('.load-face, .load-view, .dialog-action, .dialog-alert, .dialog-popover', _this).length)return;
				$('.load-overlay', _this).remove();
			}, 400);
		}, 10);
		return;
	}
	if(typeof image=='undefined' || (typeof image=='string' && !image.length) || typeof image=='number')image = '.load-animate';
	var window = $.window(), overlay = $('.load-overlay', _this), view = $('.load-view', _this);
	if(typeof text=='boolean')text = '';
	if(!view.length){
		if(!overlay.length && !!!_this.data('overlay.no')){
			overlay = $('<div class="load-overlay"></div>');
			_this.append(overlay);
		};
		view = $('<div class="load-view"><div></div><span>'+text+'</span></div>');
		_this.append(view);
	}else{
		var timer = view.removeAttr('style').data('overload.timer');
		if(!!timer){clearTimeout(timer);view.removeData('overload.timer')};
		view.find('div').removeAttr('class').removeAttr('style').show();
		view.find('span').removeAttr('style').show().html(text);
	}
	if(view.width()>180 && view.width()<260)view.css({'max-width':'180px'});
	view.css({'margin-top':-view.height()/2, 'margin-left':-view.width()/2});
	if(!image){
		view.find('div').hide();
		view.find('span').addClass('text').css({'margin-top':(view.height()-view.find('span').outerHeight(false))/2});
	}else{
		if(image.substr(0, 1)=='.'){
			view.find('div').addClass(image.substr(1));
		}else{
			view.find('div').css({width:35, height:35, 'background-image':'url('+image+')'});
		}
	}
	if(!text)view.find('div').css({'margin-top':(view.height()-view.find('div').height())/2}).next().hide();
	setTimeout(function(){
		overlay.addClass('load-overlay-in');
		view.addClass('load-view-in');
	}, 10);
	if(auto){
		var timer = setTimeout(function(){
			var timer = view.data('overload.timer');
			if(!!timer){clearTimeout(timer);view.removeData('overload.timer')}
			$.overload(false);
		}, auto);
		view.data('overload.timer', timer);
	}
	if($.isFunction(callback))view.data('overload.callback', callback);
	return view;
};

//滚轮绑定
$.fn.extend({
	mousewheel : function(fn){
		return this.each(function(){
			function wheel(e){
				var delta = 0;
				if(!e)e = window.event;
				if(e.wheelDelta){
					delta = e.wheelDelta/120;
				}else if(e.detail){
					delta = -e.detail/3;
				}
				if(delta && $.isFunction(fn))fn(e, delta);
				if(e.preventDefault)e.preventDefault();
				e.returnValue = false;
			}
			if(window.addEventListener)this.addEventListener('DOMMouseScroll', wheel, false);
			this.onmousewheel = wheel;
		});
	},
	unmousewheel : function(fn){
		return this.each(function(){
			function wheel(e){
				var delta = 0;
				if(!e)e = window.event;
				if(e.wheelDelta){
					delta = e.wheelDelta/120;
				}else if(e.detail){
					delta = -e.detail/3;
				}
				if(delta && $.isFunction(fn))fn(e, delta);
				if(e.preventDefault)e.preventDefault();
				e.returnValue = false;
			}
			if(window.addEventListener)this.removeEventListener('DOMMouseScroll', wheel, false);
			this.onmousewheel = null;
		});
	}
});

//旋转控件
function getTransformProperty(element){
	var properties = ['transform', 'WebkitTransform', 'MozTransform', 'msTransform', 'OTransform'], p;
	while(p = properties.shift()){
		if(element.style[p] !== undefined)return p;
	}
	return false;
}
$.cssHooks['rotate'] = {
	get : function(elem, computed, extra){
		var property = getTransformProperty(elem);
		if(property){
			return elem.style[property].replace(/.*rotate\((.*)deg\).*/, '$1');
		}else{
			return '';
		}
	},
	set : function(elem, value){
		var property = getTransformProperty(elem);
		if(property){
			value = Number(value);
			$(elem).data('rotatation', value);
			if(value==0){
				elem.style[property] = '';
			}else{
				elem.style[property] = 'rotate(' + value%360 + 'deg)';
			}
		}else{
			return '';
		}
	}
};
$.fx.step['rotate'] = function(fx){
	$.cssHooks['rotate'].set(fx.elem, fx.now);
};

//动画过渡效果
$.extend($.easing, {
	linear : function(x){
		return x;
	},
	swing : function(x){
		return 0.5 - Math.cos( x*Math.PI ) / 2;
	},
	easeout : function(x, t, b, c, d){
		return -c * (t /= d) * (t - 2) + b;
	},
	bounceout : function(x, t, b, c, d){
		if ((t/=d) < (1/2.75)) {
			return c*(7.5625*t*t) + b;
		} else if (t < (2/2.75)) {
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
		} else if (t < (2.5/2.75)) {
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
		}
	},
	backout : function(x, t, b, c, d){
		var s=1.70158;
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	}
});

//重写$.browser
$.uaMatch = function(ua){
	var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
		/(webkit)[ \/]([\w.]+)/.exec(ua) ||
		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
		/(msie) ([\w.]+)/.exec(ua) ||
		ua.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
		[];
	return {
		browser: match[1] || '',
		version: match[2] || '0'
	};
};
var ua = navigator.userAgent.toLowerCase(), matched = $.uaMatch(ua), browser = {ua:ua};
if(matched.browser){
	browser[matched.browser] = true;
	browser.version = matched.version;
}
if(ua.match(/windows mobile/i))browser.wm = true;
else if(ua.match(/windows ce/i))browser.wince = true;
else if(ua.match(/ucweb/i))browser.ucweb = true;
else if(ua.match(/rv:1.2.3.4/i))browser.uc7 = true;
else if(ua.match(/midp/i))browser.midp = true;
else if(browser.msie){
	if(browser.version<7)browser.ie6 = true;
	else if(browser.version<8)browser.ie7 = true;
	else if(browser.version<9)browser.ie8 = true;
	else if(browser.version<10)browser.ie9 = true;
}else if(browser.chrome)browser.webkit = true;
else if(browser.webkit){
	browser.safari = true;
	var matcher = /safari\/([\d\._]+)/.exec(ua);
	if($.isArray(matcher))browser.version = matcher[1].replace(/_/g, '.');
}else if(browser.mozilla)browser.firefox = true;
if(ua.match(/ipad/i) || ua.match(/iphone/i)){
	if(ua.match(/ipad/i))browser.ipad = true;
	if(ua.match(/iphone/i))browser.iphone = true;
	var matcher = / os ([\d\._]+) /.exec(ua);
	if($.isArray(matcher))browser.version = matcher[1].replace(/_/g, '.');
}
if(ua.match(/android/i)){
	browser.android = true;
	var matcher = /android ([\d\.]+)/.exec(ua);
	if($.isArray(matcher))browser.version = matcher[1];
}
if(ua.match(/micromessenger/i))browser.wechat = browser.weixin = browser.wx = true;
if(browser.ipad || browser.iphone)browser.ios = true;
if(browser.ios || browser.android || browser.wm || browser.wince || browser.ucweb || browser.uc7 || browser.midp || browser.wx)browser.mobile = true;

$.extend({
	browser : browser,
	log : function(obj){console.log(obj)},
	referer : function(){
		var referer = '';
		try{
			referrer = window.top.document.referrer;
		}catch(e){
			if(window.parent){
				try{
					referrer = window.parent.document.referrer;
				}catch(e2){
					referrer = '';
				}
			}
		}
		if(!referrer.length)referrer = document.referrer;
		return referrer;
	},
	appendCss : function(path){$('<link>').attr({rel:'stylesheet', type:'text/css', href:path}).appendTo('head')},
	postJSON : function(url, data, callback){
		if($.isFunction(data) && typeof callback=='undefined'){
			callback = data;
			data = {};
		};
		$.post(url, data, function(html){
			$.overload(false);
			if($.isJsonString(html)){
				var json = $.formatJSON(html);
				if($.isFunction(callback))callback(json);
			}else{
				alert(html);
			}
		});
		/*
		$.ajax({type:'post', url:url, data:data, dataType:'json', success:function(json){
				if($.isFunction(callback))callback(json);
			}, error:function(xml, status, e){
				$.overloadError(status+'\n'+e);
			}
		});
		*/
	},
	jsonString : function(obj){
		if(typeof JSON != 'undefined')return JSON.stringify(obj);
		var type = $.type(obj);
		switch(type){
			case 'undefined':case 'unknown':case 'null':return type;
			case 'function':case 'regexp':return toString.call(obj);
			case 'boolean':return obj ? 'true' : 'false';
			case 'number':return isFinite(obj) ? obj+'' : 'null';
			case 'string':
				return '"' + obj.replace(/(\/|")/g, '\\$1').replace(/\n|\r|\t/g, function(a){
					return (a == '\n') ? '\\n' : (a == '\r') ? '\\r' : (a == '\t') ? '\\t': '';
				}) + '"';
			case 'object':
				if(obj === null)return 'null';
				var results = [];
				for(var key in obj){
					if($.isFunction(obj[key]))continue;
					var val = $.jsonString(obj[key]);
					if(val.length && val!=='undefined' && val!=='unknown')results.push('"' + key + '":' + val);
				}
				return '{' + results.join(',') + '}';
			case 'array':
				var results = [];
				for(var i=0; i<obj.length; i++){
					var val = $.jsonString(obj[i]);
					if(val.length && val!=='undefined' && val!=='unknown')results.push(val);
				}
				return '[' + results.join(',') + ']';
		};
		return '';
	},
	jsonValue : function(str){
		return $.formatJSON(str);
	},
	formatJSON : function(str){
		if($.isPlainObject(str))return str;
		if(typeof JSON != 'undefined'){
			return JSON.parse(str);
		}else{
			try{
				var json = null;
				eval('json='+str);
				return json;
			}catch(e){
				return null;
			}
		}
	},
	isJsonString : function(str){
		if(typeof str == 'undefined' || !str)return false;
		try{
			var json = null;
			eval('json='+str);
			return $.isPlainObject(json);
		}catch(e){
			return false;
		}
	},
	isJson : function(obj){
		return $.isPlainObject(obj) && !$.isEmptyObject(obj);
	},
	isDate : function(obj){
		if(typeof obj=='undefined' || !obj)return false;
		if(obj instanceof Date)return true;
		if(typeof obj=='string')return /^\d{4}-\d{1,2}-\d{1,2}( \d{1,2}:\d{1,2}:\d{1,2})?$/.test(obj);
		return false;
	},
	random : function(min, max){
		return Math.floor(min + Math.random() * (max - min));
	},
	datetimeAndRandom : function(){
		return (new Date()).formatDate('yyyymmddhhnnss') + Math.ceil(Math.random()*8999+1000);
	},
	//生成由数字,大写字母,小写字母组合的指定位数的随机字符串, s:指定字符(可使用中文字), randomCode(8,'')
	randomCode : function(n, s){
		var o, codes = '';
		if(!s)s = 'EabXYcde12OP3FBADCUijk45WZlt6GHLMvwIJKfgh90TxNQRSmnopyzqrs78Vu';
		if(isNaN(n))return '';
		o = s.split('');
		for(var i=0; i<n; i++){
			var id = Math.ceil(Math.random()*o.length);
			codes += o[id];
		};
		return codes;
	},
	fillZero : function(num, length){
		num = num+'';
		if(num.length>=length)return num;
		var str = '';
		for(var i=0; i<length; i++)str += '0';
		str += num;
		return str.substr(str.length-length);
	},
	window : function(father){
		var doc = $.document(father);
		return {
			width:doc.clientWidth, height:doc.clientHeight,
			scrollWidth:doc.scrollWidth, scrollHeight:doc.scrollHeight,
			minWidth:Math.min(doc.clientWidth, doc.scrollWidth), minHeight:Math.min(doc.clientHeight, doc.scrollHeight),
			maxWidth:Math.max(doc.clientWidth, doc.scrollWidth), maxHeight:Math.max(doc.clientHeight, doc.scrollHeight),
			screenWidth:window.screen.width, screenHeight:window.screen.height,
			ratio:window.devicePixelRatio?window.devicePixelRatio:1
		}
	},
	document : function(father){
		var doc = null;
		switch(father){
			case 'top':doc = top.document[top.document.compatMode=='CSS1Compat'?'documentElement':'body'];break;
			case 'parent':doc = parent.document[parent.document.compatMode=='CSS1Compat'?'documentElement':'body'];break;
			default:doc = document[document.compatMode=='CSS1Compat'?'documentElement':'body'];break;
		};
		return doc;
	},
	//页面滚动距离
	scroll : function(father){
		var l = 0, t = 0;
		switch(father){
			case 'top':
				l = $(top.document.body).scrollLeft();
				t = $(top.document.body).scrollTop();
				break;
			case 'parent':
				l = $(parent.document.body).scrollLeft();
				t = $(parent.document.body).scrollTop();
				break;
			default:
				l = $(document.body).scrollLeft();
				t = $(document.body).scrollTop();
				break;
		}
		return {left:l, top:t}
	},
	//滚动条宽高
	scrollBar : function(body){
		if(typeof body=='undefined')body = $(document.body);
		body = $(body);
		var scrollX = true, scrollY = true, clientWidth = body[0].clientWidth, clientHeight = body[0].clientHeight, width = 0, height = 0;
		if(body.scrollLeft()==0){
			body.scrollLeft(1);
			if(body.scrollLeft()==0)scrollX = false;
			body.scrollLeft(0);
		}
		if(body.scrollTop()==0){
			body.scrollTop(1);
			if(body.scrollTop()==0)scrollY = false;
			body.scrollTop(0);
		};
		body.css({'overflow':'hidden'});
		if(scrollY){
			body.css({'overflow-x':'', 'overflow-y':'scroll'});
			width = clientWidth - body[0].clientWidth;
		}
		if(scrollX){
			body.css({'overflow-x':'scroll', 'overflow-y':''});
			height = clientHeight - body[0].clientHeight;
		};
		body.css({'overflow':'', 'overflow-x':'', 'overflow-y':''});
		return {width:width, height:height};
	},
	//获取event对象的屏幕距离
	touches : function(e){
		var x = e.clientX || e.changedTouches[0].pageX || e.targetTouches[0].pageX || e.touches[0].pageX;
		var y = e.clientY || e.changedTouches[0].pageY || e.targetTouches[0].pageY || e.touches[0].pageY;
		return {x:x, y:y};
	},
	//判断手指滑动方向,[1:上,2:下,3:左,4:右,0未滑动]
    getDirection : function(startx, starty, endx, endy){
		var angx = endx - startx, angy = endy - starty, result = 0;
		if(Math.abs(angx)<2 && Math.abs(angy)<2)return result; //如果滑动距离太短
		//获得角度
		var angle = Math.atan2(angy, angx) * 180 / Math.PI;
		if(angle>=-135 && angle<=-45){
			result = 1;
		}else if(angle>45 && angle<135){
			result = 2;
		}else if((angle>=135 && angle<=180) || (angle>=-180 && angle<-135)){
			result = 3;
		}else if(angle>=-45 && angle<=45){
			result = 4;
		};
		return result;
    },
	//检测是否存在函数
	hasFunction : function(funcName){
		try{
			if(typeof(eval(funcName))=='function')return true;
		}catch(e){}
		return false;
	},
	//检测是否存在变量
	hasVariable : function(variableName){
		try{
			if(typeof(eval(variableName))!='undefined')return true;
		}catch(e){}
		return false;
	},
	washUnit : function(str){
		if(!str)return 0;
		if((str+'')!='0')str = /^\-?\d+$/.test(str) ? str : str.replace(/px/g, '');
		return Number(str);
	},
	scrollto : function(el, speed, easing){
		$('html, body').scrollto({el:el, speed:speed, easing:easing});
		return false;
	},
	//手机端摇动, 为避免多次调用 callback, 需要在操作页面增加一个全局变量来控制, 例如:
	//var shake = false; $.shake(function(){ if(!shake){shake=true; ... } });
	shake : function(callback){
		if(window.DeviceMotionEvent && $.isFunction(callback)){
			var speed = 20, x = y = z = lastX = lastY = lastZ = 0;
			window.addEventListener('devicemotion', function(){
				var acceleration = event.accelerationIncludingGravity;
				x = acceleration.x; y = acceleration.y;
				if(Math.abs(x-lastX)>speed || Math.abs(y-lastY)>speed)callback();
				lastX = x;
				lastY = y;
			}, false);
		}
	},
	//获取URL参数, 格式:[?|#]param1=value1&param2=value2
	request : function(p, win){
		if(typeof win=='undefined')win = window;
		var params = {}, pairs, query = win.location.href;
		if(!p)p = '?';
		if(p=='?')p = '\\?';
		query = query.replace(new RegExp('^[^'+p+']+'+p+'?'), '');
		if(!query.length)return params;
		pairs = query.split('&');
		for(var i=0; i<pairs.length; i++){
			var kv = pairs[i].split('='), key, val;
			key = $.urldecode(kv[0]);
			val = $.urldecode(pairs[i].substr((kv[0]).length+1));
			params[key] = val;
		};
		return params;
	},
	//绑定点击外部
	registControl : function(options){
		if(!$.isPlainObject(options)){
			var operateControl = $(options).data('operateControl');
			if(!!operateControl && $.isFunction(operateControl))operateControl();
			return $(options);
		};
		options = $.extend({
			menu : '', //菜单选择器
			partner : '', //例外的控件,即匹配该选择器的控件都认为在内部
			outside : null //点击外部时执行, this:菜单
		}, options);
		var menu = $(options.menu);
		if(!menu.length || !!menu.data('registControl'))return menu;
		menu.data('registControl', true);
		menu.data('operateControl', operateControl);
		menu.parents('body').on('click', operateControlHandle);
		function operateControlHandle(e){
			var e = e||window.event, o = e.target||e.srcElement;
			do{
				if((options.partner && $(o).is(options.partner)) || $(o).is(menu))return;
				if((/^(html|body)$/i).test(o.tagName)){
					operateControl();
					return;
				};
				o = o.parentNode;
			}while(o.parentNode);
		}
		function operateControl(){
			if($.isFunction(options.outside))options.outside.call(menu);
		}
		return menu;
	},
	//仿iOS的UIActionSheet
	actionView : function(title, btns, e){
		return $(document.body).actionView(title, btns, e);
	},
	//popView
	popoverView : function(e, target){
		return $(document.body).popoverView(e, target);
	},
	//遮罩层与展示层
	overlay : function(target, type, callback, closeCallback){
		return $(document.body).overlay(target, type, callback, closeCallback);
	},
	//加载动画遮罩层
	overload : function(text, image, auto, callback){
		var local = top.document.body, delay = 0;
		if(!!$(document.body).data('overload.local'))local = $(document.body).data('overload.local');
		if(typeof text!='undefined' && text){
			if(!!$(document.body).data('overload.delay'))delay = Number($(document.body).data('overload.delay'));
		};
		setTimeout(function(){$(local).overload(text, image, auto, callback)}, delay);
	},
	//成功遮罩层
	overloadSuccess : function(text, auto, callback){
		if(typeof auto=='undefined')auto = 3000;
		if(!!$(document.body).data('overload.auto'))auto = Number($(document.body).data('overload.auto'));
		setTimeout(function(){$.overload(text, '.load-success', auto, callback)}, 0);
	},
	//失败遮罩层
	overloadError : function(text, auto, callback){
		if(typeof auto=='undefined')auto = 3000;
		if(!!$(document.body).data('overload.auto'))auto = Number($(document.body).data('overload.auto'));
		setTimeout(function(){$.overload(text, '.load-error', auto, callback)}, 0);
	},
	//问题遮罩层
	overloadProblem : function(text, auto, callback){
		if(typeof auto=='undefined')auto = 3000;
		if(!!$(document.body).data('overload.auto'))auto = Number($(document.body).data('overload.auto'));
		setTimeout(function(){$.overload(text, '.load-problem', auto, callback)}, 0);
	},
	//警告遮罩层
	overloadWarning : function(text, auto, callback){
		if(typeof auto=='undefined')auto = 3000;
		if(!!$(document.body).data('overload.auto'))auto = Number($(document.body).data('overload.auto'));
		setTimeout(function(){$.overload(text, '.load-warning', auto, callback)}, 0);
	},
	//底部弹出
	actionpicker : function(options){
		if(typeof options=='undefined'){
			var overlay = $('.actionpicker-overlay'), picker = $('.actionpicker');
			if(overlay.length){
				overlay.removeClass('actionpicker-overlay-x');
				setTimeout(function(){overlay.remove()}, 300);
			}
			if(picker.length){
				var height = picker.height();
				picker.css({transform:'translate3d(0,'+height+'px,0)', '-webkit-transform':'translate3d(0,'+height+'px,0)'});
				setTimeout(function(){picker.remove()}, 300);
			}
			if(!!picker.data('actionpicker-close') && $.isFunction(picker.data('actionpicker-close')))picker.data('actionpicker-close').call(picker);
			return;
		};
		options = $.extend({
			cls : '', //附加类名
			title : '', //toolBar标题
			leftBtn : null, //toolBar左按钮, 格式:{text:'left', cls:'', click:function(){}}
			rightBtn : null, //toolBar右按钮, 格式同上
			before : null, //弹出前执行
			after : null, //弹出后执行
			close : null //关闭前执行
		}, options);
		var overlay = $('.actionpicker-overlay'), picker = $('.actionpicker');
		if(!overlay.length){
			overlay = $('<div class="actionpicker-overlay"></div>');
			$(document.body).append(overlay);
			overlay.onclick(function(){
				if($.isFunction(options.close))picker.data('actionpicker-close', options.close);
				$.actionpicker();
			});
		}
		if(!picker.length){
			picker = $('<div class="actionpicker'+(options.cls.length?' '+options.cls:'')+'"></div>');
			$(document.body).append(picker);
		}else{
			picker.html('');
		}
		if(options.title.length || $.isPlainObject(options.leftBtn) || $.isPlainObject(options.rightBtn)){
			var toolBar = $('<div class="toolBar"></div>');
			picker.append(toolBar);
			if(options.title.length){
				toolBar.append('<div>'+options.title+'</div>');
			}
			if($.isPlainObject(options.leftBtn)){
				var left = $('<a href="javascript:void(0)" class="leftBtn '+(options.leftBtn.cls||'')+'">'+(options.leftBtn.text||'leftBtn')+'</a>');
				toolBar.append(left);
				if($.isFunction(options.leftBtn.click))left.click(function(){options.leftBtn.click.call(picker)});
			}
			if($.isPlainObject(options.rightBtn)){
				var right = $('<a href="javascript:void(0)" class="rightBtn '+(options.rightBtn.cls||'')+'">'+(options.rightBtn.text||'rightBtn')+'</a>');
				toolBar.append(right);
				if($.isFunction(options.rightBtn.click))right.click(function(){options.rightBtn.click.call(picker)});
			}
		}
		var modal = $('<div class="modal"></div>');
		picker.append(modal);
		if($.isFunction(options.before))options.before.call(modal);
		var height = picker.height();
		picker.css({transform:'translate3d(0,'+height+'px,0)', '-webkit-transform':'translate3d(0,'+height+'px,0)'});
		setTimeout(function(){
			overlay.addClass('actionpicker-overlay-x');
			picker.css({transform:'', '-webkit-transform':'', 'transition':'transform 300ms ease-out', '-webkit-transition':'-webkit-transform 300ms ease-out'});
			if($.isFunction(options.after))setTimeout(function(){options.after.call(modal)}, 300);
		}, 10);
	},
	//日期联动
	linkdate : function(options){
		options = $.extend({
			year : '#year', //年
			month : '#month', //月
			day : '#day', //日
			min : 1970, //最小年
			max : new Date().getFullYear()+15 //最大年
		}, options);
		if(!options.year.length || !$(options.year).length)return;
		var year = $(options.year), v = year.attr('v'), min = year.attr('min')||options.min, max = year.attr('max')||options.max,
			month = [], day = [], monthHead = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31], html = '';
		if(min=='this')min = new Date().getFullYear();
		if(max=='this')max = new Date().getFullYear();
		for(var i=min; i<=max; i++){
			var selected = !!v ? (v*1==i?true:false) : (new Date().getFullYear()==i?true:false);
			html += '<option value="'+i+'" '+(selected?'selected':'')+'>'+i+'</option>';
		};
		year.html(html).change(changeDay);
		if(options.month.length){
			month = $(options.month);
			v = month.attr('v');
			html = '';
			for(var i=1; i<=12; i++){
				var selected = !!v ? (v*1==i?true:false) : (new Date().getMonth()==i?true:false);
				html += '<option value="'+i+'" '+(selected?'selected':'')+'>'+i+'</option>';
			};
			month.html(html).change(changeDay);
			if(options.day.length){
				day = $(options.day);
				v = day.attr('v');
				html = '';
				for(var i=1; i<=monthHead[month.selected().val()*1]; i++){
					var selected = !!v ? (v*1==i?true:false) : (new Date().getDate()==i?true:false);
					html += '<option value="'+i+'" '+(selected?'selected':'')+'>'+i+'</option>';
				};
				day.html(html);
			}
		}
		function changeDay(){
			if(!month.length || !day.length)return;
			var index = day.selected().index() + 1;
			day.empty();
			var selectedYear = year.selected().val();
			var selectedMonth = month.selected().val();
			if(selectedMonth==2 && runYear(selectedYear)){ //闰年
				var html = '', n = 29;
				if(index>n)index = 29;
				for(var i=1; i<=n; i++)html += '<option value="'+i+'" '+(index==i?'selected':'')+'>'+i+'</option>';
				day.html(html);
			}else{
				var html = '', n = monthHead[selectedMonth-1];
				if(index>n)index = n;
				for(var i=1; i<=n; i++)html += '<option value="'+i+'" '+(index==i?'selected':'')+'>'+i+'</option>';
				day.html(html);
			}
		}
		function runYear(selectedYear){
			return (0 == selectedYear % 4 && ((selectedYear % 100 != 0) || (selectedYear % 400 == 0)));
		}
	},
	//获取本地坐标, callback接受一个参数:geo, 包含geo.longitude(经度), geo.latitude(纬度)
	getLocation : function(callback, notallowed){
		if(navigator.geolocation){
			$.overload(null);
			navigator.geolocation.getCurrentPosition(function(position){
				$.overload(false);
				var x = position.coords.longitude, y = position.coords.latitude;
				if($.isFunction(callback))callback({longitude:x, latitude:y});
			}, function(error){
				$.overload(false);
				switch(error.code){
					case error.PERMISSION_DENIED:console.log('定位: 用户不允许获取当前位置');break;
					case error.POSITION_UNAVAILABLE:console.log('定位: 无法获取当前位置');break;
					case error.TIMEOUT:console.log('定位: 操作超时');break;
					case error.UNKNOWN_ERROR:console.log('定位: 未知错误');break;
				}
				if($.isFunction(notallowed)){
					notallowed();
				}else if($.isFunction(callback)){
					$.getJSON('http://freegeoip.net/json/?callback=?', function(json){
						callback({longitude:json.longitude, latitude:json.latitude});
					});
				}
			}, {
				enableHighAcuracy : true, //指示浏览器获取高精度的位置，默认为false
				timeout : 5000, //指定获取地理位置的超时时间，默认不限时，单位为毫秒
				maximumAge : 3000 //最长有效期，在重复获取地理位置时，此参数指定多久再次获取位置
			});
		}else{
			console.log('定位: 此浏览器不支持Geolocation脚本');
			if($.isFunction(notallowed)){
				notallowed();
			}else if($.isFunction(callback)){
				$.getJSON('http://freegeoip.net/json/?callback=?', function(json){
					callback({longitude:json.longitude, latitude:json.latitude});
				});
			}
		}
	},
	//GPS坐标转百度坐标, callback接受一个参数:geo, 包含geo.longitude, geo.latitude
	getBaiduLocation : function(callback, notallowed){
		$.getLocation(function(geo){
			var x = geo.longitude, y = geo.latitude;
			$.getJSON('http://api.map.baidu.com/geoconv/v1/?ak=8dCDnV31Xg1QBbrWyrHmquR3&callback=?&coords='+x+','+y+'', function(json){
				if(json.status!=0)return;
				var lng = json.result[0].x, lat = json.result[0].y;
				if($.isFunction(callback))callback({longitude:x, latitude:y});
			});
		}, notallowed);
	},
	//经纬度转地点, callback接受一个参数:json, pois=1为获取附近小区
	getBaiduGeocoder : function(lat, lng, callback){
		$.getJSON('http://api.map.baidu.com/geocoder/v2/?ak=bwaWdfBatpKYvBGxOdne78Ij&callback=?&location='+lat+','+lng+'&output=json&pois=1', function(json){
			if(json.status!=0)return;
			if($.isFunction(callback))callback(json);
		});
	},
	//城市范围内的关键字地点, callback接受两个参数:xq,json
	getBaiduPlace : function(keyword, city, callback){
		if(!keyword.length){$.overloadError('请输入地区关键词');return};
		$.getJSON('http://api.map.baidu.com/place/v2/search?ak=bwaWdfBatpKYvBGxOdne78Ij&callback=?&q='+keyword+'&region='+city+'&output=json', function(json){
			if(json.status!=0){$.overloadError(json.message);return};
			var xq = [];
			for(var i=0; i<json.results.length; i++)xq.push({name:json.results[i].name, location:json.results[i].location});
			if($.isFunction(callback))callback(xq, json);
		});
	},
	//获取当前位置天气, callback接受两个参数:today,json
	getBaiduWeather : function(callback){
		$.getJSON('http://www.baidu.com/home/xman/data/superload?type=weather&callback=?', function(json){
			if(json.errNo!=0)return;
			if($.isFunction(callback))callback(json.data.weather.content.today, json);
		});
	},
	//获取当前位置附近小区或大厦, callback接受两个参数:xq,json
	getBaiduNearby : function(callback, notallowed){
		$.getBaiduLocation(function(geo){
			var lng = geo.longitude, lat = geo.latitude;
			$.getBaiduGeocoder(lat, lng, function(json){
				var xq = [];
				for(var i=0; i<json.result.pois.length; i++)xq.push({name:json.result.pois[i].name, location:{lat:json.result.pois[i].point.y, lng:json.result.pois[i].point.x}});
				if($.isFunction(callback))callback(xq, json);
			});
		}, notallowed);
	},
	//去除单位px
	fixedUnit : function(unit){
		if($.isNumeric(unit) || unit.indexOf('px')==-1)return unit;
		return Number(unit.replace('px','')||0);
	},
	//原页面和目标页面的编码是一致时，只需escape，如果页面是GB2312或其他的编码，而目标页面是UTF-8编码，就采用encodeURI或encodeURIComponent
	urlencode : function(str){
		if(typeof str!='string' || !str.length)return '';
		return str.urlencode();
	},
	urldecode : function(str){
		if(typeof str!='string' || !str.length)return '';
		return str.urldecode();
	},
	//加载图片后执行,callback:接受一个参数(图片宽高)
	imageload : function(src, callback, error){
		var img = new Image();
		img.src = src;
		var imageLoad = function(){
			img.onload = null;
			if(!this.readyState || this.readyState=='loaded' || this.readyState=='complete'){
				if($.isFunction(callback))callback({width:img.width, height:img.height});
			}
		};
		if('onload' in img){
			img.onload = imageLoad;
		}else{
			img.onreadystatechange = imageLoad;
		};
		img.onerror = function(e){
			console.log(e);
			img.onerror = null;
			if($.isFunction(error))error(e);
		}
	},
	//图片预加载
	imagePreload : function(imgs, callback){
		$(imgs).each(function(){
			if(!!_this.attr('src'))return true;
			var _this = $(this), img = new Image();
			img.src = _this.attr('url');
			if(img.complete){
				_this.attr('src', img.src);
				if($.isFunction(callback))callback.call(_this);
				return true;
			};
			img.onload = function(){
				_this.attr('src', img.src);
				if($.isFunction(callback))callback.call(_this);
			};
		});
	},
	//图片压缩, input:file控件, maxWidth:最大宽高, isSquare:是否强制正方形, callback:回调,参数为 base64 字符串
	imageCompress : function(input, maxWidth, isSquare, callback){
		var file = $(input).get(0).files[0];
		if(!file.type.match(/image.*/)){$.overloadError('The file is not an image');return};
		var reader = new FileReader();
		reader.onload = function(e){
			var image = $('<img/>');
			image.on('load', function(){
				var offsetX = 0, offsetY = 0, imageWidth = this.width, imageHeight = this.height;
				if(imageWidth<maxWidth && imageHeight<maxWidth){
					offsetX = Math.round((maxWidth - imageWidth) / 2);
					offsetY = Math.round((maxWidth - imageHeight) / 2);
				}else{
					if(imageWidth > imageHeight){
						imageWidth = maxWidth;
						imageHeight = Math.round(maxWidth * this.height / this.width);
						offsetY = - Math.round((imageHeight - maxWidth) / 2);
					}else{
						imageHeight = maxWidth;
						imageWidth = Math.round(maxWidth * this.width / this.height);
						offsetX = - Math.round((imageWidth - maxWidth) / 2);
					}
				}
				if($.isFunction(isSquare)){
					callback = isSquare;
					isSquare = false;
				};
				var canvas = document.createElement('canvas'), context = canvas.getContext('2d');
				if(isSquare){
					canvas.width = maxWidth;
					canvas.height = maxWidth;
					context.clearRect(0, 0, maxWidth, maxWidth);
				}else{
					offsetX = offsetY = 0;
					canvas.width = imageWidth;
					canvas.height = imageHeight;
					context.clearRect(0, 0, imageWidth, imageHeight);
				};
				context.imageSmoothingEnabled = true;
				context.drawImage(this, offsetX, offsetY, imageWidth, imageHeight);
				var xStart = canvas.width/2, yStart = canvas.height/2, xEnd = canvas.width/2+1, yEnd = canvas.height/2+1,
					imgData = context.getImageData(xStart, yStart, xEnd, yEnd),
					red = imgData.data[0], green = imgData.data[1], blue = imgData.data[2], alpha = imgData.data[3];
				if(red==0 && green==0 && blue==0 && alpha==0){
					context.clearRect(offsetX, offsetY, imageWidth, imageHeight);
					context.drawImage(this, offsetX, offsetY, imageWidth*1.1, imageHeight*2.720000091);
				};
				var data = canvas.toDataURL('image/png');
				if($.isFunction(callback))callback(data);
			});
			image.attr('src', e.target.result);
		};
		reader.readAsDataURL(file);
	},
	//字符串转base64
	base64Encode : function(str){
		var c1, c2, c3, base64EncodeChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		var i = 0, len= str.length, string = '';
		while(i < len){
			c1 = str.charCodeAt(i++) & 0xff;
			if(i == len){
				string += base64EncodeChars.charAt(c1 >> 2);
				string += base64EncodeChars.charAt((c1 & 0x3) << 4);
				string += "==";
				break;
			};
			c2 = str.charCodeAt(i++);
			if(i == len){
				string += base64EncodeChars.charAt(c1 >> 2);
				string += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
				string += base64EncodeChars.charAt((c2 & 0xF) << 2);
				string += '=';
				break;
			};
			c3 = str.charCodeAt(i++);
			string += base64EncodeChars.charAt(c1 >> 2);
			string += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
			string += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
			string += base64EncodeChars.charAt(c3 & 0x3F);
		};
		return string;
	},
	//解密base64
	base64Decode : function(str){
		var c1, c2, c3, c4, base64DecodeChars = new Array(
			-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
			-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
			-1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57,
			58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0,  1,  2,  3,  4,  5,  6,
			7,  8,  9,  10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
			25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
			37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1,
			-1, -1);
		var i = 0, len = str.length, string = '';
		while(i < len){
			do{
				c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
			}while(i < len && c1 == -1);
			if (c1 == -1) break;
			do{
				c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
			}while(i < len && c2 == -1);
			if (c2 == -1) break;
			string += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
			do{
				c3 = str.charCodeAt(i++) & 0xff;
				if(c3 == 61)return string;
				c3 = base64DecodeChars[c3]
			}while(i < len && c3 == -1);
			if (c3 == -1) break;
			string += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));
			do{
				c4 = str.charCodeAt(i++) & 0xff;
				if(c4 == 61)return string;
				c4 = base64DecodeChars[c4]
			}while(i < len && c4 == -1);
			if (c4 == -1) break;
			string += String.fromCharCode(((c3 & 0x03) << 6) | c4);
		};
		return string;
	},
	//MD5加密
	md5 : function(str){
		var hexcase = 0;
		function hex_md5(a) {if(a == "")return a;return rstr2hex(rstr_md5(str2rstr_utf8(a)))}
		function hex_hmac_md5(a, b) {return rstr2hex(rstr_hmac_md5(str2rstr_utf8(a), str2rstr_utf8(b)))}
		function md5_vm_test() {return hex_md5("abc").toLowerCase() == "900150983cd24fb0d6963f7d28e17f72"}
		function rstr_md5(a) {return binl2rstr(binl_md5(rstr2binl(a), a.length * 8))}
		function rstr_hmac_md5(c, f) {
			var e = rstr2binl(c);
			if (e.length > 16) {
				e = binl_md5(e, c.length * 8)
			};
			var a = Array(16),
			d = Array(16);
			for (var b = 0; b < 16; b++) {
				a[b] = e[b] ^ 909522486;
				d[b] = e[b] ^ 1549556828
			};
			var g = binl_md5(a.concat(rstr2binl(f)), 512 + f.length * 8);
			return binl2rstr(binl_md5(d.concat(g), 512 + 128))
		}
		function rstr2hex(c) {
			try {
				hexcase
			} catch(g) {
				hexcase = 0
			};
			var f = hexcase ? "0123456789ABCDEF": "0123456789abcdef", b = "", a;
			for (var d = 0; d < c.length; d++) {
				a = c.charCodeAt(d);
				b += f.charAt((a >>> 4) & 15) + f.charAt(a & 15)
			};
			return b
		}
		function str2rstr_utf8(c) {
			var b = "", d = -1, a, e;
			while (++d < c.length) {
				a = c.charCodeAt(d);
				e = d + 1 < c.length ? c.charCodeAt(d + 1) : 0;
				if (55296 <= a && a <= 56319 && 56320 <= e && e <= 57343) {a = 65536 + ((a & 1023) << 10) + (e & 1023);d++}
				if (a <= 127) {
					b += String.fromCharCode(a)
				} else {
					if (a <= 2047) {
						b += String.fromCharCode(192 | ((a >>> 6) & 31), 128 | (a & 63))
					} else {
						if (a <= 65535) {
							b += String.fromCharCode(224 | ((a >>> 12) & 15), 128 | ((a >>> 6) & 63), 128 | (a & 63))
						} else {
							if (a <= 2097151) b += String.fromCharCode(240 | ((a >>> 18) & 7), 128 | ((a >>> 12) & 63), 128 | ((a >>> 6) & 63), 128 | (a & 63))
						}
					}
				}
			};
			return b
		}
		function rstr2binl(b) {
			var a = Array(b.length >> 2);
			for (var c = 0; c < a.length; c++) {a[c] = 0}
			for (var c = 0; c < b.length * 8; c += 8) {a[c >> 5] |= (b.charCodeAt(c / 8) & 255) << (c % 32)}
			return a
		}
		function binl2rstr(b) {
			var a = "";
			for (var c = 0; c < b.length * 32; c += 8) {a += String.fromCharCode((b[c >> 5] >>> (c % 32)) & 255)}
			return a
		}
		function binl_md5(p, k) {
			p[k >> 5] |= 128 << ((k) % 32);
			p[(((k + 64) >>> 9) << 4) + 14] = k;
			var o = 1732584193, n = -271733879, m = -1732584194, l = 271733878;
			for (var g = 0; g < p.length; g += 16) {
				var j = o, h = n, f = m, e = l;
				o = md5_ff(o, n, m, l, p[g + 0], 7, -680876936);l = md5_ff(l, o, n, m, p[g + 1], 12, -389564586);m = md5_ff(m, l, o, n, p[g + 2], 17, 606105819);
				n = md5_ff(n, m, l, o, p[g + 3], 22, -1044525330);o = md5_ff(o, n, m, l, p[g + 4], 7, -176418897);l = md5_ff(l, o, n, m, p[g + 5], 12, 1200080426);
				m = md5_ff(m, l, o, n, p[g + 6], 17, -1473231341);n = md5_ff(n, m, l, o, p[g + 7], 22, -45705983);o = md5_ff(o, n, m, l, p[g + 8], 7, 1770035416);
				l = md5_ff(l, o, n, m, p[g + 9], 12, -1958414417);m = md5_ff(m, l, o, n, p[g + 10], 17, -42063);n = md5_ff(n, m, l, o, p[g + 11], 22, -1990404162);
				o = md5_ff(o, n, m, l, p[g + 12], 7, 1804603682);l = md5_ff(l, o, n, m, p[g + 13], 12, -40341101);m = md5_ff(m, l, o, n, p[g + 14], 17, -1502002290);
				n = md5_ff(n, m, l, o, p[g + 15], 22, 1236535329);o = md5_gg(o, n, m, l, p[g + 1], 5, -165796510);l = md5_gg(l, o, n, m, p[g + 6], 9, -1069501632);
				m = md5_gg(m, l, o, n, p[g + 11], 14, 643717713);n = md5_gg(n, m, l, o, p[g + 0], 20, -373897302);o = md5_gg(o, n, m, l, p[g + 5], 5, -701558691);
				l = md5_gg(l, o, n, m, p[g + 10], 9, 38016083);m = md5_gg(m, l, o, n, p[g + 15], 14, -660478335);n = md5_gg(n, m, l, o, p[g + 4], 20, -405537848);
				o = md5_gg(o, n, m, l, p[g + 9], 5, 568446438);l = md5_gg(l, o, n, m, p[g + 14], 9, -1019803690);m = md5_gg(m, l, o, n, p[g + 3], 14, -187363961);
				n = md5_gg(n, m, l, o, p[g + 8], 20, 1163531501);o = md5_gg(o, n, m, l, p[g + 13], 5, -1444681467);l = md5_gg(l, o, n, m, p[g + 2], 9, -51403784);
				m = md5_gg(m, l, o, n, p[g + 7], 14, 1735328473);n = md5_gg(n, m, l, o, p[g + 12], 20, -1926607734);o = md5_hh(o, n, m, l, p[g + 5], 4, -378558);
				l = md5_hh(l, o, n, m, p[g + 8], 11, -2022574463);m = md5_hh(m, l, o, n, p[g + 11], 16, 1839030562);n = md5_hh(n, m, l, o, p[g + 14], 23, -35309556);
				o = md5_hh(o, n, m, l, p[g + 1], 4, -1530992060);l = md5_hh(l, o, n, m, p[g + 4], 11, 1272893353);m = md5_hh(m, l, o, n, p[g + 7], 16, -155497632);
				n = md5_hh(n, m, l, o, p[g + 10], 23, -1094730640);o = md5_hh(o, n, m, l, p[g + 13], 4, 681279174);l = md5_hh(l, o, n, m, p[g + 0], 11, -358537222);
				m = md5_hh(m, l, o, n, p[g + 3], 16, -722521979);n = md5_hh(n, m, l, o, p[g + 6], 23, 76029189);o = md5_hh(o, n, m, l, p[g + 9], 4, -640364487);
				l = md5_hh(l, o, n, m, p[g + 12], 11, -421815835);m = md5_hh(m, l, o, n, p[g + 15], 16, 530742520);n = md5_hh(n, m, l, o, p[g + 2], 23, -995338651);
				o = md5_ii(o, n, m, l, p[g + 0], 6, -198630844);l = md5_ii(l, o, n, m, p[g + 7], 10, 1126891415);m = md5_ii(m, l, o, n, p[g + 14], 15, -1416354905);
				n = md5_ii(n, m, l, o, p[g + 5], 21, -57434055);o = md5_ii(o, n, m, l, p[g + 12], 6, 1700485571);l = md5_ii(l, o, n, m, p[g + 3], 10, -1894986606);
				m = md5_ii(m, l, o, n, p[g + 10], 15, -1051523);n = md5_ii(n, m, l, o, p[g + 1], 21, -2054922799);o = md5_ii(o, n, m, l, p[g + 8], 6, 1873313359);
				l = md5_ii(l, o, n, m, p[g + 15], 10, -30611744);m = md5_ii(m, l, o, n, p[g + 6], 15, -1560198380);n = md5_ii(n, m, l, o, p[g + 13], 21, 1309151649);
				o = md5_ii(o, n, m, l, p[g + 4], 6, -145523070);l = md5_ii(l, o, n, m, p[g + 11], 10, -1120210379);m = md5_ii(m, l, o, n, p[g + 2], 15, 718787259);
				n = md5_ii(n, m, l, o, p[g + 9], 21, -343485551);o = safe_add(o, j);n = safe_add(n, h);m = safe_add(m, f);l = safe_add(l, e)
			};
			return Array(o, n, m, l)
		}
		function md5_cmn(h, e, d, c, g, f) {return safe_add(bit_rol(safe_add(safe_add(e, h), safe_add(c, f)), g), d)}
		function md5_ff(g, f, k, j, e, i, h) {return md5_cmn((f & k) | ((~f) & j), g, f, e, i, h)}
		function md5_gg(g, f, k, j, e, i, h) {return md5_cmn((f & j) | (k & (~j)), g, f, e, i, h)}
		function md5_hh(g, f, k, j, e, i, h) {return md5_cmn(f ^ k ^ j, g, f, e, i, h)}
		function md5_ii(g, f, k, j, e, i, h) {return md5_cmn(k ^ (f | (~j)), g, f, e, i, h)}
		function safe_add(a, d) {var c = (a & 65535) + (d & 65535);var b = (a >> 16) + (d >> 16) + (c >> 16);return (b << 16) | (c & 65535)}
		function bit_rol(a, b) {return (a << b) | (a >>> (32 - b))};
		return hex_md5(str);
	},
	/*
	$.cookie('name'); //获取
	$.cookie('name', 'value'); //保存
	$.cookie('name', 'value', { expires:7, path:'/', domain:'jquery.com', secure:true }); //保存带有效期(单位天),路径,域名,安全协议
	$.cookie('name', '', { expires:-1 }); or $.cookie('name', null); //删除
	*/
	cookie : function(name, value, options){
		if(typeof value != 'undefined'){
			options = options || {};
			if(value === null){
				value = '';
				options.expires = -1;
			}
			if(typeof value != 'string')value = $.jsonString(value);
			var expires = '';
			if($.isNumeric(options)){
				var date = new Date();
				date.setTime(date.getTime() + (options * 24*60*60*1000));
				expires = ';expires='+date.toUTCString();
				options = {};
			}else if(options.expires && ($.isNumeric(options.expires) || options.expires.toUTCString)){
				var date = '';
				if($.isNumeric(options.expires)){
					date = new Date();
					date.setTime(date.getTime() + (options.expires * 24*60*60*1000));
				}else{
					date = options.expires;
				};
				expires = ';expires='+date.toUTCString();
			};
			var path = options.path ? ';path='+options.path : '';
			var domain = options.domain ? ';domain='+options.domain : '';
			var secure = options.secure ? ';secure' : '';
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
			return true;
		}else{
			value = null;
			if(typeof name == 'undefined'){
				value = decodeURIComponent(document.cookie);
			}else{
				if(document.cookie.length){
					var cookies = document.cookie.split(';');
					for(var i=0; i<cookies.length; i++){
						var cookie = $.trim(cookies[i]);
						if(cookie.substring(0, name.length+1) == (name + '=')){
							value = decodeURIComponent(cookie.substring(name.length+1));
							if($.isJsonString(value))value = $.jsonValue(value);
							break;
						}
					}
				}
			};
			return value;
		}
	},
	//浏览器本地存储, time:默认一天,单位天
	//$.localStorage('key'); 获取
	//$.localStorage('key', 'data'); 设置
	//$.localStorage('key', 'data', 1/24); 设置,过期时间为1小时
	//$.localStorage('key', null); 删除
	//$.localStorage(null); 删除所有
	localStorage : function(key, data, time){
		if(typeof key == 'undefined')return window.localStorage;
		if(key === null){
			if(window.localStorage){
				for(var i=0; i<window.localStorage.length; i++){
					if((window.localStorage.key(i).split('_') || [''])[0] === 'cache'){
						window.localStorage.removeItem(name);
					}
				}
			}
			return null;
		};
		key = {data:'cache_data_'+encodeURIComponent(key), time:'cache_time_'+encodeURIComponent(key)};
		if(window.localStorage){
			if(typeof data == 'undefined'){
				data = window.localStorage.getItem(key.data);
				if(data){
					if(Number(window.localStorage.getItem(key.time)) > (new Date()).getTime()){
						if($.isJsonString(data))data = $.jsonValue(data);
						return data;
					}else{
						window.localStorage.removeItem(key.data);
						window.localStorage.removeItem(key.time);
					}
				}
			}else if(data === null){
				window.localStorage.removeItem(key.data);
				window.localStorage.removeItem(key.time);
			}else{
				if(typeof time == 'undefined')time = 1;
				time = (new Date()).getTime() + Number(time) * 24*60*60*1000;
				if(typeof data != 'string')data = $.jsonString(data);
				window.localStorage.setItem(key.data, data);
				window.localStorage.setItem(key.time, time);
			}
		}else{
			if(typeof data == 'undefined'){
				data = $.cookie(key.data);
				if(data){
					if(Number($.cookie(key.time)) > (new Date()).getTime()){
						if($.isJsonString(data))data = $.jsonValue(data);
						return data;
					}else{
						$.cookie(key.data, null);
						$.cookie(key.time, null);
					}
				}
			}else if(data === null){
				$.cookie(key.data, null);
				$.cookie(key.time, null);
			}else{
				if(typeof time == 'undefined')time = 1;
				if(typeof data != 'string')data = $.jsonString(data);
				$.cookie(key.data, data, {expires:time});
				$.cookie(key.time, time, {expires:time});
			}
		}
		return null;
	},
	debugHTML : function(cls, w, h){
		if(typeof cls=='undefined')cls = 'debugHTML';
		var ele = $('.'+cls);
		if(!ele.length){
			if(typeof w=='undefined'){w = '100%';h = 30};
			ele = $('<div class="'+cls+'" style="position:fixed;left:0;top:0;z-index:99999;padding-left:5px;font-size:12px;text-align:left;box-shadow:0 0 20px rgba(0,0,0,0.5);background:rgba(255,255,255,0.85);"></div>');
			ele.css({width:w, height:h, 'line-height':h+'px'});
			$(document.body).append(ele);
		};
		return ele;
	},
	debug : function(o, n){
		var s, i, n = n||0, m = '', mn = n*2;
		if($.isArray(o)){
			if(o.length>0){
				s = 'Array['+o.length+']';
				for(i=0; i<mn; i++)m += '    ';
				s += '\n' + m + "(" + '\n';
				for(i=0; i<o.length; i++)s += m + '    [' + i + '] => ' + $.debug(o[i], n+1) + '\n';
				s += m + ')';
			}else{
				s = 'Array[0]';
			}
		}else if($.isPlainObject(o)){
			if(!$.isEmptyObject(o)){
				s = 'Object';
				for(i=0; i<mn; i++)m += '    ';
				s += '\n' + m + "{" + '\n';
				for(i in o)s += m + '    ' + i + ' => ' + $.debug(o[i], n+1) + '\n';
				s += m + '}';
			}else{
				s = 'Object { }';
			}
		}else{
			switch(typeof o){
				case 'undefined':
					s = '(Undefined)';break;
				case 'object':
					if(o instanceof Date)s = '(Date) ' + o;
					else if(o instanceof jQuery)s = '(Object) jQuery';
					else s = '(Null)';
					break;
				case 'number':
					s = '(Number) ' + o;break;
				case 'boolean':
					s = '(Boolean) ' + (o?'true':'false');break;
				case 'function':
					s = '(Function)';
					Function.prototype.getName = function(){
						return typeof this.name==='string' ? this.name : /function\s+([^\{\(\s]+)/.test(toString.call(this)) ? RegExp['$1'] : '';
					};
					var f = o.getName();
					if(f.length)s += ' ' + f + '()';
					break;
				case 'string':
					s = '(String)';
					var p = o.replace(/(^\s*)|(\s*$)/g, '');
					if(p.length)s += ' ' + p;
					break;
				default:
					s = '('+(typeof o)+')' + o;break;
			}
		}
		return s;
	}
});

})(jQuery);

if(typeof window.console == 'undefined')window.console = {log : function(){}};

//兼容浏览器支持localStorage
if(typeof window.localStorage == 'undefined'){
	var localStorageClass = function(){
		this.options = {
			expires : 365,
			domain : location.hostname
		}
	};
	localStorageClass.prototype = {
		getKey : function(key){
			return 'cache_data_' + encodeURIComponent(key);
		},
		key : function(i){
			var keys = [], cookies = document.cookie.split(';');
			for(var i=0; i<cookies.length; i++){
				var cookie = $.trim(cookies[i]);
				keys.push((cookie.split('=') || [''])[0]);
			}
			if(i<0 || i>=keys.length)return '';
			return keys[i];
		},
		//获取,不存在返回null
		getItem : function(key){
			return $.cookie(this.getKey(key));
		},
		//设置
		setItem : function(key, value){
			$.cookie(this.getKey(key), value, this.options);
		},
		//删除
		removeItem : function(key){
			$.cookie(this.getKey(key), null);
		},
		//删除所有
		clear : function(){
			var cookies = document.cookie.split(';');
			for(var i=0; i<cookies.length; i++){
				var cookie = $.trim(cookies[i]);
				if((cookie.split('_') || [''])[0] === 'cache')$.cookie((cookie.split('='))[0], null);
			}
		}
	};
	var localStorage = new localStorageClass();
	window.localStorage = localStorage;
};

//兼容浏览器支持Object.keys
if(typeof Object.keys == 'undefined'){
	Object.prototype.keys = function(object){
		var keys = [];
		for(var property in object){
			if(typeof property!='function' && Object.prototype.hasOwnProperty.call(obj, property)){
				keys.push(property);
			}
		}
		return keys;
	};
};

//提示框, 需引入alertUI.css
function alertUI(options){
	if(!$.isPlainObject(options)){
		var win, child;
		switch(options){
			case 'top':win = $(top.document.body);break;
			case 'parent':win = $(parent.document.body);break;
			default:win = $(document.body);break;
		};
		var dialog = $('.dialog-alert', win), overlay = $('.load-overlay', win);
		options = dialog.data('options');
		child = dialog.find('.contentbox').children().eq(0);
		if(!!dialog.data('close'))dialog.data('close').call(dialog);
		dialog.find('.tbox').addClass('tbox-out');
		overlay.removeClass('load-overlay-in');
		setTimeout(function(){
			if(!!child.data('originnext'))child.data('originnext').before(child);
			if(!!child.data('parent'))child.data('parent').append(child);
			dialog.remove();
			if($('.load-face, .load-view, .dialog-action, .dialog-alert, .dialog-popover', win).length)return;
			if(options.cls)overlay.removeClass('dialog-overlay-'+options.cls+'-x');
			if($.browser.ie6)$('select').css({visibility:'visible'});
			overlay.remove();
		}, 300);
	}else{
		options = $.extend({
			win : 'self', //显示窗口['self'|'parent'|'top']
			cls : '', //自定义窗口样式
			title : '', //标题
			width : 0, //总宽度
			height : 'auto', //总高度
			drag : false, //允许拖动, 需引入jdrag插件
			fixd : false, //随页面滚动(设置后不可拖动)
			auto : 0, //自动关闭, 单位毫秒
			nox : false, //不显示右上角的关闭按钮
			nobg : false, //不显示遮罩层
			html : '', //显示内容, string|url|对象, 默认为空
			btns : [], //按钮组, 不设置即不显示按钮
			clickbg : null, //点击背景执行
			before : null, //显示提示窗前执行
			after : null, //显示提示窗后执行
			close : null //关闭提示窗后执行
		}, options);
		var win, doc, dialog, html, contentbox, sl, st, ow, oh, overlay = [];
		if($.isFunction(options.before))options.before(options);
		if($.inArray(options.win, ['top','parent','self'])==-1)options.win = 'self';
		doc = $.document(options.win);
		switch(options.win){
			case 'top':win = $(top.document.body);break;
			case 'parent':win = $(parent.document.body);break;
			default:win = $(document.body);break;
		}
		if(!options.nobg){
			overlay = $('.load-overlay', win);
			if(!overlay.length){
				overlay = $('<div class="load-overlay"></div>');
				win.append(overlay);
			}
			if($.browser.ie6)overlay.css({width:Math.max(doc.clientWidth, doc.scrollWidth), height:Math.max(doc.clientHeight, doc.scrollHeight)});
			if(options.cls)overlay.addClass('dialog-overlay-'+options.cls);
			if($.browser.ie6)$('select').css({visibility:'hidden'});
			if($.isFunction(options.clickbg))overlay.onclick(function(){options.clickbg.call($(this))});
			else overlay.stopBounces();
		};
		dialog = $('<div class="dialog-alert"></div>');
		win.append(dialog);
		html = '<div class="tbox">\
					<div class="titlebox">'+(options.nox?'':'<a href="javascript:;" target="_self">×</a>')+'<span>'+options.title+'</span></div>\
					<div class="contentbox"></div>\
					'+(options.btns.length?'<div class="btnbox"><div></div></div>':'')+'\
				</div>';
		dialog.html(html).stopBounces();
		if(!options.title.length)dialog.find('.tbox').addClass('tbox-titlebox-none');
		contentbox = dialog.find('.contentbox');
		if(options.cls)dialog.addClass(options.cls);
		for(var i=0; i<options.btns.length; i++){
			var btntext = options.btns[i].text||'btn'+(i+1), tbtn = $('<button class="tbtn"><span>'+btntext+'</span></button>');
			dialog.find('.btnbox div').append(tbtn);
			tbtn.hover(function(){$(this).addClass('hover')},function(){$(this).removeClass('hover')});
			if(i==0)tbtn.addClass('tbtn_l');
			if(i==options.btns.length-1)tbtn.addClass('tbtn_r');
			if($.isFunction(options.btns[i].click)){
				tbtn.data('click', options.btns[i].click);
				tbtn.click(function(){$(this).data('click').call(dialog, options.win)});
			}
		}
		if(typeof options.html!='object'){
			var htm = options.html+'';
			if(htm.substring(0,7)=='http://'){
				contentbox.css('overflow', 'hidden').html('<iframe scrolling="auto" frameborder="0" src="'+options.html+'" width="100%" height="100%"></iframe>');
			}else{
				var object = $(htm);
				if(object.length){
					if(object.next().length)object.data('originnext', object.next());
					else object.data('parent', object.parent());
					contentbox.append(object);
				}else{
					contentbox.html(htm);
				}
			}
		}else{
			var object = $(options.html);
			if(object.length){
				if(object.next().length)object.data('originnext', object.next());
				else object.data('parent', object.parent());
				contentbox.append(object);
			}
		}
		if(!options.nox){
			dialog.find('.titlebox a').click(function(){alertUI(options.win)});
			$(document).keydown(function(e){
				var e = e||window.event, code = e.which||e.keyCode;
				if(code==27)alertUI(options.win);
			});
		};
		sl = $.scroll(options.win).left, st = $.scroll(options.win).top;
		var tbox = contentbox.parent(), padding = tbox.padding(), border = tbox.border(), boxmargin = contentbox.margin();
		if(!isNaN(options.width) && options.width>0){
			tbox.width(options.width-padding.left-padding.right-border.left-border.right);
			dialog.css('width', 'auto').css({'margin-left': -dialog.width()/2 });
		}
		if(!isNaN(options.height)){
			tbox.height(options.height-padding.top-padding.bottom-border.top-border.bottom);
			setTimeout(function(){
				contentbox.height(tbox.height()-tbox.find('.titlebox').outerHeight(true)-tbox.find('.btnbox').outerHeight(true)-boxmargin.top-boxmargin.bottom);
			}, 10);
		};
		var dch = doc.clientHeight>=dialog.outerHeight(false);
		oh = dch ? (doc.clientHeight-dialog.outerHeight(false))/2 : 0;
		dialog.css({top: ( ((options.fixd && (!$.browser.msie || ($.browser.msie && $.browser.version>=7)))?0:(dch?st:0)) + oh ) + 'px'});
		if(overlay.length && oh==0)overlay.css({height:dialog.outerHeight(false)});
		if(options.drag && !options.fixd)dialog.find('.titlebox').drag({target:'.dialog-alert'});
		if(options.fixd && dch){
			if($.browser.ie6){
				if($(document).css('background-image')=='none')$(document).css({'background-image':'url(about:blank)', 'background-attachment':'fixed'});
				dialog[0].style.cssText += ";left:expression(documentElement.scrollLeft+body.scrollLeft+"+ow+"+'px');"
				dialog[0].style.cssText += ";top:expression(documentElement.scrollTop+body.scrollTop+"+oh+"+'px');"
			}else{
				dialog.css({position:'fixed'});
			}
		}
		//if(options.btns.length && !$.browser.mobile)dialog.find('.btnbox div button:eq(0)').focus();
		if($.isFunction(options.after))options.after.call(dialog);
		if($.isFunction(options.close))dialog.data('close', options.close);
		if(options.auto>0)setTimeout(function(){alertUI(options.win)}, options.auto);
		setTimeout(function(){
			if(overlay.length){
				overlay.addClass('load-overlay-in');
				if(options.cls)overlay.addClass('dialog-overlay-'+options.cls+'-x');
			};
			tbox.addClass('tbox-x');
		}, 10);
		dialog.data('options', options);
		return contentbox;
	}
}

//截取字符串
String.prototype.left = function(length){
	return this.substring(0, length);
};
String.prototype.right = function(length){
	return this.substring(this.length-length);
};

//URL编码
String.prototype.urlencode = function(){
	if(!this.length)return '';
    return encodeURIComponent(this).replace(/!/g,'%21').replace(/'/g,'%27').replace(/\(/g,'%28').replace(/\)/g,'%29').replace(/\*/g,'%2A').replace(/%20/g,'+');
};
String.prototype.urldecode = function(){
	if(!this.length)return '';
	return decodeURIComponent(this);
};

//字符串转JSON
String.prototype.formatJSON = function(){
	if(!this.length)return null;
	return $.formatJSON(this);
};

//检测是否为空
String.prototype.isEmpty = function(){
    return $.trim(this).length == 0;
};

//检测中文
String.prototype.isCN = function(){
    return /^[\u4e00-\u9fa5]+$/.test(this);
};

//检测邮箱
String.prototype.isEmail = function(){
    return /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(this);
};

//检测固话
String.prototype.isTel = function(){
    return /^((\d{3,4}-)?\d{8}(-\d+)?|(\(\d{3,4}\))?\d{8}(-\d+)?)$/.test(this);
};

//检测手机
String.prototype.isMobile = function(){
    return /^(\+?86)?(13|15|18)[0-9]\d{8}$/.test(this);
};

//检测网址
String.prototype.isUrl = function(){
    return /^((http|https|ftp):\/\/)?[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/.test(this);
};

//检测日期
String.prototype.isDate = function(){
    return /^(?:(?!0000)[0-9]{4}[\/-](?:(?:0?[1-9]|1[0-2])[\/-](?:0?[1-9]|1[0-9]|2[0-8])|(?:0?[13-9]|1[0-2])[\/-](?:29|30)|(?:0?[13578]|1[02])[\/-]31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)[\/-]0?2[\/-]29)$/.test(this);
};

//计算字符串长度,中文占两位
String.prototype.lengths = function(){return $.trim(this.replace(/[^\x00-\xff]/g,'xx')).length};

//截取字符串
//正则replace内的函数参数为function(匹配到的字符,$1,$2,...,出现的位置[整数],字符本身)
//replace补充说明: 如果正则存在子表达式匹配, 如 /ab(\w+?)ef/, 函数有三个参数, 如 function(m,i,s), 那么 i:子表达式匹配的字符串, s:出现的位置, 如此向后推
String.prototype.cutFont = function(num){
	var x = 0, str = $.trim(this).replace(/[\s\S]/g, function(m, i, s){
		if(m.charCodeAt(0)>127)x++;
		if(x+i>=num)return '';
		return m;
	});
	return str;
};

//删除例如 [xxxx] 组合的字符串段落
String.prototype.deleteString = function(prefix, suffix){
	var str = '', length = this.length;
	if(length>0){
		if (suffix == this.substr(length-suffix.length)){
			if(this.indexOf(prefix) == -1){
				str = this.substring(0, length-1);
			}else{
				var tmp = this.substring(this.lastIndexOf(prefix), length-1);
				if(tmp.indexOf(suffix) == -1){
					str = this.substring(0, this.lastIndexOf(prefix));
				}else{
					str = this.substring(0, length-1);
				}
			}
		}else{
			str = this.substring(0, length-1);
		}
	};
	return str;
};

//填充前导零
String.prototype.fillZero = function(places){
	var _this = this;
	for(var i=0; i<places; i++)_this = '0'+''+_this;
	return _this.substr(-places);
};
Number.prototype.fillZero = function(places){
	var _this = this+'';
	return _this.fillZero(places);
};

//保留小数
String.prototype.round = function(places){
	if(isNaN(this)){
		return this.cutFont(places);
	}else{
		var _this = this * 1;
		return _this.round(places);
	}
};
Number.prototype.round = function(places){
	places = !isNaN(places=Math.abs(places)) ? places : 0;
	return Math.round(this * Math.pow(10,places)) / Math.pow(10,places);
	//Math.ceil() 小数进一
	//Math.floor() 取整数部分
	//Math.round() 四舍五入
};
String.prototype.numberFormat = function(places){ //保留小数后转为字符串
	var _this = this * 1;
	return _this.numberFormat(places);
};
Number.prototype.numberFormat = function(places){
	var _this = this.round(places) + '', arr = _this.split('.'), decimal = '';
	for(var i=0; i<places; i++)decimal += '0';
	if(arr.length>1)decimal = arr[1] + decimal;
	decimal = decimal.substr(0, places);
	return arr[0] + '.' + decimal;
};

//数字转中文
Number.prototype.cn = function(){
	var num = this;
	if(!/^\d*(\.\d*)?$/.test(num))return '';
	var AA = new Array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
	var BB = new Array('', '十', '百', '千', '万', '亿', '点', '');
	var a = ('' + num).replace(/(^0*)/g, '').split('.'), k = 0, re = '';
	for(var i=a[0].length-1; i>=0; i--){
		switch(k){
			case 0:re = BB[7] + re;break;
			case 4:if(!new RegExp('0{4}\\d{'+(a[0].length-i-1)+'}$').test(a[0]))re = BB[4] + re;break;
			case 8:re = BB[5] + re; BB[7] = BB[5]; k=0;break;
		}
		if(k%4==2 && a[0].charAt(i+2)!=0 && a[0].charAt(i+1)==0)re = AA[0] + re;
		if(a[0].charAt(i)!=0)re = AA[a[0].charAt(i)] + BB[k%4] + re;
		k++;
	}
	if(re.substr(0,2)=='一十')re = re.substr(1);
	//加上小数部分(如果有小数部分)
	if(a.length>1){
		re += BB[6];
		for(var i=0; i<a[1].length; i++)re += AA[a[1].charAt(i)];
	}
	return re;
};

//时间戳转日期
String.prototype.toDate = function(formatStr){
	var number = this * 1;
	return number.toDate(formatStr);
};
Number.prototype.toDate = function(formatStr){
	var date = new Date(this * 1000);
	if(typeof formatStr == 'undefined'){
		return date;
	}else{
		return date.formatDate(formatStr);
	}
};

//日期转时间戳
String.prototype.time = function(){
	var date = this.date();
	return date.time();
};
Date.prototype.time = function(){
	var timestamp = this.getTime()+'';
    return timestamp.substr(0, 10) * 1;
};

//日期字符串转日期
String.prototype.date = function(){
	var m = /^(\d{4})-(\d{1,2})-(\d{1,2})( (\d{1,2}))?(:(\d{1,2}))?(:(\d{1,2}))?$/.exec(this);
	if(!m.length)return null;
	var date = this.split(/\D/);
	--date[1];
	for(var i=0; i<(6-date.length); i++){
		date.push(0);
	}
	for(var i=0; i<date.length; i++){
		date[i] = date[i] * 1;
	}
	eval('date = new Date('+date.join(',')+')');
    return date;
};

//加上天数得到日期
String.prototype.DateAdd = function(t, number){
	var date = this.date();
	return date.DateAdd(t, number);
};
Date.prototype.DateAdd = function(t, number){
	number = parseInt(number);
	var date = this.clone(), l = { 'q':3, 'w':7 },
	k = { 'y':'FullYear', 'q':'Month', 'm':'Month', 'w':'Date', 'd':'Date', 'h':'Hours', 'n':'Minutes', 's':'Seconds', 'ms':'MilliSeconds' };
	eval('date.set'+k[t]+'(date.get'+k[t]+'()+'+((l[t]||1)*number)+')');
	return date;
};

//两个日期的时间差
String.prototype.DateDiff = function(t, dtEnd){
	var date = this.date();
	return date.DateDiff(t, dtEnd);
};
Date.prototype.DateDiff = function(t, dtEnd){
	var dtStart = this.clone();
	if(typeof dtEnd == 'string')dtEnd = dtEnd.date();
	switch(t){
		case 'y':
			return dtEnd.getFullYear() - dtStart.getFullYear();
		case 'm':
			return (dtEnd.getMonth() + 1) + ((dtEnd.getFullYear() - dtStart.getFullYear()) * 12) - (dtStart.getMonth() + 1);
		case 'd':
			return parseInt((dtEnd - dtStart) / 86400000);
		case 'w':
			return parseInt((dtEnd - dtStart) / (86400000 * 7));
		case 'h':
			return parseInt((dtEnd - dtStart) / 3600000);
		case 'n':
			return parseInt((dtEnd - dtStart) / 60000);
		case 's':
			return parseInt((dtEnd - dtStart) / 1000);
	}
};

//减去天数得到日期
String.prototype.DateDiffNum = function(t, number){
	var date = this.date();
	return date.DateDiffNum(t, number);
};
Date.prototype.DateDiffNum = function(t, number){
	var d = this.clone(), k = { 'd':24*60*60*1000, 'h':60*60*1000, 'n':60*1000, 's':1000 };
	d = d.getTime();
	d = d - number * k[t];
	return new Date(d);
};

//日期格式化, callback:接受1个参数date(date为对象,包含year, month, day, hour, minute, second, week)
String.prototype.formatDate = function(formatStr, callback){
	var date = this.date();
	return date.formatDate(formatStr, callback);
};
Date.prototype.formatDate = function(formatStr, callback){
	var format = formatStr ? formatStr : 'yyyy-mm-dd hh:nn:ss',
		monthName = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		monthFullName = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		weekName = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
		weekFullName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		monthNameCn = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
		monthFullNameCn = monthNameCn;
		weekNameCn = ['日', '一', '二', '三', '四', '五', '六'],
		weekFullNameCn = ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
		getYearWeek = function(y, m, d){
			var date = new Date(y, m, d), firstDay = new Date(y, 0, 1),
			day = Math.round((date.valueOf()-firstDay.valueOf()) / 86400000);
			return Math.ceil( (day + ((firstDay.getDay()+1)-1)) / 7 );
		},
		year = this.getFullYear()+'', month = (this.getMonth()+1)+'', day = this.getDate()+'', week = this.getDay(),
		hour = this.getHours()+'', minute = this.getMinutes()+'', second = this.getSeconds()+'',
		yearWeek = getYearWeek(this.getFullYear(), this.getMonth(), this.getDate())+'';
	format = format.replace(/yyyy/g, year);
	format = format.replace(/yy/g, (this.getYear()%100)>9 ? (this.getYear()%100)+'' : '0'+(this.getYear()%100));
	format = format.replace(/mme/g, monthFullName[month-1]);
	format = format.replace(/me/g, monthName[month-1]);
	format = format.replace(/mmc/g, monthFullNameCn[month-1]);
	format = format.replace(/mc/g, monthNameCn[month-1]);
	format = format.replace(/mm/g, month.length<2?'0'+month:month);
	format = format.replace(/m/g, month);
	format = format.replace(/dd/g, day.length<2?'0'+day:day);
	format = format.replace(/d/g, day);
	format = format.replace(/hh/g, hour.length<2?'0'+hour:hour);
	format = format.replace(/h/g, hour);
	format = format.replace(/nn/g, minute.length<2?'0'+minute:minute);
	format = format.replace(/n/g, minute);
	format = format.replace(/ss/g, second.length<2?'0'+second:second);
	format = format.replace(/s/g, second);
	format = format.replace(/wwe/g, weekFullName[week]);
	format = format.replace(/we/g, weekName[week]);
	format = format.replace(/ww/g, weekFullNameCn[week]);
	format = format.replace(/w/g, weekNameCn[week]);
	format = format.replace(/WW/g, yearWeek.length<2?'0'+yearWeek:yearWeek);
	format = format.replace(/W/g, yearWeek);
	if($.isFunction(callback))callback.call(this, {year:year, month:month, day:day, hour:hour, minute:minute, second:second, week:week});
	return format;
	//d.toLocaleDateString() 获取当前日期
	//d.toLocaleTimeString() 获取当前时间
	//d.toLocaleString() 获取日期与时间
};

//微博形式时间
String.prototype.fromToday = function(){
	var date = this.date();
	if(!date)return this;
    return date.fromToday();
};
Date.prototype.fromToday = function(){
	var date = this, d1 = date.getTime(), d2 = new Date().getTime(), between = Math.floor(d2/1000) - Math.floor(d1/1000);
	if(between < 60)return '刚刚';
	if(between >= 60 && between < 3600)return parseInt(between/60) + '分钟前';
	if(between >= 3600 && between < 86400)return parseInt(between/3600) + '小时前';
	if(between >= 86400 && between <= 864000)return parseInt(between/86400) + '天前';
	if(between > 864000)return this.formatDate('yyyy-mm-dd');
};

//某月最后一天
String.prototype.monthLastDay = function(){
	var date = this.date();
	return date.monthLastDay();
};
Date.prototype.monthLastDay = function(){
	var date = this.clone();
	date.setMonth(date.getMonth()+1);
	date.setDate(0);
	return date;
};

//某日所在周的第一天与最后一天
String.prototype.getWeekDays = function(monIsFirstDay){
	var date = this.date();
	return date.getWeekDays(monIsFirstDay);
};
Date.prototype.getWeekDays = function(monIsFirstDay){
	var time = this.clone(), first = null, last = null;
	time.setDate(time.getDate() - time.getDay() + (monIsFirstDay?1:0));
	first = time.DateAdd('d',0);
	time.setDate(time.getDate() + 6);
	last = time.DateAdd('d',0);
	return {first:first, last:last};
};

//当前周为当前年的第几周
String.prototype.weekNO = function(){
	var date = this.date();
	return date.weekNO();
};
Date.prototype.weekNO = function(){
	var d = this.clone(), totalDays = 0, years = d.getYear();
	if(years<1000)years += 1900;
	var days = new Array(12);
	days[0] = 31;
	days[1] = d.isLeapYear() ? 29 : 28;
	days[2] = 31;
	days[3] = 30;
	days[4] = 31;
	days[5] = 30;
	days[6] = 31;
	days[7] = 31;
	days[8] = 30;
	days[9] = 31;
	days[10] = 30;
	days[11] = 31;
	if(d.getMonth()==0){
		totalDays = totalDays + d.getDate();
	}else{
		var curMonth = d.getMonth();
		for(var i=0; i<curMonth; i++)totalDays += days[i];
		totalDays += d.getDate();
	}
	var week = Math.ceil(totalDays / 7);
	return week;
};

//是否闰年
String.prototype.isLeapYear = function(){
	var date = this.date();
	return date.isLeapYear();
};
Date.prototype.isLeapYear = function(){
	return (0 == this.getYear() % 4 && ((this.getYear() % 100 != 0) || (this.getYear() % 400 == 0)));
};

//把日期分割成数组
Date.prototype.toArray = function(){
	var d = this.clone(), arr = [];
	arr[0] = d.getFullYear();
	arr[1] = d.getMonth();
	arr[2] = d.getDate();
	arr[3] = d.getHours();
	arr[4] = d.getMinutes();
	arr[5] = d.getSeconds();
	return arr;
};

//深度复制, Object类型使用 var obj = jQuery.extend(true, {}, oldObj) 来复制
Date.prototype.clone = function(){return new Date(this.valueOf())};
Array.prototype.clone = function(){
	var arr = this.valueOf(), newArr = [];
	for(var i=0; i<arr.length; i++)newArr.push(arr[i].clone());
	return newArr;
};

//获取指定元素的索引
Array.prototype.indexOf = function(val){
	for(var i=0; i<this.length; i++){
		if(val instanceof Date){
			if(Date.parse(this[i]) == Date.parse(val))return i;
		}else{
			if(this[i] == val)return i;
		}
	}
	return -1;
};

//删除指定元素
Array.prototype.remove = function(val){
	var index = this.indexOf(val);
	if(index>-1)this.splice(index, 1);
	return this;
};

//删除指定索引
Array.prototype.removeIndex = function(index){
	if(isNaN(index) || index>this.length)return false;
	this.splice(index, 1);
	return this;
};

//获取第一个元素
Array.prototype.first = function(){
	if(!this.length)return null;
	return this[0];
};

//获取最后一个元素
Array.prototype.last = function(){
	if(!this.length)return null;
	return this[this.length-1];
}

//检测数据库是否已存在该值
//checkField({o:取值对象,a:显示信息对象,n:取值对象中/英文名称,t:表名,d:字段名,v:数据库值(添加时为空),h:相对根目录路径,l:是否英文,u:目标网址})
function checkField(z){
	var o = $(z.o), a = $(z.a), n = z.n, t = z.t||'', d = z.d||'', h = z.h||'', l = z.l, u = z.u||'', v = o.val();
	if(!o.length || !a.length || t=='' || d=='' || u=='')return;
	o.removeData('bad').removeData('checked');
	if(!!!o.data('area'))o.data('area', a.html());
	if(v==z.v || v==''){
		a.html((o.data('area')||'')).removeClass('checkfield');
		o.removeData('area');
		return;
	};
	a.html('<img src="'+h+'images/load02.gif" border="0" align="absmiddle"> Loading...');
	$.get(u, 't='+t+'&d='+d+'&v='+v, function(html){
		switch(html){
			case '0':
				var error = v + (!l?' 已存在':' already exists');
				a.html('<img src="'+h+'images/check_error.gif" border="0" align="absmiddle"> '+error);
				o.data('bad', n+' '+error);break;
			case '1':
				a.html('<img src="'+h+'images/check_right.gif" border="0" align="absmiddle">');
				o.data('checked',true);break;
			default:
				a.html('<font color="#6699FF">'+(!l?'查询失败':'Check error')+'</font>');break;
		}
	});
}

//Cookie操作, cookie(名字,值[为null时删除],过期时间[单位天])
function cookie(name, val, date){
	if(typeof val!='undefined'){
		if(typeof val!='object'){
			var days = 30; //默认保存天数
			if(typeof date=='undefined'){
				days = '';
			}else{
				if(!isNaN(date))days = date;
				days = {expires:days};
			};
			$.cookie(name, escape(val), days);
		}else{
			var value = $.cookie(name);
			if(value!=null)$.cookie(name, null);
		}
	}else{
		var value = $.cookie(name);
		if(value!=null)return unescape(value[2]);
		return null;
	}
}

//专门获取ASP格式的Cookie, ASP格式:Response.Cookies("xxx")("zzz"), 如果ASP设置Cookie时有中文, 必须escape
function GetCookie(domain, name){
	var re = /%25u/g, str = new String(document.cookie), header = domain + '=', position = str.indexOf(header);
	if(position != -1){
		str = str.substring(position + header.length);
		header = name + '=';
		position = str.indexOf(header);
		if(position != -1){
			str = str.substring(position + header.length);
			if(str.indexOf('&')>-1)str = str.substring(0, str.indexOf('&'));
			if(str.indexOf(';')>-1)str = str.substring(0, str.indexOf(';'));
			return unescape(str.replace(re,'%u'));
		}
	}
	return '';
}

//<a href="javascript:void(0)" onClick="setHome(this,'{@SITE_URL}')">设为首页</a>
//<a href="javascript:void(0)" onClick="setFav('{@SITE_NAME}','{@SITE_URL}')">加入收藏Collection</a>
function setHome(sObj, sURL){
	try{document.body.style.behavior='url(#default#homepage)';document.body.setHomePage(sURL)}
	catch(e){
		$.overloadError('非 IE 浏览器请手动将【'+sURL+'】设为首页');
	}
}
function setFav(sTitle, sURL){
	try{window.external.addFavorite(sURL, sTitle)}
	catch(e){
		try{window.sidebar.addPanel(sTitle, sURL, '')}
		catch(e){
			var ctrl = $.browser.safari ? 'Command/Cmd': 'Ctrl';
			$.overloadError('请按 '+ctrl+'+D 键添加到收藏夹');
		}
	}
}

//FLASH免激活, <script>flashView("images/top.swf",873,154,'',0) //flashView(路径,宽度,高度,传值,直接write)< /script>
function flashView(u, w, h, v, f){
	var html = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="'+w+'" height="'+h+'">'
		+ '<param name="movie" value="'+u+'">'
		+ '<param name="wmode" value="transparent">'
		+ '<param name="loop" value="true">'
		+ '<param name="quality" value="high">';
	if(v)html += '<param name="flashvars" value="'+v+'">';
	html += '<embed src="'+u+'" loop=true wmode=transparent quality=high swLiveConnect=false '+(v?'flashvars="'+v+'"':'')+' width="'+w+'" height="'+h+'" type="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed>'
		+ '</object>';
	if(f){return html}else{document.write(html)}
}

//禁用按钮
function dbtn(id, par){
	var el;
	if(typeof par=='string'){
		if($.inArray(par.substr(0,1), ['#','.',':'])>-1)par = $(par);
		else par = $('#'+par);
	}
	if(typeof id=='string'){
		if($.inArray(id.substr(0,1), ['#','.',':'])>-1)el = $(id, par);
		else el = $('#'+id, par);
	}else el = $(id);
	el.hasClass('disabled') ? el.removeClass('disabled') : el.addClass('disabled');
	el[0].disabled = !el[0].disabled;
}

function changeFaceSize(){
	if(!$.browser.mobile)return;
	var clientWidth = $.window().width;
	$('html').css('font-size', clientWidth/320*10);
};
$(window).resize(changeFaceSize);
$(function(){
	changeFaceSize();
	if(!$.browser.mobile || ($.browser.ios && parseInt($.browser.version)>=8))$('body').addClass('half');
	//$(document.body).on('focus', 'a, area, :submit, :button, :reset, :image, :radio, :checkbox', function(){$(this).blur()});
	$(':text, :hidden, :password, textarea, select, form').each(function(){
		if(!!!$(this).attr('id') && !!$(this).attr('name'))$(this).attr('id', $(this).attr('name'));
	});
	$(document.body).on('click', 'a.return', function(){history.go(-1)});
	$(document.body).on('click', 'a.delete', function(){
		var q = '真的要删除?';
		if(!!$(this).attr('confirm'))q = $(this).attr('confirm');
		if(!confirm(q))return false;
		var warning = $(this).attr('warning');
		if(!!warning){$.overloadError(warning);return false}
	});
	$(document.body).on('click', 'a.confirm', function(){
		var q = '真的执行该操作?';
		if(!!$(this).attr('confirm'))q = $(this).attr('confirm');
		if(!confirm(q))return false;
		var warning = $(this).attr('warning');
		if(!!warning){$.overloadError(warning);return false}
	});
	$(document.body).on('click', '.tableView-animate li a', function(){
		var _this = $(this);
		if(_this.outerWidth(false)<_this.parent().width()-10 || _this.hasClass('active') || _this.hasClass('release'))return;
		_this.addClass('active');
		setTimeout(function(){
			_this.addClass('release');
			setTimeout(function(){
				_this.removeClass('active');
				setTimeout(function(){_this.removeClass('release')}, 1200);
			}, 1200);
		}, 1200);
	});
	setTimeout(function(){
		$('.datepicker').datepicker();
		$('input.focus').focus();
	}, 0);
	if($.browser.mobile){
		$.getScript('/js/fastclick.js', function(){FastClick.attach(document.body)});
		if($.browser.ios)document.body.addEventListener('touchstart', function(){});
	}
});