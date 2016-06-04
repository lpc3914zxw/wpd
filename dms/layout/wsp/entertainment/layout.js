var DMSPage = 1;
var DMSEndPage = false;
var DMS_COMMENT_NUM = 15;

var dmsBH = $('body').height();
var dmsTextStatus = 0;
var dmsInputInterval;
var dmsInputInterval2;

function dmsInputFocus(){
	if(dmsTextStatus == 1){
		return;
	}
	var dmsBH2 = $('body').height();
	if(dmsBH2 < dmsBH){
		dmsTextStatus = 0;
		clearInterval(dmsInputInterval);
		dmsInputInterval2 = setInterval(dmsInputBlur,100);
	}
	dmsTextStatus = 0;
}


function dmsInputBlur(){
	if(dmsTextStatus == 1){
		return;
	}
	var dmsBH2 = $('body').height();
	dmsTextStatus = 1;
	if(dmsBH2 >= dmsBH){
		$('.dms-input-mask').hide();
		$('.dms-message-container').show();
		$('.dms-message-container').scrollTop($('#dmsMessage').height());
		$('#dmsSend').removeClass('dms-send-container-hover');
		dmsTextStatus = 0;
		clearInterval(dmsInputInterval2);
	}
	dmsTextStatus = 0;
}

function dmsLayoutInit(){
	if(dmsConfig.isBlack == true){
		alert('抱歉，您已被管理员踢出本频道！');
		window.location = '../entertainment/'+dmsConfig.channelId;
		return;
	}
	
	this.init = function(){
		var link = document.createElement('link');
		link.id = 'DMSCss';
		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = DMS_CDN_SITE + 'layout/wsp/entertainment/layout.css';
		link.rel = 'stylesheet';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(link);

		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_CDN_SITE + 'layout/wsp/entertainment/jquery.qqFace.js';
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);

		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_CDN_SITE + 'layout/wsp/entertainment/jquery.gift.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);

		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = DMS_CDN_SITE + 'layout/wsp/entertainment/idangerous.swiper.min.js';
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
	}
	
	this.toWrite = function(){
		if(DMS_AGENT != 'iPhone' && DMS_AGENT != 'iPad'){
			$('.dms-message-container').hide();
			$('.dms-input-mask').show();
			$('#dmsSend').addClass('dms-send-container-hover');
			dmsInputInterval = setInterval(dmsInputFocus,100);
		}
	}
	
	this.cssLoad = function(){
		if(document.getElementById('DMSCss') && typeof($.fn.qqFace) == 'function' && typeof(Swiper) != 'undefined'){
			clearInterval(dmsLayoutHandle.cSSInterval);

			var html = '\
<div class="dms-container">\
	<div class="dms-message-container">\
		<div id="dmsMessage">\
		</div>\
    </div>\
    <div class="dms-send-container" id="dmsSend">\
    	<div class="dms-le-container">\
        	<input type="image" src="'+DMS_CDN_SITE+'layout/wsp/entertainment/emotiona.png" value="" class="dms-login-button" />\
        </div>\
        <div class="dms-button-container">\
';
			
			if(typeof(dmsConfig.uid) != 'undefined' && typeof(dmsConfig.uid) != ''){
        		html += '<input type="button" onClick="dmsLayoutHandle.publish();" value="发 布" />';
			}
			else{
				html += '<input type="button" onClick="dmsCommonHandle.wxLogin();" value="登 录" />';
			}
			html += '<img src="'+DMS_CDN_SITE+'layout/wsp/entertainment/gift-btn.png"  class="aodianyun-dms-gift"/>';
        	html += '\
		</div>\
        <div class="dms-textarea-container">\
        	<div class="dms-textarea-box">\
        		<input type="text" id="aodianyun-dms-text" onclick="dmsLayoutHandle.toWrite();" />\
            </div>\
        </div>\
        <div class="dms-cb"></div>\
    </div>\
<div class="dms-input-mask" style="display:none;"></div>\
</div>\
';

			$('#'+dmsConfig.container).html(html);
			ROP.Enter(dmsConfig.dmsPubKey,dmsConfig.dmsSubKey);
			dmsCommonHandle.getCommentList(DMSPage,dmsLayoutHandle,DMS_COMMENT_NUM);
			$(".dms-message-container").scroll(function(){
				var dmsScrollTop = $(this)[0].scrollTop;
				if(dmsScrollTop <= 0 && DMS_GET_COMMENT_STATUS == false){
					dmsLayoutHandle.nextPage();
				}
			});

			//表情
			$('.dms-login-button').qqFace({
				id:'facebox',
				assign:'aodianyun-dms-text', 
	    		path:DMS_CDN_SITE + 'layout/wsp/entertainment/arclist/' //表情存放的路径
    		});
    		//礼物
    		$('.aodianyun-dms-gift').aodianGIFT({
				id:'aodianyun-dms-giftbox',
				assign:'aodianyun-dms-text', 
	    		path:DMS_CDN_SITE + 'layout/wsp/entertainment/img-gift/' //礼物存放的路径
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
				var time = dmsCommonHandle.formatDate(data.List[i].time);
				try{
					var dmsData = $.parseJSON(data.List[i].msg);
					var nick = dmsData.nick;
					var ava = dmsData.ava;
					var content =  dmsData.content;
					var url = dmsData.url ? dmsData.url : '';
				}
				catch(e){
					var nick = '匿名用户';
					var ava = DMS_USER_AVA;
					var content = '无内容';
				}
				var mode = /http:\/\/([A-z0-9]+[_\-]?[A-z0-9]+\.)*[A-z0-9]+\-?[A-z0-9]+\.[A-z]{2,}(\/.*)*\/?/;
				if(!mode.test(ava)){
					ava = DMS_CDN_SITE + 'layout/wsp/entertainment/def_ava_120.png';
				}
				msg += '\
				<div class="dms-message-info">\
					<div class="dms-user-info">\
						<div class="dms-ava"><img src="'+decodeURI(ava)+'" /></div>\
					</div>\
					<div class="dms-content">\
						<div class="dms-header"><span class="dms-nick">'+decodeURI(nick)+'</span><span class="dms-time">'+time+'</span></div>\
						<div>'+dmsLayoutHandle.replaceEm(dmsCommonHandle.htmlspecialchars(decodeURI(content)))+'</div>\
					</div>\
					<div class="dms-cb"></div>\
				</div>\
				';
			}
			$('#dmsMessage').prepend(msg);
			if(DMSPage == 1){
				$('.dms-message-container').scrollTop($('#dmsMessage').height());
			}
			else{
				var h = 0;
				for(var i = $('.dms-message-info').length -1; i > $('.dms-message-info').length - DMS_COMMENT_NUM - 1; i--){
					h += $('.dms-message-info').eq(i).height();
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

	this.publishHtml = function(dmsData){
		var mode = /^([0-9]+)$/;
		if(mode.test(dmsData.time)){
			dmsData.time = dmsCommonHandle.formatDate(dmsData.time);
		}
		var html = '\
			<div class="dms-message-info">\
				<div class="dms-user-info">\
					<div class="dms-ava"><img src="'+decodeURI(dmsData.ava)+'" /></div>\
				</div>\
				<div class="dms-content">\
					<div class="dms-header"><span class="dms-nick">'+decodeURI(dmsData.nick)+'</span><span class="dms-time">'+dmsData.time+'</span></div>\
					<div>'+dmsLayoutHandle.replaceEm(dmsCommonHandle.htmlspecialchars(decodeURI(dmsData.content)))+'</div>\
				</div>\
				<div class="dms-cb"></div>\
			</div>\
			';
		$('#dmsMessage').append(html);
		var h = 0;
		for(var i = 0; i < DMS_COMMENT_NUM; i++){
			h += $('.dms-message-info').eq(i).height();
		}
		var b = $('#dmsMessage').height() - $('.dms-message-container')[0].scrollTop;
		if(typeof(dmsData.uid) != 'undefined' || b <= h){
			$('.dms-message-container').scrollTop($('#dmsMessage').height());
		}
	}
	
	this.publish = function(){
		var content = document.getElementById("aodianyun-dms-text").value;
		if(content == ''){
			alert('请输入内容');
			return;
		}
		if(content.length > 100){
			alert('内容不能大于100个字！');
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
			time:Math.ceil(new Date().getTime()/1000),

		};
		dmsLayoutHandle.publishHtml(dmsData);
		document.getElementById("aodianyun-dms-text").value = '';
		dmsCommonHandle.wxPublish(dmsData);
	}

	this.systempublishHtml = function(dmsData){
		var content ="主播收到"+dmsData.nick+"送的"+dmsData.num+"个"+dmsData.giftname;
		var ava = DMS_CDN_SITE + 'layout/wsp/entertainment/def_ava_admin.png';
		var html = '\
					<div class="dms-message-info">\
						<div class="dms-user-info">\
							<div class="dms-ava"><img src="'+ava+'" /></div>\
						</div>\
						<div class="dms-content">\
							<div class="dms-header"><span class="dms-nick dms-systemrole">系统消息</span><span class="dms-time">'+dmsCommonHandle.formatDate()+'</span></div>\
							<div class="dms-systemrole">'+content+
							'<img align="absmiddle" class="dms-giftshow" src="'+dmsData.gifturl+'"/></div>\
						</div>\
						<div class="dms-cb"></div>\
					</div>\
					';
		$('#dmsMessage').append(html);
		var h = 0;
		for(var i = 0; i < DMS_COMMENT_NUM; i++){
			h += $('.dms-message-info').eq(i).height();
		}
		var b = $('#dmsMessage').height() - $('.dms-message-container')[0].scrollTop;
		if(typeof(dmsData.uid) != 'undefined' || b <= h){
			$('.dms-message-container').scrollTop($('#dmsMessage').height());
		}
	}
	
	this.replaceEm = function (str){
		str = str.replace(/\</g,'&lt;');
		str = str.replace(/\>/g,'&gt;');
		str = str.replace(/\n/g,'<br/>');
		str = str.replace(/\[em_([0-9]*)\]/g,'<img src="'+DMS_CDN_SITE + 'layout/wsp/entertainment/arclist/$1.gif" border="0" />');
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
	alert('聊天服务加载失败！请刷新页面重试。');
})
ROP.On("publish_data",function(data,topic){
	
	if(window.console){
		console.log(data,topic);
	}

	var dmsData = $.parseJSON(data);

	if(typeof(dmsConfig.uid) != 'undefined' && dmsData.uid == dmsConfig.uid && topic == dmsConfig.topic){
		return;
	}

	var num = $('#dmsMessage .dms-message-info').length;
	if(num >= 100){
		$('#dmsMessage .dms-message-info').eq((num-1)).remove();
	}
	if(topic == dmsConfig.topic){		
		dmsLayoutHandle.publishHtml(dmsData);
	}
	else{
		if(dmsData.cmd=="gift"){
			//{"cmd":"gift","nick":"xx","num":"1","gifturl":"\/wsp\/assets\/images\/1.png","giftname":"\u73ab\u7470"} 
			dmsLayoutHandle.systempublishHtml(dmsData);			
		}
		else if(dmsData.cmd == 'nums'){
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
						window.location = '../entertainment/'+dmsConfig.channelId;
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
				if(dmsData.state == 1){
					var videoHtml = '\
						<div id="playBtn" class="play-btn"></div>\
						<img id="carousel" src="'+dmsConfig.carousel+'" />\
						<video id="lss" controls preload="auto" webkit-playsinline>\
							<source src="'+dmsConfig.liveUrl+'" type="application/x-mpegURL"/>\
						</video>\
					';
					$('#lssPlayBox').html(videoHtml);
					$('#lssPlayBox').attr('onClick','play();');
					//$('#liveState').removeClass('unliving');
					//$('#liveState').addClass('living');
					str = '直播开始啦！';
				}
				else{
					if(dmsConfig.videoUrl == ''){
						var videoHtml = '<img id="carousel" src="'+dmsConfig.carousel+'" />';
						$('#lssPlayBox').html(videoHtml);
						$('#lssPlayBox').attr('onClick','');
					}
					else{
						var videoHtml = '\
							<div id="playBtn" class="play-btn"></div>\
							<img id="carousel" src="'+dmsConfig.videoCarousel+'" />\
							<video id="lss" controls preload="auto" webkit-playsinline>\
								<source src="'+dmsConfig.videoUrl+'" type="application/x-mpegURL"/>\
							</video>\
						';
						$('#lssPlayBox').html(videoHtml);
						$('.ui-slider-item').eq(0).find('img').addClass('active');
						$('#lssPlayBox').attr('onClick','play();');
					}
					//$('#liveState').removeClass('living');
					//$('#liveState').addClass('unliving');
					str = '直播结束！';
				}
			}
			else if(dmsData.cmd == 'chatOpt'){
				dmsConfig.chatOpt = dmsData.state;
				if(dmsData.state == 1){
					str = '管理员开启了聊天';
				}
				else{
					str = '管理员关闭了聊天';
				}
			}
			if(str == ''){
				return;
			}
			var html = '\
				<div class="dms-message-info">\
					<div class="dms-user-info">\
						<div class="dms-ava"><img src="'+DMS_CDN_SITE+'layout/wsp/entertainment/speaker.png" /></div>\
					</div>\
					<div class="dms-content">\
						<div class="dms-header"><span class="dms-nick"><font color="red">系统消息</font></span><span class="dms-time">'+dmsCommonHandle.formatDate()+'</span></div>\
						<div>'+str+'</div>\
					</div>\
					<div class="dms-cb"></div>\
				</div>\
				';
			$('#dmsMessage').append(html);
			$('.dms-message-container').scrollTop($('#dmsMessage').height());
		}
	}
})
ROP.On("losed",function(){
	alert('您已和聊天服务失去了连接！请刷新页面重试。');
})

var dmsLayoutHandle = new dmsLayoutInit();
dmsLayoutHandle.init();
dmsLayoutHandle.cSSInterval = setInterval("dmsLayoutHandle.cssLoad()",100);