var DMSPage = 1;
var DMSEndPage = false;
var DMS_USER_TARGET_URL = '';
var DMS_COMMENT_NUM = 15;

function dmsLayoutInit(){
	if(dmsConfig.isBlack == true){
		alert('抱歉，您已被管理员踢出本频道！');
		window.location = '../livestream/'+dmsConfig.channelId;
		return;
	}
	
	this.init = function(){
		var link = document.createElement('link');
		link.id = 'DMSCss';
		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = DMS_CDN_SITE + 'layout/wsp/livestreamPc/style.css';
		link.rel = 'stylesheet';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(link);

		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_CDN_SITE + 'layout/wsp/livestreamPc/jquery.qqFace.js';
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
	}

	this.cssLoad = function(){
		if(document.getElementById('DMSCss') && typeof($.fn.qqFace) == 'function'){
			clearInterval(dmsLayoutHandle.cSSInterval);
			var html = '\
			<div class="dms-container">\
				<div class="dms-title">\
					<p class="count">';
			if(dmsConfig.viewNumType == 1){
				html +=	'<span class="icon"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/icon02.png" width="31" height="26"></span>\
						<span class="nub" id="pvNum">'+dmsConfig.userNum+'</span>';
			}
			else if(dmsConfig.viewNumType == 2){
				html +=	'<span class="icon"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/icon02.png" width="31" height="26"></span>\
						<span class="nub" id="pvNum">'+dmsConfig.pvNum+'</span>';
			}
			html +=		'<span class="icon"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/icon03.png" width="31" height="26"></span>\
						<span class="nub" id="praiseNum">'+dmsConfig.praiseNum+'</span>\
						<span class="icon"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/icon04.png" width="31" height="26"></span>\
						<span class="nub" id="msgNum">'+dmsConfig.msgNum+'</span>\
						<span class="icon"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/icon05.png" width="31" height="26"></span>\
						<span class="nub" id="shareNum">'+dmsConfig.shareNum+'</span>\
					</p>\
				</div>\
				<div class="dms-message-container">\
					<ul id="dmsMessage"></ul>\
				</div>\
				<div class="dms-send-container">\
					<div class="emt"></div>\
					<div class="dms-publish-btn" onClick="dmsLayoutHandle.publish();">发布</div>\
					<div class="dms-textarea-container">\
						<input type="text" placeholder="请输入聊天文字" id="aodianyun-dms-text" onkeydown="javascript:if(event.keyCode==13){dmsLayoutHandle.publish();}"></span>\
					</div>\
					<div class="dms-cb"></div>\
				</div>\
			</div>\
			';
			$('#'+dmsConfig.container).html(html);
			var client = dmsConfig.uid + String(Math.ceil(new Date().getTime()/1000));
			ROP.Enter(dmsConfig.dmsPubKey,dmsConfig.dmsSubKey,client);
			dmsCommonHandle.getCommentList(DMSPage,dmsLayoutHandle,DMS_COMMENT_NUM);
			$(".dms-message-container").scroll(function(){
				var dmsScrollTop = $(this)[0].scrollTop;
				if(dmsScrollTop <= 0 && DMS_GET_COMMENT_STATUS == false){
					dmsLayoutHandle.nextPage();
				}
			});
			//表情
			$('.emt').qqFace({
				id:'facebox',
				assign:'aodianyun-dms-text', 
	    		path:DMS_CDN_SITE + 'layout/wsp/livestreamPc/arclist/' //表情存放的路径
	    	});
		}
	}
	
	this.prevPage = function(){
		if(DMSPage == 1){
			return;
		}
		DMSPage--;
		dmsCommonHandle.getCommentList(DMSPage,dmsLayoutHandle);
	}
	this.nextPage = function(){
		if(DMSEndPage){
			return;
		}
		DMSPage++;
		dmsCommonHandle.getCommentList(DMSPage,dmsLayoutHandle,DMS_COMMENT_NUM);
	}
	
	this.createCommentHtml = function(data){
		if(typeof(data.List) != 'undefined' && data.List.length > 0){
			DMSEndPage = false;
			var msg = '';
			for(var i=(data.List.length-1); i>=0; i--){
				try{
					var dmsData = $.parseJSON(data.List[i].msg);
					var nick = dmsData.nick;
					var ava = dmsData.ava;
					var content =  dmsData.content;
					var url = dmsData.url ? dmsData.url : '';
					var uin = dmsData.uid ? dmsData.uid : '';
					var time = dmsCommonHandle.formatDate(dmsData.time);
				}
				catch(e){
					var nick = '匿名用户';
					var ava = DMS_CDN_SITE + 'layout/wsp/livestream/def_ava_120.png';
					var content = '无内容';
				}
				var mode = /http:\/\/([A-z0-9]+[_\-]?[A-z0-9]+\.)*[A-z0-9]+\-?[A-z0-9]+\.[A-z]{2,}(\/.*)*\/?/;
				if(!mode.test(ava)){
					ava = DMS_CDN_SITE + 'layout/wsp/livestream/def_ava_120.png';
				}
				if(uin == 'areward'){
					msg += '\
						<li>\
							<div class="dms-header"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/speaker.png"></div>\
							<div class="message">\
							  <div class="message-info">\
								<div class="nick"><font color="red">系统消息</font></div>\
								<div class="date">'+time+'</div>\
								<div class="clear"></div>\
							  </div>\
							  <div class="message-content"><span class="dms-areward">'+decodeURI(nick)+'</span>打赏了 <span class="dms-areward">'+content+'元</span></div> \
			      			</div>\
						</li>';
				}
				else{
					msg += '\
						<li>\
							<div class="dms-header"><img src="'+decodeURI(ava)+'"></div>\
							<div class="message">\
							  <div class="message-info">\
								<div class="nick">'+decodeURI(nick)+'</div>\
								<div class="date">'+time+'</div>\
								<div class="clear"></div>\
							  </div>\
							  <div class="message-content">'+dmsLayoutHandle.replaceEm(dmsCommonHandle.htmlspecialchars(decodeURI(content)))+'</div>\
							</div>\
						</li>\
						';
				}
			}
			$('#dmsMessage').prepend(msg);
			if(DMSPage == 1){
				$('.dms-message-container').scrollTop($('#dmsMessage').height());
			}
			else{
				var h = 0;
				for(var i = $('#dmsMessage > li').length -1; i > $('#dmsMessage > li').length - DMS_COMMENT_NUM - 1; i--){
					h += $('#dmsMessage > li').eq(i).height();
				}
				$('.dms-message-container').scrollTop(h);
			}
		}
		else{
			if(DMSPage > 1){
				DMSPage--;
				DMSEndPage = true;
			}
		}
	}

	this.alertMsg = function(msg){
		if(typeof(dlgMsg) == 'function'){
			dlgMsg(2,'提示',msg);
		}
		else{
			alert(msg);
		}
	}

	this.publishHtml = function(dmsData){
		var mode = /^([0-9]+)$/;
		var time = dmsData.time;
		if(mode.test(dmsData.time)){
			time = dmsCommonHandle.formatDate(dmsData.time);
		}
		var msg = '';
		if(dmsData.uid == 'areward'){
			msg += '\
				<li>\
					<div class="dms-header"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/speaker.png"></div>\
					<div class="message">\
					  <div class="message-info">\
						<div class="nick"><font color="red">系统消息</font></div>\
						<div class="date">'+time+'</div>\
						<div class="clear"></div>\
					  </div>\
					  <div class="message-content"><span class="dms-areward">'+decodeURI(dmsData.nick)+'</span>打赏了 <span class="dms-areward">'+dmsData.content+'元</span></div> \
	      			</div>\
				</li>';
		}
		else{
			msg += '\
				<li>\
					<div class="dms-header"><img src="'+decodeURI(dmsData.ava)+'"></div>\
					<div class="message">\
					  <div class="message-info">\
						<div class="nick">'+decodeURI(dmsData.nick)+'</div>\
						<div class="date">'+time+'</div>\
						<div class="clear"></div>\
					  </div>\
					  <div class="message-content">'+dmsLayoutHandle.replaceEm(dmsCommonHandle.htmlspecialchars(decodeURI(dmsData.content)))+'</div>\
					</div>\
				</li>\
				';
		}
		$('#dmsMessage').append(msg);
		var h = 0;
		for(var i = 0; i < DMS_COMMENT_NUM; i++){
			h += $('#dmsMessage > li').eq(i).height();
		}
		var b = $('#dmsMessage').height() - $('.dms-message-container')[0].scrollTop;
		if((typeof(dmsData.uid) != 'undefined' && dmsData.uid == dmsConfig.uid) || b <= h){
			$('.dms-message-container').scrollTop($('#dmsMessage').height());
		}
	}
	
	this.publish = function(){
		var content = document.getElementById("aodianyun-dms-text").value;
		if(content == ''){
			dmsLayoutHandle.alertMsg('请输入内容');
			return;
		}
		if(content.length > 100){
			dmsLayoutHandle.alertMsg('内容不能大于100个字');
			return;
		}

    	content = content.replace(/\[([\u4e00-\u9fa5]{1,})\]/g,function(){
	       if(typeof(dmsFaceArr2[arguments[1]]) == 'undefined'){
    			return arguments[0];
    		}else{
        		return '['+dmsFaceArr2[arguments[1]]+']';
        	}
	   	 });

		var dmsData = {
			uid:dmsConfig.uid,
			nick:encodeURI(dmsConfig.nick),
			ava:encodeURI(dmsConfig.ava),
			url:'',
			content:encodeURI(content),
			time:Math.ceil(new Date().getTime()/1000)
		};

		dmsLayoutHandle.publishHtml(dmsData);
		document.getElementById("aodianyun-dms-text").value = '';
		dmsCommonHandle.wxPublish(dmsData);
	}

	this.replaceEm = function (str){
		str = str.replace(/\</g,'&lt;');
		str = str.replace(/\>/g,'&gt;');
		str = str.replace(/\n/g,'<br/>');
		str = str.replace(/\[em_([0-9]*)\]/g,'<img src="' + DMS_CDN_SITE + 'layout/wsp/livestreamPc/arclist/$1.gif" border="0" />');
		return str;
	}
	
}

