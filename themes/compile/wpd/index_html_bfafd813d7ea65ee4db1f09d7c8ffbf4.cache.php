<?php if (!class_exists('CTemplate')) die('Access Denied');?>
<!DOCTYPE html>
<html>

<!--template compile at 2015-03-31 17:43:10-->


<!--template compile at 2015-12-28 15:02:52-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="width=device-width, initial-scale=1" name="viewport">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<link type="text/css" rel="stylesheet" href="<?php echo WPD_ASSETS_PATH;?>css/style.css">
<link type="text/css" rel="stylesheet" href="<?php echo WPD_ASSETS_PATH;?>css/index.css">
<title><?php if(empty($serviceInfo['pageTitle'])) { ?>频道管理<?php } else { ?><?php echo $serviceInfo['pageTitle'];?><?php } ?></title>
</head>
<body>
<script type="text/javascript" src="http://cdn.aodianyun.com/static/jquery/jquery-1.7.2.min.js"></script>
<script language="javascript">
var iheight=window.screen.height;	
var link = document.createElement('link');
link.id = 'INDEXCss';
link.type = 'text/css';
link.rel = 'stylesheet';

var stylelink = document.createElement('link');
stylelink.id = 'STYLECss';
stylelink.type = 'text/css';
stylelink.rel = 'stylesheet';
if(iheight>=900){
	stylelink.href ='<?php echo WPD_ASSETS_PATH;?>css/style_1920.css';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(stylelink);
	link.href ='<?php echo WPD_ASSETS_PATH;?>css/index_1920.css';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(link);
}
else if(iheight>=600&&iheight<800){
	stylelink.href ='<?php echo WPD_ASSETS_PATH;?>css/style_1280.css';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(stylelink);
	link.href ='<?php echo WPD_ASSETS_PATH;?>css/index_1280.css';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(link);
}
</script>
<?php include(CTemplate::getInstance()->getfile('header.html')); ?>
<div class="wapper">
  <div class="channel-info">
    <p><font>频道：</font><?php echo $channelInfo['title'];?><span class="btn btn-red share-btn" id="shareChannel">分享</span></p>
    <p class="activity"><font>当前活动：</font><?php if(empty($activePartiesInfo) || $activePartiesInfo['state'] == 3) { ?><span id="partysName">无活动</span><span class="btn btn-red create-btn" id="partiesBtn">创建活动</span><?php } else { ?><span id="partysName"><?php echo $activePartiesInfo['title'];?></span><span class="btn btn-gary close-btn" id="partiesBtn">关闭活动</span><span class="btn btn-red share-btn" id="shareParty">分享</span><?php } ?></p>
  </div>
  <div class="lcps">
    <div class="tab-title">
      <h1>直播发布</h1>
    </div>
  	<div class="player-box">
  		<div class="player-volume-wapper">
  			<div class="player-volume-box">
				<div class="volume-box" id="volumeBox"></div>
			    <div class="volume-btn" id="volumeBtn"></div>
				<div class="volume-icon" id="banVolume"></div>
			</div>
            <?php if($activePartiesInfo["living"] == 1) { ?>
			<span class="btn btn-red live-state">LIVE</span>
            <?php } else { ?>
            <span class="btn btn-gary live-state">LIVE</span>
            <?php } ?>
  		</div>
  		<div class="lss-box">
  			<div id="lss"></div>
  		</div>
  		<div class="change-video-source">
         <?php if($activePartiesInfo["living"] == 1) { ?>
  			<span class="btn-big btn-red cut-mic">↓切麦</span>
  		<?php } else { ?>
			<span class="btn-big btn-gary cut-mic">↓切麦</span>
  		<?php } ?>
  		</div>
  		<div class="clearfix"></div>
  	</div>
  	<div class="panes-wapper">
  		<div class="tabPanel">
			<ul>
			    <li publishType="0" class="hit">本地发布</li>
			    <li publishType="1">手机发布</li>
			    <?php if($serviceInfo['showExternTab'] == 1) { ?>
			    <li publishType="2">外部发布</li>
			    <?php } ?>
			</ul>
		</div>
		<div class="panes">
			<div class="pane" id="publishVideoBox" style="display:block;">
				<p>
					视频设备：
					<select id="camList" onChange="lssSetCam(this.value);">
						<option value="">没有检测到任何视频设备</option>
					</select>
				</p>
				<p>
					音频设备：
					<select id="micList" onChange="lssSetMic(this.value);">
						<option value="">没有检测到任何音频设备</option>
					</select>
				</p>
				<p>
					视频尺寸：
					<select id="videoSize" onChange="lssSetCameraMode();">
						<option value="320x240">320x240</option>
						<option value="320x180">320x180</option>
						<option value="640x480">640x480</option>
						<option value="640x360">640x360</option>
					</select>
				</p>
				<p style="margin-left:250px;">
                	<?php if($activePartiesInfo["living"] == 1) { ?>
                    <span id="lssOperateBtn" class="btn-big btn-gary">上麦</span>
                    <?php } else { ?>
                	<span id="lssOperateBtn" class="btn-big btn-red">上麦</span>
                    <?php } ?>
                </p>
				<div class="publish-volume-box">
					<div class="volume-box" id="publishVolumeBox"></div>
				    <div class="volume-btn" id="publishVolumeBtn"></div>
					<div class="volume-icon" id="publishBanVolume"></div>
				</div>
			</div>
			<div class="pane" id="phonePublishConfigBox"></div>
			<?php if($serviceInfo['showExternTab'] == 1) { ?>
			<div class="pane" id="outPublishBox"></div>
			<?php } ?>
        </div>
  	</div>
  </div>
  <div class="dms" id="dms"></div>
  <div class="clearfix"></div>
