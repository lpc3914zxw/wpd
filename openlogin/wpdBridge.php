<?php
require dirname(dirname(__FILE__)).'/system/Base.php';
$base = Base::init();

if(empty($_GET['secKey']) || empty($_GET['openid']) || empty($_GET['unionid']) || empty($_GET['nick'])){
	show_msg('登陆失败，请重新登陆',SYSTEM_HOST);
}

$secKey = trim($_GET['secKey']);
$openid = trim($_GET['openid']);
$unionid = trim($_GET['unionid']);
$nick = urldecode(trim($_GET['nick']));
$picurl = empty($_GET['picurl']) ? '' : urldecode(trim($_GET['picurl']));
$channelId = empty($_GET['channelId']) ? '' : $_GET['channelId'];

$param = array('secKey'=>$secKey);
$serviceInfo = $base->api('wsp/wspService/getServiceBySecKey',$param);
if(empty($serviceInfo['Info']) || $serviceInfo['Info']['state'] != 1 || $serviceInfo['Info']['freeze'] != 0){
	new CHttpHeader('404');
}
$serviceInfo = $serviceInfo['Info'];

$param = array('adminUin'=>$serviceInfo['uid'],'adminWxUid'=>$serviceInfo['wxUid'],'adminWxOpenId'=>$serviceInfo['wxOpenId'],'adminWxNick'=>$serviceInfo['wxNick'],'key'=>$secKey,'openid'=>$openid,'unionid'=>$unionid,'nick'=>$nick,'ava'=>$picurl);
$rst = $base->api('core/sso/setLogin',$param);

if($rst['Flag'] != 100){
	show_msg($rst['FlagString'],SYSTEM_HOST);
}
$back = SYSTEM_HOST.'/wpd/index.php?r=login/index&secKey='.$secKey.'&channelId='.$channelId;
header("Location:$back");