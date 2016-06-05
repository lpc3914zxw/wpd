<?php if (!class_exists('CTemplate')) die('Access Denied');?>
<html>

<!--template compile at 2015-12-28 15:11:07-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" type="text/css" href="<?php echo WSP_ASSETS_PATH;?>css/livestreamlayout.css"/>
<title><?php echo $channelInfo['title'];?></title>
</head>
<body>
<?php if(WSP_REWRITE === true) { ?>
<a href="<?php echo SYSTEM_HOST;?>/layout/party/<?php echo $activePartiesInfo['partyId'];?>">
<?php } else { ?>
<a href="<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/party&id=<?php echo $activePartiesInfo['partyId'];?>">
<?php } ?>
    <div class="surface-container">
        <div class="play-btn"></div>
        <?php if($activePartiesInfo['carousel']) { ?>
        <img src="<?php echo $activePartiesInfo['carousel'];?>" />
        <?php } ?>
    </div>
</a>
<div class="channel-info">
	<div <?php if($activePartiesInfo['living'] == 1) { ?>class="living"<?php } else { ?>class="unliving"<?php } ?>>LIVE</div>
    <div class="channel-desc">
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon02.png" />
        <span><?php echo $activePartiesInfo['pvNum'];?></span>
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon03.png" />
        <span><?php echo $activePartiesInfo['praiseNum'];?></span>
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon04.png" />
        <span><?php echo $activePartiesInfo['msgNum'];?></span>
    	<img src="<?php echo WSP_ASSETS_PATH;?>images/icon05.png" />
        <span><?php echo $activePartiesInfo['shareNum'];?></span>
    	<div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<?php if(is_array($partyList)) {foreach((array)$partyList as $val) {?>
<div class="party-info">
<?php if(WSP_REWRITE === true) { ?>
    <a class="party-img" href="<?php echo SYSTEM_HOST;?>/layout/party/<?php echo $val['partyId'];?>">
<?php } else { ?>
    <a class="party-img" href="<?php echo SYSTEM_HOST;?>/wsp/index.php?r=layout/party&id=<?php echo $val['partyId'];?>">
<?php } ?>
    	<img class="surface" src="<?php echo $val['carousel'];?>" />
    	<img class="live-btn" src="<?php echo WSP_ASSETS_PATH;?>images/liveBtn.png">
    </a>
    <div class="party-desc">
        <div class="party-title"><?php echo $val['title'];?></div>
        <div class="talk-num"><?php echo $val['msg_num'];?></div>
       	<img class="talks" src="<?php echo WSP_ASSETS_PATH;?>images/icon04.png" />
        <div class="clear"></div>
    </div>
    <div class="party-time"><?php echo $val['startTime'];?>&nbsp;至&nbsp;<?php echo $val['endTime'];?>&nbsp;&nbsp;&nbsp;&nbsp;浏览：<?php echo $val['pvNum'];?></div>
</div>
<?php }} ?>
</body>
</html>