</div>

<script type="text/javascript" src="<?php echo WPD_ASSETS_PATH;?>js/dlg.js"></script>
<script type="text/javascript" src="http://cdn.aodianyun.com/lss/publish.js"></script>
<script type="text/javascript">
var lsswidth=320;
var lssheight=240;
var screenheight=window.screen.height;

if (screenheight>=900) {
	var lsswidth=400;
	var lssheight=300;
}
else if (screenheight>=600&&screenheight<800) {
	var lsswidth=240;
	var lssheight=180;
}

var channelId = "<?php echo $channelInfo['id'];?>";
var LSS_PLAYER_STATUS = '<?php echo $activePartiesInfo["living"];?>';
var LSS_PUBLISH_STATUS = false;
var lss = new aodianLss({
  container:'lss',//播放器容器ID，必要参数
  url:'<?php echo $rtmpPublishUrl;?>',//控制台开通的APP rtmp地址，必要参数
  width: lsswidth,//播放器宽度，可用数字、百分比等
  height: lssheight,//播放器高度，可用数字、百分比等
  autoconnect: true//加载完毕后是否初始化连接，默认为true
});
var lssInterval = setInterval("getLssPublishConfig()",1000);
var volumeNum = 80;
var oldVoulmeNum = 0;
var publishVolumeNum = 80;
var publishOldVoulmeNum = 0;
var lssApp = '<?php echo $lssApp;?>';
var lssStream = '<?php echo $lssStream;?>';
var rtmpAddr = '<?php echo $rtmpAddr;?>';
var rtmpPublishAddr = '<?php echo $rtmpPublishAddr;?>';
var DMS_SITE = '<?php echo SYSTEM_HOST;?>';

