if(typeof(DMS_SITE) == 'undefined'){
	var DMS_SITE = '';
}
var DMS_CDN_SITE = DMS_SITE == '' ? 'http://cdn.aodianyun.com/dms/' : DMS_SITE + '/dms/';
//var DMS_CDN_SITE = '/dms/';
var DMS_JQUERY_PATH = 'http://cdn.aodianyun.com/static/jquery/jquery-1.7.2.min.js';
var DMS_JQUERY_JSON_PATH = 'http://cdn.aodianyun.com/static/jquery/jquery-json.min.js';
var DMS_WEB_SOCKET_ROP = DMS_CDN_SITE + 'rop_client_ws.js';
var DMS_ROP_CLIENT_PATH = DMS_CDN_SITE + 'rop_client.js';
var DMS_PUBLISH_PATH = DMS_SITE + '/wsp/index.php?r=dms/publish';
var DMS_HISTORY_PATH = DMS_SITE + '/wsp/index.php?r=dms/getHistoryMessage';

var DMS_USER_LOGIN_STATUS = false;
var DMS_EQUIPMENT = 'pc';
var DMS_AGENT = '';
var DMS_GET_COMMENT_STATUS = false;
function dmsInit(){
	if(typeof(dmsConfig.container) == 'undefined' || typeof(dmsConfig.layout) == 'undefined' || typeof(dmsConfig.channelId) == 'undefined' || typeof(dmsConfig.partyId) == 'undefined' || typeof(dmsConfig.dmsAppKey) == 'undefined' || typeof(dmsConfig.dmsPubKey) == 'undefined' || typeof(dmsConfig.dmsSubKey) == 'undefined' || typeof(dmsConfig.chatOpt) == 'undefined' || typeof(dmsConfig.topic) == 'undefined' || typeof(dmsConfig.controlTopic) == 'undefined'){
		if(window.console){
			console.log('参数错误');
		}
		return;
	}
	if(window.WebSocket){
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_WEB_SOCKET_ROP;
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
	}
	else{
		document.getElementsByTagName("body")[0].innerHTML +='<div id="rop_context"></div>';

		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_ROP_CLIENT_PATH;
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
	}
	if(typeof(jQuery) == 'undefined'){
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_JQUERY_PATH;
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
	}
	else{
		if(typeof($.toJSON) != 'function'){
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = DMS_JQUERY_JSON_PATH;
			s.charset = 'UTF-8';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
		}
	}
	
	var funInterval = setInterval("this.funLoad()",100);
	var _this = this;
	
	this.funLoad = function(){
		if(typeof(jQuery) != 'undefined' && typeof(ROP) != 'undefined' && typeof($.toJSON) == 'function'){
			if(window.WebSocket && typeof(Paho) == 'undefined'){
				return;
			}
			clearInterval(funInterval);
			var layoutPath = DMS_CDN_SITE + 'layout/wsp/' + dmsConfig.layout + '/layout.js';
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = layoutPath;
			s.charset = 'UTF-8';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
		}
	}
}

