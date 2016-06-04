<?php if (!class_exists('CTemplate')) die('Access Denied');?>
<div class="top">
 <div class="topcont">
  <div class="logo"><img src="<?php echo WPD_ASSETS_PATH;?>img/logo.png"></div>
  <div class="nav">
    <ul>
       <li><a<?php if($actionType == 'console') { ?> class="active"<?php } ?> href="index.php?r=console/index&channelId=<?php echo $channelId;?>">直播管理</a></li>
       <li><a<?php if($actionType == 'party') { ?> class="active"<?php } ?> href="index.php?r=party/index&channelId=<?php echo $channelId;?>">活动管理</a></li>
       <li><a<?php if($actionType == 'user') { ?> class="active"<?php } ?> href="index.php?r=user/index&channelId=<?php echo $channelId;?>&page=1">用户管理</a></li>
       <li><a<?php if($actionType == 'channel') { ?> class="active"<?php } ?> href="index.php?r=channel/index&channelId=<?php echo $channelId;?>">频道设置</a></li>

<!--       <li><a href="#">用户管理</a></li>
       <li><a href="#">聊天管理</a></li>
       <li><a href="#">广告管理</a></li>
       <li><a href="#">设置管理</a></li>
       <li><a href="#">数据分析</a></li>-->
    </ul>
  </div>
  <div class="userInfo">欢迎您！<span><?php echo $userInfo['nick'];?></span><a href="index.php?r=login/loginOut&channelId=<?php echo $channelId;?>">退出</a></div>
  </div>
</div>