var dmsConfig = {
  container:"dms",
  channelId:channelId,
  partyId:"<?php echo $activePartiesInfo['partyId'];?>",
  layout:"playConsole",
  dmsAppKey:"<?php echo $serviceInfo['dmsAppKey'];?>",
  dmsPubKey:"<?php echo $serviceInfo['dmsPubKey'];?>",
  dmsSubKey:"<?php echo $serviceInfo['dmsSubKey'];?>",
  wxAppid:"<?php echo $serviceInfo['wxAppid'];?>",
  chatOpt:"<?php echo $activePartiesInfo['chatOpt'];?>",
  topic:"<?php echo $activePartiesInfo['topic'];?>",
  controlTopic:"<?php echo $activePartiesInfo['controlTopic'];?>",
  uid:"<?php echo $userInfo['openid'];?>",
  nick:"<?php echo $userInfo['nick'];?>",
  ava:"<?php echo $userInfo['ava'];?>",
  blackList:$.parseJSON('<?php echo $blackList;?>'),
  gapsList:$.parseJSON('<?php echo $gapsList;?>'),
  pvNum:"<?php echo $activePartiesInfo['userNum'];?>",
  praiseNum:"<?php echo $activePartiesInfo['praiseNum'];?>",
  msgNum:"<?php echo $activePartiesInfo['msgNum'];?>",
  shareNum:"<?php echo $activePartiesInfo['shareNum'];?>"
};
(function() {
  var dms = document.createElement('script');
  dms.type = 'text/javascript';
  dms.async = true;
  dms.src = DMS_SITE + '/dms/wsp.js';
  dms.charset = 'UTF-8';
  (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dms);
})();

