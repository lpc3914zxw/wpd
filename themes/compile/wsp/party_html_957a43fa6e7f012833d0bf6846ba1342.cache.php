<?php if (!class_exists('CTemplate')) die('Access Denied');?>
<html>

<!--template compile at 2015-12-28 15:11:11-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" type="text/css" href="<?php echo WSP_ASSETS_PATH;?>css/slider.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WSP_ASSETS_PATH;?>css/livestreamlayout.css"/>
<title><?php echo $partyInfo['title'];?></title>
</head>
<body>
<style>
html,body{
	width:100%;
	height:100%;
	overflow:hidden;
}
.ui-slider{
	height:3rem;
}
.ui-slider .ui-slider-wheel{
	margin-top:0.25rem;
	margin-bottom:0.25rem;
}
.ui-slider .ui-slider-item{
	width:4rem;
	height:2.5rem;
}
.ui-slider .ui-slider-item img{
	width:96%;
	height:2.3rem;
}
.ui-slider .ui-slider-item .active{
	border:0.1rem solid #ff6600;
}
.channel-info{
	background:#ebebeb;
	margin-bottom:0;
	color:#777;
}
.channel-info .back{
	float:left;
	margin-left:0.7rem;
	margin-top:0.1rem;
	padding-right:0.8rem;
	height:2.2rem;
	border-right:1px solid #CCC;
	z-index:9;
}
.channel-info .back img{
	height:1.4rem;
	margin-top:0.45rem;
}
#dms{
	width:100%;
	position:relative;
	background:#ffffff;
	z-index:9;
}
#dms .loading{
  width: 100px;
  height: 100px;
  position: absolute;
  top: 50%;
  margin-top: -50px;
  left: 50%;
  margin-left: -50px;
  display: none;
}
#lss{
	width:100%;
	height:100%;
	padding:0;
	margin:0;
	z-index:1
}
</style>
<?php if($partyInfo['living'] == 1) { ?>
<div class="surface-container" id="lssPlayBox" onClick="play();">
	<div id="playBtn" class="play-btn"></div>
	<img id="carousel" src="<?php echo $partyInfo['carousel'];?>" />
    <video id="lss" controls preload="auto" webkit-playsinline>
        <source src="<?php echo $videoUrl;?>" type='application/x-mpegURL'/>
    </video>
</div>
<?php } else { ?>
    <?php if(!empty($partyInfo['vodList'])) { ?>
	<div class="surface-container" id="lssPlayBox" onClick="play();">
        <div id="playBtn" class="play-btn"></div>
        <img id="carousel" src="<?php echo $partyInfo['vodList']['0']['surfaceUrl'];?>/0/0" />
        <video id="lss" controls preload="auto" webkit-playsinline>
            <source src="<?php echo $partyInfo['vodList']['0']['m3u8'];?>" type='application/x-mpegURL'/>
        </video>
    </div>    
    <?php } else { ?>
    <div class="surface-container" id="lssPlayBox">
        <img id="carousel" src="<?php echo $partyInfo['carousel'];?>" />
    </div>
    <?php } ?>
<?php } ?>
<?php if(!empty($partyInfo['vodList'])) { ?>
<div id="slider"></div>
<?php } ?>
<div class="channel-info" id="channelInfo">
<?php if(WSP_REWRITE === true) { ?>
	<div class="back" onClick="window.location='<?php echo SYSTEM_HOST;?>/layout/livestream/<?php echo $partyInfo['channelId'];?>';">
		<a href="<?php echo SYSTEM_HOST;?>/layout/livestream/<?php echo $partyInfo['channelId'];?>"><img src="<?php echo WSP_ASSETS_PATH;?>images/arrowleft.png" /></a>
    </div>
<?php } else { ?>
	<div class="back" onClick="window.location='<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/livestream&id=<?php echo $partyInfo['channelId'];?>';">
		<a href="<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/livestream&id=<?php echo $partyInfo['channelId'];?>"><img src="<?php echo WSP_ASSETS_PATH;?>images/arrowleft.png" /></a>
    </div>
<?php } ?>
	<?php if($partyInfo['state'] == 1 || $partyInfo['state'] == 3) { ?>
    <div id="liveState" <?php if($partyInfo['living'] == 1) { ?>class="living"<?php } else { ?>class="unliving"<?php } ?>>LIVE</div>
    <?php } ?>
    <div class="channel-desc">
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon02.png" />
        <span id="pvNum"><?php echo $partyInfo['userNum'];?></span>
		<?php if($isPraises) { ?>
		<img id="praiseImg" src="<?php echo WSP_ASSETS_PATH;?>images/icon03b.png" />
		<?php } else { ?>
    	<img id="praiseImg" onclick="addPraises();" src="<?php echo WSP_ASSETS_PATH;?>images/icon03.png" />
    	<?php } ?>
        <span id="praiseNum"><?php echo $partyInfo['praiseNum'];?></span>
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon04.png" />
        <span id="msgNum"><?php echo $partyInfo['msgNum'];?></span>
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon05.png" />
        <span id="shareNum"><?php echo $partyInfo['shareNum'];?></span>
    	<div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>

