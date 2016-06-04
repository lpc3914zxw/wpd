<?php
require dirname(dirname(__FILE__)).'/system/Base.php';
$base = Base::init();

if(empty($_GET['partyId']) || empty($_GET['code']) || empty($_GET['state'])){
	echo '<script>alert("登陆失败，请稍后重试..");window.location.history(-1);</script>';
	exit;
}
$partyId = $_GET['partyId'];
$host = base64_decode($_GET['state']);
if(WSP_REWRITE == true){
	$back = SYSTEM_HOST . '/layout/party/' . $partyId;
}
else{
	$back = SYSTEM_HOST . '/wsp/index.php?r=layout/party&id=' . $partyId;
}

if(!defined('WSP_SEC_KEY') || WSP_SEC_KEY == '{WSP_SEC_KEY}'){
	echo '<script>alert("微视评未安装");window.location.history(-1);</script>';
	exit;
}
$param = array('secKey'=>WSP_SEC_KEY);
$serviceInfo = $base->api('wsp/wspService/getServiceBySecKey',$param);
if(empty($serviceInfo['Info'])){
	echo '<script>alert("服务获取失败");window.location.history(-1);</script>';
	exit;
}

$wxAppid = $serviceInfo['Info']['wxAppid'];
$wxAppSecret = $serviceInfo['Info']['wxAppSecret'];
if(empty($wxAppid) || empty($wxAppSecret)){
	echo '<script>alert("微信服务号未配置");window.location.history(-1);</script>';
	exit;
}

$wxSdk = new LWxjssdk($wxAppid,$wxAppSecret);
$userInfo = $wxSdk->getUserInfo($_GET['code']);
if($userInfo['Flag'] != 100){
	echo '<script>alert("登录失败，错误码：'.json_encode($userInfo['FlagString']).'");window.location.history(-1);</script>';
	exit;
}
$userInfo = $userInfo['Info'];

$openid = trim($userInfo['openid']);
$nick = trim($userInfo['nickname']);
$picurl = empty($userInfo['headimgurl']) ? '' : trim($userInfo['headimgurl']);

$param = array('openid'=>$openid,'nick'=>$nick,'ava'=>$picurl);
$rst = $base->api('core/sso/setWspLogin',$param);

header("Location:$back");

?>