$(function(){
    $('#volumeBox').on('click',function(e){
        var volumeOffset = $(this).offset();
        var volumeHeight = $(this).height();
        var relativeY = (e.pageY - volumeOffset.top);
        volumeNum = Math.ceil(((volumeHeight-relativeY)/volumeHeight)*100);
        $('#volumeBtn').css('top',relativeY+'px');
		lssSetVolume(volumeNum);
    })
    $('#volumeBtn').on('mousedown',function(){
    	$(this).on('mousemove',function(e){
            var volumeOffset = $('#volumeBox').offset();
            var volumeHeight = $('#volumeBox').height();
            var relativeY = (e.pageY - volumeOffset.top);
            volumeNum = Math.ceil(((volumeHeight-relativeY)/volumeHeight)*100);
            if(volumeNum < 0 || volumeNum > 100){
                return;
            }
            $('#volumeBtn').css('top',relativeY+'px');
    	})
        $(this).on('mouseout',function(){
            $(this).off('mousemove');
			lssSetVolume(volumeNum);
        })
    	$(this).on('mouseup',function(){
    		$(this).off('mousemove');
			lssSetVolume(volumeNum);
    	})
    })
    $('#banVolume').toggle(
    	function(){
    		$(this).removeClass('volume-icon');
    		$(this).addClass('volume-no-icon');
    		oldVoulmeNum = volumeNum;
    		volumeNum = 0;
    		var relativeY = $('#volumeBox').height() - volumeNum;
    		$('#volumeBtn').css('top',relativeY+'px');
			lssSetVolume(volumeNum);
    	},
    	function(){
    		$(this).addClass('volume-icon');
    		$(this).removeClass('volume-no-icon');
    		volumeNum = oldVoulmeNum;
    		var relativeY = $('#volumeBox').height() - volumeNum;
    		$('#volumeBtn').css('top',relativeY+'px');
			lssSetVolume(volumeNum);
    	}
    )
    $('#publishVolumeBox').on('click',function(e){
        var volumeOffset = $(this).offset();
        var volumeHeight = $(this).height();
        var relativeY = (e.pageY - volumeOffset.top);
        publishVolumeNum = Math.ceil(((volumeHeight-relativeY)/volumeHeight)*100);
        $('#publishVolumeBtn').css('top',relativeY+'px');
		lssSetVolume(publishVolumeNum);
    })
    $('#publishVolumeBtn').on('mousedown',function(){
    	$(this).on('mousemove',function(e){
            var volumeOffset = $('#publishVolumeBox').offset();
            var volumeHeight = $('#publishVolumeBox').height();
            var relativeY = (e.pageY - volumeOffset.top);
            publishVolumeNum = Math.ceil(((volumeHeight-relativeY)/volumeHeight)*100);
            if(publishVolumeNum < 0 || publishVolumeNum > 100){
                return;
            }
            $('#publishVolumeBtn').css('top',relativeY+'px');
    	})
        $(this).on('mouseout',function(){
            $(this).off('mousemove');
			lssSetVolume(publishVolumeNum);
        })
    	$(this).on('mouseup',function(){
    		$(this).off('mousemove');
			lssSetVolume(publishVolumeNum);
    	})
    })
    $('#publishBanVolume').toggle(
    	function(){
    		$(this).removeClass('volume-icon');
    		$(this).addClass('volume-no-icon');
    		publishOldVoulmeNum = publishVolumeNum;
    		publishVolumeNum = 0;
    		var relativeY = $('#publishVolumeBox').height() - publishVolumeNum;
    		$('#publishVolumeBtn').css('top',relativeY+'px');
			lssSetVolume(publishVolumeNum);
    	},
    	function(){
    		$(this).addClass('volume-icon');
    		$(this).removeClass('volume-no-icon');
    		publishVolumeNum = publishOldVoulmeNum;
    		var relativeY = $('#publishVolumeBox').height() - publishVolumeNum;
    		$('#publishVolumeBtn').css('top',relativeY+'px');
			lssSetVolume(publishVolumeNum);
    	}
    )
	$('.tabPanel ul li').live('click',function(){
		var publishType = $(this).attr('publishType');
		if(publishType != 0 && LSS_PUBLISH_STATUS == true){
			dlgMsg(2,'提示','抱歉，您正在上麦，不能进行外部发布！');
			return;
		}
		if(publishType != 0 && LSS_PLAYER_STATUS == 1){
			dlgMsg(2,'提示','抱歉，已有人上麦，请切麦后再操作！');
			return;
		}
		$(this).addClass('hit').siblings().removeClass('hit');
		$('.panes>.pane:eq('+$(this).index()+')').show().siblings('.pane').hide();
		if(publishType == 1){
			$('#phonePublishConfigBox').html('<iframe scrolling="no" frameborder="0" width="100%" height="200px" src="index.php?r=console/phonePublishConfig&channelId='+channelId+'&rtmpPublishUrl=<?php echo urlencode($rtmpPublishUrl);;?>&rtmpUrl=<?php echo urlencode($rtmpUrl);;?>"></iframe>');
		}
		else if(publishType == 2){
			$('#outPublishBox').html('<iframe scrolling="no" frameborder="0" width="100%" height="200px" src="index.php?r=console/outPublishVideo&channelId='+channelId+'&rtmpPublishUrl=<?php echo urlencode($rtmpPublishUrl);;?>&rtmpUrl=<?php echo urlencode($rtmpUrl);;?>"></iframe>');
		}
	})
	$('.create-btn').live('click',function(){
		createParties();
	});
	$('.close-btn').live('click',function(){
		closeParties();
	});
	$('#shareChannel').live('click',function(){
		var url = '<?php echo $channelUrl;?>';
		var title = '<?php echo urlencode($channelInfo["title"]);;?>';
		var desc = '<?php echo urlencode($channelInfo["title"]);;?>';
		var img = '<?php echo urlencode($channelInfo["surfaceUrl"]);;?>';
		var html = '<iframe scrolling="no" frameborder="0" width="600px" height="300px" src="index.php?r=console/share&channelId='+channelId+'&type=channel&url='+url+'&title='+title+'&desc='+desc+'&img='+img+'"></iframe>';
		dlg(670,450,'频道分享',html,function(){
			dlgClose();
		});
	});
	$('#shareParty').live('click',function(){
		var url = '<?php echo $partyUrl;?>';
		var title = '<?php echo urlencode($channelInfo["title"]);;?>';
		var desc = '<?php echo urlencode($channelInfo["title"]);;?>';
		var img = '<?php echo urlencode($channelInfo["surfaceUrl"]);;?>';
		var html = '<iframe scrolling="no" frameborder="0" width="600px" height="300px" src="index.php?r=console/share&channelId='+channelId+'&type=party&url='+url+'&title='+title+'&desc='+desc+'&img='+img+'"></iframe>';
		dlg(670,450,'活动分享',html,function(){
			dlgClose();
		});
	});
	$('.cut-mic').on('click',function(){
		cutMic();
	});
	$('#lssOperateBtn').on('click',function(){
		if(LSS_PLAYER_STATUS == 0 && LSS_PUBLISH_STATUS == false){
			lssPublish();
		}
		if(LSS_PLAYER_STATUS == 1 && LSS_PUBLISH_STATUS == true){
			lssCloseConnect();
		}
	});
})