<div id="dms"><img src="<?php echo WSP_ASSETS_PATH;?>images/loading.gif?v=1" class="loading"></div>
<?php if(!empty($partyInfo['vodList'])) { ?>
<script type="text/javascript" src="<?php echo WSP_ASSETS_PATH;?>js/zepto.js"></script>
<script type="text/javascript" src="<?php echo WSP_ASSETS_PATH;?>js/touch.js"></script>
<script type="text/javascript" src="<?php echo WSP_ASSETS_PATH;?>js/zepto.extend.js"></script>
<script type="text/javascript" src="<?php echo WSP_ASSETS_PATH;?>js/zepto.ui.js"></script>
<script type="text/javascript" src="<?php echo WSP_ASSETS_PATH;?>js/slider.js"></script>
<script type="text/javascript">
//创建slider组件
var videoList = $.parseJSON('<?php echo $videoList;?>');
var slider = $.ui.slider('#slider', {
	autoPlay:false,
	showArr:false,
	showDot:false,
	viewNum:5,
	loop:true,
	content:videoList
});
</script>
<?php } ?>
<script type="text/javascript" src="http://cdn.aodianyun.com/static/jquery/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
var video = document.getElementById('lss');
var partyId = "<?php echo $partyInfo['partyId'];?>";
var living = "<?php echo $partyInfo['living'];?>";

var DMS_SITE = '<?php echo SYSTEM_HOST;?>';
var DMS_OPENID = '';
<?php if($userInfo['Flag'] == 100) { ?>
var dmsConfig = {
  container:"dms",
  channelId:"<?php echo $partyInfo['channelId'];?>",
  partyId:"<?php echo $partyInfo['partyId'];?>",
  layout:"livestream",
  dmsAppKey:"<?php echo $serviceInfo['dmsAppKey'];?>",
  dmsPubKey:"<?php echo $serviceInfo['dmsPubKey'];?>",
  dmsSubKey:"<?php echo $serviceInfo['dmsSubKey'];?>",
  wxAppid:"<?php echo $serviceInfo['wxAppid'];?>",
  chatOpt:"<?php echo $partyInfo['chatOpt'];?>",
  topic:"<?php echo $partyInfo['topic'];?>",
  controlTopic:"<?php echo $partyInfo['controlTopic'];?>",
  isBlack:<?php echo $isBlack;?>,
  isGaps:<?php echo $isGaps;?>,
  uid:"<?php echo $userInfo['openid'];?>",
  nick:"<?php echo $userInfo['nick'];?>",
  ava:"<?php echo $userInfo['ava'];?>",
  carousel:"<?php echo $partyInfo['carousel'];?>",
  videoCarousel:"<?php echo empty($partyInfo['vodList']['0']['surfaceUrl']) ? '' : $partyInfo['vodList']['0']['surfaceUrl'].'/0/0';;?>",
  liveUrl:"<?php echo $videoUrl;?>",
  videoUrl:"<?php echo empty($partyInfo['vodList']['0']['m3u8']) ? '' : $partyInfo['vodList']['0']['m3u8'];;?>",
  redirect:"<?php echo $redirect;?>"
};
DMS_OPENID = '<?php echo $userInfo["openid"];?>';
<?php } else { ?>
var dmsConfig = {
  container:"dms",
  channelId:"<?php echo $partyInfo['channelId'];?>",
  partyId:"<?php echo $partyInfo['partyId'];?>",
  layout:"livestream",
  dmsAppKey:"<?php echo $serviceInfo['dmsAppKey'];?>",
  dmsPubKey:"<?php echo $serviceInfo['dmsPubKey'];?>",
  dmsSubKey:"<?php echo $serviceInfo['dmsSubKey'];?>",
  wxAppid:"<?php echo $serviceInfo['wxAppid'];?>",
  chatOpt:"<?php echo $partyInfo['chatOpt'];?>",
  topic:"<?php echo $partyInfo['topic'];?>",
  controlTopic:"<?php echo $partyInfo['controlTopic'];?>",
  isBlack:<?php echo $isBlack;?>,
  isGaps:<?php echo $isGaps;?>,
  carousel:"<?php echo $partyInfo['carousel'];?>",
  videoCarousel:"<?php echo empty($partyInfo['vodList']['0']['surfaceUrl']) ? '' : $partyInfo['vodList']['0']['surfaceUrl'].'/0/0';;?>",
  liveUrl:"<?php echo $videoUrl;?>",
  videoUrl:"<?php echo empty($partyInfo['vodList']['0']['m3u8']) ? '' : $partyInfo['vodList']['0']['m3u8'];;?>",
  redirect:"<?php echo $redirect;?>"
};
<?php } ?>

