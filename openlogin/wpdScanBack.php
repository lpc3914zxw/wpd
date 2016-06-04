<?php
require dirname(dirname(__FILE__)).'/system/Base.php';
$base = Base::init();

if(empty($_GET['state']) || empty($_GET['code'])){
	$error = '参数错误，请重试！';
	include('wxLoginFail.php');
	exit;
}
$state = explode(',',trim($_GET['state']));
if(empty($state[0]) || empty($state[1])){
	$error = '返回地址错误！';
	include('wxLoginFail.php');
	exit;
}
$secKey = $state[0];
$topic = $state[1];


$param = array('secKey'=>$secKey);
$serviceInfo = $base->api('wsp/wspService/getServiceBySecKey',$param);
if(empty($serviceInfo['Info']) || empty($serviceInfo['Info']['wxAppid']) || empty($serviceInfo['Info']['wxAppSecret'])){
	$error = '微信公众号配置错误！';
	include('wxLoginFail.php');
	exit;
}

$wxSdk = new LWxjssdk($serviceInfo['Info']['wxAppid'],$serviceInfo['Info']['wxAppSecret']);
$userInfo = $wxSdk->getUserInfo($_GET['code']);
if($userInfo['Flag'] != 100){
	$signPackage = $wxSdk->getSignPackage();
	$error = '登录失败，错误码：'.json_encode($userInfo['FlagString']);
	include('wxLoginFail.php');
	exit;
}

$userInfo = $userInfo['Info'];

include __BASE__."/openlogin/dms/phpMQTT.php";
include __BASE__."/openlogin/dms/Pusher.php";

$pusher = new Pusher('pub_b096fd62-0133-7821-d97f-43be9c20ab9b','');
$data = array('openid'=>$userInfo['openid'],'unionid'=>empty($userInfo['unionid'])?$userInfo['openid']:$userInfo['unionid'],'nick'=>$userInfo['nickname'],'picurl'=>$userInfo['headimgurl']);
$rst = $pusher->trigger($topic,json_encode($data));

if($rst == true){
	include('wxLoginSuccess.php');
	exit;
}
else{
	$wxSdk = new LWxjssdk($serviceInfo['Info']['wxAppid'],$serviceInfo['Info']['wxAppSecret']);
	$signPackage = $wxSdk->getSignPackage();
	$error = '登陆失败，请点击以下按钮重新扫码登陆！';
	include('wxLoginFail.php');
	exit;
}