function cutMic(){
	if(LSS_PLAYER_STATUS != 1){
		return;
	}
	dlgWait();
	$.ajax({
		type:'POST',
		url:'index.php?r=console/cutMic&channelId='+channelId,
		data:{},
		dataType:'JSON',
		async:false,
		success:function(data) {
			dlgWaitClose();
			if(data.Flag == 100){
				dlgMsg(1,'提示','操作成功');
				LSS_PLAYER_STATUS = 0;
				LSS_PUBLISH_STATUS = false;
				lssCloseConnect();
			}
			else{
				dlgMsg(3,'提示',data.FlagString);
			}
		}
	});
}

function changeLiveState(){
	if(LSS_PLAYER_STATUS == 1){
		$('.live-state').removeClass('btn-gary');
		$('.live-state').addClass('btn-red');
		$('.cut-mic').removeClass('btn-gary');
		$('.cut-mic').addClass('btn-red');
		if(LSS_PUBLISH_STATUS == false){
			$('#lssOperateBtn').addClass('btn-gary');
			$('#lssOperateBtn').removeClass('btn-red');
			$('#lssOperateBtn').removeClass('btn-a');
			lssPlay();
		}
	}
	else{
		$('.live-state').addClass('btn-gary');
		$('.live-state').removeClass('btn-red');
		$('.cut-mic').addClass('btn-gary');
		$('.cut-mic').removeClass('btn-red');
		lssCloseConnect();
	}
}

function getLssPublishConfig(){
	if(typeof(lss.checkPlayerReady) == 'function' && lss.checkPlayerReady() == true){
		var camArr = lss.getCam();
		if(typeof(camArr[0]) != 'undefined'){
			$('#camList').html('');
			for(var i in camArr){
				var item = new Option(camArr[i],i);
				document.getElementById('camList').options.add(item);
			}
		}
		var micArr = lss.getMic();
		if(typeof(micArr[0]) != 'undefined'){
			$('#micList').html('');
			for(var i in micArr){
				var item = new Option(micArr[i],i);
				document.getElementById('micList').options.add(item);
			}
		}
		if(typeof(LSS_PLAYER_STATUS) !='undefined' && LSS_PLAYER_STATUS == 1){
			lssPlay();
		}
		clearInterval(lssInterval);
	}
}

function lssPlay(){
	lss.closeConnect();
	lss.initConnect(rtmpAddr,lssApp,lssStream);
	var playConf = {
		volume:volumeNum,
		isMute:false
	};
	lss.startPlay(playConf);
}

function lssPublish(){
	if(typeof(LSS_PLAYER_STATUS) !='undefined' && LSS_PLAYER_STATUS == 1){
		dlgMsg(2,'提示','您已在外部进行视频发布，请暂停发布后再进行上麦操作！');
		return;
	}
	if(typeof(lss.checkPlayerReady) != 'function'){
		dlgMsg(2,'提示','视频组件正在加载中，请稍后再试！');
		return;
	}
	if(typeof(lss.checkPlayerReady) == 'function' && lss.checkPlayerReady() == false){
		dlgMsg(2,'提示','视频组件正在加载中，请稍后再试！');
		return;
	}
	lss.closeConnect();
	lss.initConnect(rtmpPublishAddr,lssApp,lssStream);
	
	var videoSize = $('#videoSize').val();
	videoSize = videoSize.split('x');
	var videoWidth = videoSize[0];
	var videoHeight = videoSize[1];
	lss.setCameraMode(videoWidth,videoHeight,25);
	
	var camID = $('#camList').val() == '' ? 0 : $('#camList').val();
	var micID = $('#micList').val() == '' ? 0 : $('#micList').val();
	var publishConf = {
		videoWidth:videoWidth,
		videoHeight:videoHeight,
		micID:micID,
		camID:camID,
		audioKBitrate:96,
		audioSamplerate:44100,
		videoFPS:25,
		keyFrameInterval:75,
		videoKBitrate:360,
		videoQuality:100,
		volume:volumeNum,
		isUseCam:true,
		isUseMic:true,
		isMute:false
	};
	lss.startPublish(publishConf);
	$('#lssOperateBtn').removeClass('btn-red');
	$('#lssOperateBtn').addClass('btn-a');
	$('#lssOperateBtn').html('下麦');
	LSS_PUBLISH_STATUS = true;
}