function dmsCommon(){
	
    var userAgentInfo = navigator.userAgent;
    var Agents = ["Android","iPhone","SymbianOS","Windows Phone","WP","iPad","iPod"];
    for(var v = 0; v < Agents.length; v++){
        if(userAgentInfo.indexOf(Agents[v]) > 0){
            DMS_EQUIPMENT = 'phone';
			DMS_AGENT = Agents[v];
            break;
        }
    }
	
	var _this = this;
	
	this.wxLogin = function(){
		var host = encodeURIComponent('http://' + window.location.host);
		if(typeof(dmsConfig.wxAppid) == 'undefined' || dmsConfig.wxAppid == ''){
			var wxLoginUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx4f394838f20e789c&redirect_uri=http%3A%2F%2Fwww.aodianyun.com%2Fopenlogin%2Fwx%2FwspLogin.php?partyId=' + dmsConfig.partyId + '&response_type=code&scope=snsapi_userinfo&state='+ host +'&connect_redirect=1#wechat_redirect';
		}
		else{
			if(!dmsConfig.redirect){
				var url = 'http%3A%2F%2Fwx.aodianyun.com%2Fopenlogin%2Fwx%2FwspLogin.php?partyId=' + dmsConfig.partyId;
			}
			else{
				var url = dmsConfig.redirect;
			}
			var wxLoginUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + dmsConfig.wxAppid + '&redirect_uri=' + url + '&response_type=code&scope=snsapi_userinfo&state='+ host +'&connect_redirect=1#wechat_redirect';
		}
		window.location = wxLoginUrl;
		return;
	}
	
	this.getCommentList = function(page,obj,num){
		if(DMS_GET_COMMENT_STATUS == true){
			return;
		}
		DMS_GET_COMMENT_STATUS = true;
		if(!num){
			num = 20;
		}
		$.ajax({
			type:'POST',
			url:DMS_HISTORY_PATH,
			dataType:'json',
			data:{partyId:dmsConfig.partyId,page:page,num:num},
			async:false,
			success:function(data){
				DMS_GET_COMMENT_STATUS = false;
				obj.createCommentHtml(data);
			}
		});
	}
	
	this.errorMessage = function(msg){
		$('#'+dmsConfig.container).html(msg);
	}
	
	this.htmlspecialchars = function (str){    
		str = str.replace(/&/g, '&amp;');  
		str = str.replace(/</g, '&lt;');  
		str = str.replace(/>/g, '&gt;');  
		str = str.replace(/"/g, '&quot;');  
		str = str.replace(/'/g, '&#039;');  
		return str;  
	}
	
	// private property
	_keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
 
	// public method for encoding
	this.encode = function (input) {
		input = String(input);
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		input = _this._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
			_keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
			_keyStr.charAt(enc3) + _keyStr.charAt(enc4);
		}
		return output;
	}
 
	// public method for decoding
	this.decode = function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = _keyStr.indexOf(input.charAt(i++));
			enc2 = _keyStr.indexOf(input.charAt(i++));
			enc3 = _keyStr.indexOf(input.charAt(i++));
			enc4 = _keyStr.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = _this._utf8_decode(output);
		return output;
	}
 
	// private method for UTF-8 encoding
	this._utf8_encode = function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
		return utftext;
	}
 
	// private method for UTF-8 decoding
	this._utf8_decode = function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
	
	this.getIeVerson = function(){
		var mode = /Microsoft Internet Explorer/i;
		if(mode.test(navigator.appName)){
			var mode = /MSIE 6\./i;
			if(mode.test(navigator.appVersion)){
				return 6;
			}
			var mode = /MSIE 7\./i;
			if(mode.test(navigator.appVersion)){
				return 7;
			}
			var mode = /MSIE 8\./i;
			if(mode.test(navigator.appVersion)){
				return 8;
			}
			var mode = /MSIE 9\./i;
			if(mode.test(navigator.appVersion)){
				return 9;
			}
			var mode = /MSIE 10\./i;
			if(mode.test(navigator.appVersion)){
				return 10;
			}
			var mode = /MSIE 11\./i;
			if(mode.test(navigator.appVersion)){
				return 11;
			}
		}
		return 0;


	}
	
	this.formatDate = function(now){
		if(now){
			var now = new Date(now*1000);
		}
		else{
			var now = new Date().getTime();
			var now = new Date(now);
		}
		var year = now.getFullYear();     
		var month = now.getMonth()+1;     
		var date = now.getDate();     
		var hour = now.getHours();     
		var minute = now.getMinutes();    
		if(minute < 10){
			minute = '0' + minute.toString();
		} 
		return year+"-"+month+"-"+date+" "+hour+":"+minute;
	}
	
	this.wxPublish = function(data){
		if(dmsConfig.chatOpt != 1){
			alert('抱歉，管理员已经关闭了聊天！');
			return;
		}
		if(typeof(dmsConfig.isGaps) != 'undefined' &&  dmsConfig.isGaps == true){
			alert('抱歉，您已被管理员禁止发言！');
			return;
		}
		$.ajax({
			type:'POST',
			url:DMS_PUBLISH_PATH,
			data:{data:data,partyId:dmsConfig.partyId},
			dataType:'json',
			async:true,
			success:function(data){
				if(data.Flag != 100){
					if(data.Info.error == 'disallow chat at blacklists'){
						alert('抱歉，您已被管理员禁言');
						return;
					}
				}
			}
		});
	}
}

var dmsCommonHandle = new dmsCommon();
dmsInit();