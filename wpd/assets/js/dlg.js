var dlgCallBack;
function dlg(width,height,title,content,callback){
	var w1 = $(document).width();
	var h1 = $(document).height();
	var w2 = $(window).width();
	var h2 = $(window).height();
	var w3 = Math.ceil(w2/2) - Math.ceil(width/2) + $(document).scrollLeft();
	var h3 = Math.ceil(h2/2) - Math.ceil(height/2) + $(document).scrollTop();
	var html = '\
			<div id="dlgMask" class="dlg-mask" style="width:'+w1+'px;height:'+h1+'px;"></div>\
			<div id="dlg" class="dlg" style="width:'+width+'px;height:'+height+'px; left:'+w3+'px; top:'+h3+'px;">\
				<div class="dlg-title-box">\
			    	<div class="dlg-title">'+title+'</div>\
			        <div class="dlg-close" onClick="dlgClose()">x</div>\
			        <div class="clear"></div>\
			    </div>\
			    <div class="dlg-body">'+content+'</div>\
			    <div class="dlg-btn-box">\
			    	<span class="btn-big btn-red" onClick="dlgSubmit();">确定</span>\
			        <span class="btn-big btn-a ml30" onClick="dlgClose();">取消</span>\
			        <div class="clear"></div>\
			    </div>\
			</div>\
		';
	$('body').append(html);
	$('body').addClass('dlg-lock');
	dlgCallBack = callback;
}
function dlgClose(){
	$('#dlg').remove();
	$('#dlgMask').remove();
	$('body').removeClass('dlg-lock');
	dlgCallBack = '';
}
function dlgSubmit(){
	if(typeof(dlgCallBack) == 'function'){
		dlgCallBack();
	}
}
function dlgMsg(type,title,content,callback,width,height){
	var js = document.scripts || document.getElementsByTagName("script");
    var path;
    for (var i = js.length; i > 0; i--) {
        if (js[i - 1].src.indexOf("dlg.js") > -1) {
            path = js[i - 1].src.substring(0, js[i - 1].src.lastIndexOf("/") - 2);
        }
    }
	width = typeof(width) == 'undefined' ? 400 : width;
	height = typeof(height) == 'undefined' ? 180 : height;
	var w1 = $(document).width();
	var h1 = $(document).height();
	var w2 = $(window).width();
	var h2 = $(window).height();
	var w3 = Math.ceil(w2/2) - Math.ceil(width/2) + $(document).scrollLeft();
	var h3 = Math.ceil(h2/2) - Math.ceil(height/2) + $(document).scrollTop();
	if(type == 1){
		var icon = 'icon_success.png';
		var msgClass = 'dlg-msg-success';
	}
	else if(type == 2){
		var icon = 'icon_warning.png';
		var msgClass = 'dlg-msg-warning';
	}
	else if(type == 3){
		var icon = 'icon_error.png';
		var msgClass = 'dlg-msg-error';
	}
	else{
		var icon = 'icon_success.png';
		var msgClass = 'dlg-msg-success';
	}
	icon = path + 'img/' + icon;
	var dlgStatus = false;
	if($('.dlg').length > 0){
		dlgStatus = true;
	}
	var html = '\
		<div id="dlgMsgMask" class="dlg-msg-mask" style="width:'+w1+'px;height:'+h1+'px;"></div>\
		<div id="dlgMsg" class="dlg dlg-msg" style="width:'+width+'px;height:'+height+'px; left:'+w3+'px; top:'+h3+'px;">\
			<div class="dlg-title-box">\
		    	<div class="dlg-title"><b>'+title+'</b></div>\
		        <div class="clear"></div>\
		    </div>\
		    <div class="dlg-msg-body">\
			    <table border="0" cellpadding="0" cellspacing="0">\
					<tr>\
						<td align="right" class="dlg-msg-type-icon"><img src="'+icon+'"></td>\
						<td align="left" class="'+msgClass+'">'+content+'</td>\
					</tr>\
				</table>\
		   	</div>\
		</div>\
	';
	$('body').append(html);
	if(dlgStatus == false){
		$('body').addClass('dlg-lock');
	}
	setTimeout(
		function(){
			$('#dlgMsg').fadeOut(300,function(){
				$('#dlgMsg').remove();
				if(typeof(callback) == 'function'){
					callback();
				}
			});
			$('#dlgMsgMask').remove();
			if(dlgStatus == false){
				$('body').removeClass('dlg-lock');
			}
		},1200
	);
}

function dlgWait(title,content,callback){
	var js = document.scripts || document.getElementsByTagName("script");
    var path;
    for (var i = js.length; i > 0; i--) {
        if (js[i - 1].src.indexOf("dlg.js") > -1) {
            path = js[i - 1].src.substring(0, js[i - 1].src.lastIndexOf("/") - 2);
        }
    }

	title = typeof(title) == 'undefined' ? '提示' : '';
	content = typeof(content) == 'undefined' ? '操作中，请稍后...' : '';
	var width = 300;
	var height = 150;
	var w1 = $(document).width();
	var h1 = $(document).height();
	var w2 = $(window).width();
	var h2 = $(window).height();
	var w3 = Math.ceil(w2/2) - Math.ceil(width/2) + $(document).scrollLeft();
	var h3 = Math.ceil(h2/2) - Math.ceil(height/2) + $(document).scrollTop();
	var dlgStatus = false;

	if($('.dlg').length > 0){
		dlgStatus = true;
	}
	var html = '\
		<div id="dlgWaitMask" class="dlg-wait-mask" style="width:'+w1+'px;height:'+h1+'px;"></div>\
		<div id="dlgWait" class="dlg dlg-wait" style="width:'+width+'px;height:'+height+'px; left:'+w3+'px; top:'+h3+'px;">\
			<div class="dlg-title-box">\
		    	<div class="dlg-title"><b>'+title+'</b></div>\
		        <div class="clear"></div>\
		    </div>\
		    <div class="dlg-wait-body">\
				<img src="'+path+'img/loading.gif">'+content+'\
		   	</div>\
		</div>\
	';
	$('body').append(html);
	if(dlgStatus == false){
		$('body').addClass('dlg-lock');
	}
	if(typeof(callback) == 'function'){
		callback();
	}
}
function dlgWaitClose(){
	$('#dlgWaitMask').remove();
	$('#dlgWait').remove();
	if($('.dlg').length <= 1){
		$('body').removeClass('dlg-lock');
	}
}