function lssCloseConnect(){
	if(LSS_PLAYER_STATUS == 1 && LSS_PUBLISH_STATUS == false){
		dlgMsg(2,'提示','您已在外部进行视频发布，请暂停发布后再进行上麦操作！');
		return;
	}
	if(typeof(lss.checkPlayerReady) != 'function'){
		dlgMsg(2,'提示','视频组件正在加载中，请稍后再试！');
		return;
	}
	if(typeof(lss.checkPlayerReady) == 'function' && lss.checkPlayerReady() == false){
		dlgMsg(2,'提示','视频组件正在加载中，请稍后再试！');
		return;
	}
	lss.closeConnect();
	$('#lssOperateBtn').removeClass('btn-gary');
	$('#lssOperateBtn').removeClass('btn-a');
	$('#lssOperateBtn').addClass('btn-red');
	$('#lssOperateBtn').html('上麦');
	LSS_PUBLISH_STATUS = false;
}

function lssSetCam(camID){
	if(camID == ''){
		return;
	}
	lss.setCam(camID);
}

function lssSetMic(micID){
	if(micID == ''){
		return;
	}
	lss.setMic(micID);
}

function lssSetCameraMode(){
	var videoSize = $('#videoSize').val();
	videoSize = videoSize.split('x');
	var videoWidth = videoSize[0];
	var videoHeight = videoSize[1];
	lss.setCameraMode(videoWidth,videoHeight,25);
}

function lssSetVolume(volume){
	lss.setVolume(volume);
}

var activePartiesId = '<?php echo empty($activePartiesInfo) || $activePartiesInfo["state"] != 1 ? 0 : $activePartiesInfo["partyId"];?>';

function createParties(){
	var html = '\
		<iframe name="createParty" src="index.php?r=party/create&channelId='+channelId+'" frameborder="0" width="550" height="250"></iframe>\
	';
	dlg(600,450,'创建活动',html,function(){
		var title = $("#title", window.frames["createParty"].document).val();
		var sTime = $("#sTime", window.frames["createParty"].document).val();
		var eTime = $("#eTime", window.frames["createParty"].document).val();
		var pic = $("#pic", window.frames["createParty"].document).val();

		if(title == ''){
			dlgMsg(2,'提示','请输入活动主题');
			return;
		}
		if(title.length > 20){
			dlgMsg(2,'提示','活动主题不能超过20个字');
			return;
		}
		if(sTime == ''){
			dlgMsg(2,'提示','请选择开始时间');
			return;
		}
		if(eTime == ''){
			dlgMsg(2,'提示','请选择结束时间');
			return;
		}
		if(pic == ''){
			dlgMsg(2,'提示','请上传封面图');
			return;
		}
		dlgWait();
		$("#createPartyForm", window.frames["createParty"].document).submit();
		return;
	});
}

function closeParties(){
	if(activePartiesId == '0'){
		dlgMsg(2,'提示','当前没有正在进行的活动！');
		return;
	}
	var html = '<p style="font-size:20px;ont-weight:blod;">确定要关闭此活动吗？</p>';
	dlg(400,200,'关闭活动',html,function(){
		dlgWait();
		$.ajax({
			type:'POST',
			url:'index.php?r=party/close&channelId='+channelId,
			data:{id:activePartiesId},
			dataType:'JSON',
			async:false,
			success:function(data) {
				dlgWaitClose();
				if(data.Flag == 100){
					dlgClose();
					dlgMsg(1,'提示','活动关闭成功！');
					setTimeout(function(){window.location.reload();},1300);
				}
				else{
					dlgMsg(3,'提示',data.FlagString);
				}
			}
		});
	});
}
</script>
<?php include(CTemplate::getInstance()->getfile('footer.html')); ?>
</body>
</html>