
function TheServerKind(name, id, state)
{
    //部门或服务项目名称
    this.name = name;
    //部门或服务项目编号
    this.id = id;
    //部门或服务项目状态：0-离线,1-离开,2-繁忙,3-在线,4-对话中
    this.state = state; 
}
var ServerKindArray=new Array(1);var ServerKindlist=new Object();
ServerKindArray[0]=ServerKindlist["%U5BA2%U670D%U90E8"]=new TheServerKind("%u5ba2%u670d%u90e8",13,0);
var LR_sysurl = 'http://pwt.zoosnet.net/';
var LR_websiteid = 'PWT31187391';
function LR_checkagent(_lr_na){var _lr_o = _lr_na.split('|');for(_lr_w=0;_lr_w<_lr_o.length;_lr_w++){if(navigator.userAgent.toLowerCase().indexOf(_lr_o[_lr_w])>-1)return true;}return false;}
function lr_refer5238() {if (typeof (lr_refer5236) != 'undefined') {return '&r='+escape(lr_refer5236);}var lr_refer5235=LR_getCookie('lr_refer5');if(lr_refer5235!=null){LR_SetCookie('lr_refer5','',-60);return '&r='+lr_refer5235;}var ur = document.referrer;var i = ur.lastIndexOf('.');return '&rf1=' + escape(ur.substring(0, i)) + '&rf2=' + escape(ur.substr(i));}
function openZoosUrl(url,data)
{
if (typeof(openZoosUrl_UserDefine) == 'function'){if(openZoosUrl_UserDefine())return;};
if (typeof (LR_istate) != 'undefined') {LR_istate=3;}
var lr_url1=url;
if (typeof (LR_opentimeout) != 'undefined' && typeof (LR_next_invite_seconds) != 'undefined')LR_next_invite_seconds=999999;
	if(url=='sendnote')
	{
		url=LR_sysurl+'LR/Chatwin2.aspx?siteid='+LR_websiteid+'&cid='+LR_cid+'&sid='+LR_sid+'&lng='+LR_lng+'&p='+escape(location.href)+lr_refer5238();
	}
	else
	{
		url=((LR_userurl0 && typeof (LR_userurl) != 'undefined')?LR_userurl:(LR_sysurl+'LR/Chatpre.aspx'))+'?id='+LR_websiteid+'&cid='+LR_cid+'&lng='+LR_lng+'&sid='+LR_sid+'&p='+escape(location.href)+lr_refer5238();
	}
	if(typeof(LR_UserSSL) != 'undefined' && LR_UserSSL && url.charAt(4)==':')url=url.substring(0,4)+'s'+url.substring(4,url.length);
	if(!data){if(typeof(LR_explain)!='undefined' && LR_explain!=''){url+='&e='+escape(escape(LR_explain));}else if(typeof(LiveAutoInvite1)!='undefined'){url+='&e='+escape(escape(LiveAutoInvite1));}}
	if(typeof(LR_username)!='undefined'){url+='&un='+escape(LR_username);}
	if(typeof(LR_userdata)!='undefined'){url+='&ud='+escape(LR_userdata);}
	if(typeof(LR_ucd)!='undefined'){url+='&ucd='+escape(LR_ucd);}
	url+='&msg='+escape(LR_msg);
	if(data)url+=data;url+='&d='+new Date().getTime();if (typeof (LR_imgint) != 'undefined')url+='&imgint='+LR_imgint;
	if(lr_url1=='fchatwin')
	{
		LR_ClientEnd=0;window.location=url+'&f=1';return;
	}
	if(LR_sidexists!=2 && LiveReceptionCode_isonline && lr_url1!='bchatwin' && !LR_isMobile && typeof(LR_pm003)!='undefined' && LR_pm003==1){LR_HideInvite();LR_istate=1;clickopenmini=1;LR_showminiDiv();lrminiMax();return;} 
	if (LR_isMobile){LR_ClientEnd=0;window.location=url;return;}
	var oWindow;
	try
	{
		if (LR_isMobile || LR_checkagent('opera|safari|se 2.x'))
		{
			oWindow=window.open(url);
		}
		else
		{
			oWindow=window.open(url,'LRWIN_'+LR_websiteid, 'toolbar=no,width=760,height=460,resizable=yes,location=no,scrollbars=no,left='+((screen.width  - 760) / 4)+',top='+((screen.height - 460) / 4));
		}
		if(oWindow==null)
		{
			LR_ClientEnd=0;window.location=url;
			return;
		}
		oWindow.focus();
	}
	catch(e){
		if(oWindow==null){LR_ClientEnd=0;window.location=url;}
	}
}
function LR_SetCookie(name,value,minutes)
{
	if (name.indexOf(LR_websiteid)==-1){name='N'+LR_websiteid+name;}
	var exp  = new Date();
	exp.setTime(exp.getTime() + minutes*60*1000);
	document.cookie = name + '='+ escape (value) + ';'+getRDomain()+'path=/;expires=' + exp.toGMTString();
}
function LR_getCookie(name)
{
	if (name.indexOf(LR_websiteid)==-1){
	var arr = document.cookie.match(new RegExp('(^| )'+'N'+LR_websiteid+name+'=([^;]*)(;|$)'));
	if(arr != null) return unescape(arr[2]);
	}
	var arr = document.cookie.match(new RegExp('(^| )'+name+'=([^;]*)(;|$)'));
	if(arr != null) return unescape(arr[2]);
	if(name=='LiveWS'+LR_websiteid)
	{
		LR_SetCookie(name,LR_Tick,2628000);
		return LR_Tick;
	}
	if(name=='LiveWS'+LR_websiteid+'sessionid')
	{
		LR_SetCookie(name,LR_Tick,720);
		return LR_Tick;
	}
	return null;
}
function getRDomain(){var d,a=location.hostname,b='',c=['.com','.co','.cn','.vn','.info','.net','.org','.me','.mobi','.us','.biz','.top','.xxx','.ca','.co.jp','.js.cn','.com.cn','.net.cn','.org.cn','.gov.cn','.cq.cn','.bj.cn','.zj.cn','.gd.cn','.hn.cn','.hl.cn','.sh.cn','.hb.cn','.ac.cn','.edu.cn','.mx','.tv','.ws','.ag','.com.ag','.net.ag','.org.ag','.am','.asia','.at','.be','.com.br','.net.br','.bz','.com.bz','.net.bz','.cc','.com.vn','.com.co','.net.co','.nom.co','.de','.es','.com.es','.nom.es','.org.es','.eu','.fm','.fr','.gs','.in','.co.in','.firm.in','.gen.in','.ind.in','.net.in','.org.in','.it','.jobs','.jp','.ms','.com.mx','.nl','.nu','.co.nz','.net.nz','.org.nz','.se','.tc','.tk','.tw','.com.tw','.com.hk','.idv.tw','.org.tw','.hk','.co.uk','.me.uk','.org.uk','.vg','.name'];return c=c.join('|').replace('.','\\.'),d=new RegExp('\\.?([^.]+('+c+'))$'),a.replace(d,function(a,c){b=c}),''!=b?'domain=.'+b+';':b}
var LR_cookie_test=1;function LR_cookie_test1() {LR_SetCookie('LR_cookie_t0',1,0.05);LR_cookie_test=(LR_getCookie('LR_cookie_t0')!=null);}LR_cookie_test1();
if(typeof(LR_Tick) == 'undefined')var LR_Tick='37e9bce5113d4ea98f9d379fbc0ce707';
function LiveReception_regetCookie()
{
return LR_getCookie('LiveWS'+LR_websiteid);
}
function LiveReception_regetnewCookie()
{
return LR_getCookie('LiveWS'+LR_websiteid+'sessionid');
}
function openZoosWindow_olist(url,data)
{
openZoosUrl(url,data);}
