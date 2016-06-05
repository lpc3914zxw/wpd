<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no"> 
<link rel="stylesheet" type="text/css" href="http://wx.aodianyun.com/wsp/assets/css/livestream.css"> 
<title>扫码登陆</title>
</head>
<body style="background:#f2f2f2; text-align: center;">
<div class="publish-info-container">
	<p class="info-title"><font color="red">登陆失败</font></p>
    <p class="info-desc"><?php echo $error; ?></p>
</div>
<div style="margin:0 20px auto;">
    <a href="#" onclick="scan();return false;" class="button button-blue">扫码登陆</a>
</div>

<script type="text/javascript" src="http://cdn.aodianyun.com/static/jquery/jquery-1.7.2.min.js"></script> 
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
wx.config({
  debug: false,
  appId: '<?php echo $signPackage["appId"]; ?>',
  timestamp: <?php echo $signPackage["timestamp"]; ?>,
  nonceStr: '<?php echo $signPackage["nonceStr"]; ?>',
  signature: '<?php echo $signPackage["signature"]; ?>',
  jsApiList: [
	  'scanQRCode'
  ]
});
function scan(){
	wx.scanQRCode();
}
</script>
</body>
</html>