wx.config({
  debug: false,
  appId: '<?php echo $signPackage["appId"];?>',
  timestamp: <?php echo $signPackage["timestamp"];?>,
  nonceStr: '<?php echo $signPackage["nonceStr"];?>',
  signature: '<?php echo $signPackage["signature"];?>',
  jsApiList: [
	  'onMenuShareTimeline'
  ]
});
function shareTimeline(){
	wx.onMenuShareTimeline({
		title: '<?php echo addslashes($partyInfo['title']);;?>',
<?php if(WSP_REWRITE === true) { ?>		
		link: '<?php echo SYSTEM_HOST;?>/layout/party/<?php echo $partyInfo["partyId"];?>',
<?php } else { ?>
		link: '<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/party&id=<?php echo $partyInfo["partyId"];?>',
<?php } ?>
		imgUrl: '<?php echo $partyInfo["carousel"];?>',
		trigger: function (res) {},
		success: function (res) {
			$.ajax({
				type:'POST',
				url:'<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/addPartyShares',
				data:{uin:DMS_OPENID,partyId:partyId},
				dataType:'json',
				async:false,
				success:function(data){}
			});
		},
		cancel: function (res) {},
		fail: function (res) {}
	});
}

$(window).load(function(){
	$('#dms').height(document.body.clientHeight-$('#lssPlayBox').height()-$('#slider').height()-$('#channelInfo').height());
	$('.loading').show();
	(function() {
	  var dms = document.createElement('script');
	  dms.type = 'text/javascript';
	  dms.async = true;
      dms.src = DMS_SITE + '/dms/wsp.js';
	  dms.charset = 'UTF-8';
	  (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dms);
	})();
	if(living != '1'){
		$('.ui-slider-item').eq(0).find('img').addClass('active');
	}
	setTimeout("shareTimeline()",2000);
});
function play(){
	$('#playBtn').hide();
	$('#carousel').hide();
	var video = document.getElementById('lss');
	video.play();
}
function showVideo(key,url){
	video.pause();
	var html = '<source src="'+url+'" type=\'application/x-mpegURL\'/>';
	$('#lss').html(html);
	video.load(url);
	video.play();
	$('#playBtn').hide();
	$('#carousel').hide();
	$('.ui-slider-item').find('img').removeClass('active');
	$('.ui-slider-item').eq(key).find('img').addClass('active');
}
function addPraises(){
	if(typeof(dmsCommonHandle) == 'undefined'){
		alert('组件正在加载中，请稍后...');
		return;
	}
	if(DMS_OPENID == 0){
		dmsCommonHandle.wxLogin();
	}
	else{
		$.ajax({
			type:'POST',
			url:'<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/addPartyPraises',
			data:{uin:DMS_OPENID,partyId:partyId},
			dataType:'json',
			async:false,
			success:function(data){
				if(data.Flag == 100){
					$('#praiseImg').off('click');
					$('#praiseImg').attr('src','<?php echo WSP_ASSETS_PATH;?>images/icon03b.png');
					return;
				}
				else if(data.Flag == 102){
					$('#praiseImg').off('click');
					$('#praiseImg').attr('src','<?php echo WSP_ASSETS_PATH;?>images/icon03b.png');
					alert(data.FlagString);
					return;
				}
				else{
					alert('点赞失败，请稍后再试...');
					return;
				}
			}
		});
	}
}
</script>
</body>
</html>