ROP.On("enter_suc",function(){
	ROP.Subscribe(dmsConfig.topic);
	ROP.Subscribe(dmsConfig.controlTopic);
	ROP.Subscribe('channel_'+dmsConfig.channelId);
	if(window.console){
		console.log('dms connect success');
	}
})
ROP.On("enter_fail",function(err){
	if(err == 'domain not allow'){
		$('.dmsSystemMessage').html("<p>您还未开通免费DMS服务，请到先到奥点云<a href='http://www.aodianyun.com' target='_blank'>www.aodianyun.com</a>开通服务后重试。</p>");
	}
	else{
		$('.dmsSystemMessage').html('<p style="color:red;">聊天服务加载失败！请刷新页面重试。</p>');
	}
})
ROP.On("publish_data",function(data,topic){
	if(window.console){
		console.log(data,topic);
	}
	var num = $('#dmsMessage .dms-message-info').length;
	if(num >= 100){
		$('#dmsMessage .dms-message-info').eq(0).remove();
	}
	
	var dmsData = $.parseJSON(data);
	if(dmsData.uid == dmsConfig.uid && topic == dmsConfig.topic){
		var liObj = $('#dmsMessage').find('.message-operate');
		liObj.eq((liObj.length-1)).attr('time',dmsData.time);
		return;
	}
	if(topic == dmsConfig.topic){
		dmsLayoutHandle.publishHtml(dmsData);
	}
	else{
		if(dmsData.cmd == 'nums'){
			$('#pvNum').html(dmsData.userNum);
			$('#praiseNum').html(dmsData.praiseNum);
			$('#msgNum').html(dmsData.msgNum);
			$('#shareNum').html(dmsData.shareNum);
		}
		else{
			var str = '';
			if(dmsData.cmd == 'kill'){
				if(dmsData.state == 1){
					str = decodeURI(dmsData.nick)+'被踢出了房间！';
					if(dmsConfig.uid == dmsData.uid){
						alert('抱歉，您已被管理员踢出本频道！');
						window.location = '../livestream/'+dmsConfig.channelId;
					}
				}
			}
			else if(dmsData.cmd == 'gap'){
				if(dmsData.state == 1){
					str = decodeURI(dmsData.nick)+'被禁言了！';
					dmsConfig.isGaps = true;
					if(dmsConfig.uid == dmsData.uid){
						alert('抱歉，您已被管理员禁言！');
					}
				}
				else{
					dmsConfig.isGaps = false;
					if(dmsConfig.uid == dmsData.uid){
						alert('管理员解除了对您的禁言！');
					}
				}
			}
			else if(dmsData.cmd == 'mic'){
				if(typeof(LSS_PLAYER_STATUS) != 'undefined'){
					LSS_PLAYER_STATUS = dmsData.state;
				}
				if(dmsData.state == 1){
					str = '直播开始啦！';
				}
				else{
					str = '直播结束！';
				}
				if(typeof(changeLiveState) == 'function'){
					changeLiveState();
				}
			}
			else if(dmsData.cmd == 'chatOpt'){
				if(dmsData.state == 1){
					$('#dmsStatusBtn').html('关闭聊天');
					str = '管理员开启了聊天';
				}
				else{
					$('#dmsStatusBtn').html('开启聊天');
					str = '管理员关闭了聊天';
				}
			}
			else if(dmsData.cmd == 'bkt'){
				if(typeof(changeLcpsState) == 'function'){
					changeLcpsState();
				}
			}
			if(str == ''){
				return;
			}
			var msg = '';
			
			msg += '\
			<li>\
				<div class="dms-header"><img src="'+DMS_CDN_SITE+'layout/wsp/livestreamPc/img/speaker.png"></div>\
				<div class="message">\
				  <div class="message-info">\
					<div class="nick"><font color="red">系统消息</font></div>\
					<div class="date">'+dmsCommonHandle.formatDate()+'</div>\
					<div class="clear"></div>\
				  </div>\
				  <div class="message-content">'+str+'</div> \
      			</div>\
			</li>';
			$('#dmsMessage').append(msg);
			$('.dms-message-container').scrollTop($('#dmsMessage').height());
		}
	}
})
ROP.On("losed",function(){
	$('.dmsSystemMessage').html('<p style="color:red;">您已和聊天服务失去了连接！请刷新页面重试。</p>');
})

var dmsLayoutHandle = new dmsLayoutInit();
dmsLayoutHandle.init();
dmsLayoutHandle.cSSInterval = setInterval("dmsLayoutHandle.cssLoad()",100);