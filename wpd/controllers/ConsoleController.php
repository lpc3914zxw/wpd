<?php

class ConsoleController extends CController{
	
	private $userInfo;
	private $serviceInfo;
	private $channelId;
	private $channelInfo;

	public function beforeAction(){
		if(empty($_GET['channelId'])){
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				exit(json_encode(array('Flag'=>110,'FlagString'=>'没有查询到任何服务记录，请确认链接是否正确','code'=>1,'message'=>'没有查询到任何服务记录，请确认链接是否正确')));
			}
			else{
				new CHttpHeader('404');
			}
		}

		$param = array('channelId'=>$_GET['channelId']);
		$userInfo = $this->base->api('core/sso/getLogin',$param);
		if($userInfo['Flag'] != 100){
			$this->showMsg('登陆失败，请重新的登陆',SYSTEM_HOST);
		}
		$this->channelId = $userInfo['channelInfo']['id'];
		$this->serviceInfo = $userInfo['serviceInfo'];
		$this->channelInfo = $userInfo['channelInfo'];
		unset($userInfo['serviceInfo']);
		unset($userInfo['channelInfo']);
		$this->userInfo = $userInfo;

		$mode = '#^([0-9]+)\.wx\.cdn\.aodianyun\.com#';
		if(preg_match($mode,CDOMAIN)){
			$this->channelInfo['host'] = empty($serviceInfo['cname']) ? CDOMAIN : $serviceInfo['cname'];
		}
		else{
			$this->channelInfo['host'] = CDOMAIN;
		}

		$this->begin(array('pagecache'=>false,'template'=>'playConsole'));
		$this->assign('actionType','console');
		$this->assign('channelId',$this->channelId);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('channelInfo',$this->channelInfo);
		$this->assign('serviceInfo',$this->serviceInfo);
	}
	
	public function actionIndex(){
		//当前正在进行的活动
		$param = array('id'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
		$activePartiesInfo = $this->base->api('wsp/wspService/activeParties',$param);
		$activePartiesInfo = $activePartiesInfo['Info'];
		
		//增加新用户
		if(!empty($activePartiesInfo)){
			$param = array('partyId'=>$activePartiesInfo['partyId'],'uin'=>$this->userInfo['openid'],'nick'=>$this->userInfo['nick'],'headimgurl'=>$this->userInfo['ava']);
			$rst = $this->base->api('wsp/dms/addDmsUser',$param);
		}

		$lssApp = $this->channelInfo['lssApp'];
		$lssStream = $this->channelInfo['lssStream'];
		
		$rtmpPublishUrl = 'rtmp://'.$this->userInfo['adminUin'].'.lsspublish.aodianyun.com/'.$lssApp.'/'.$lssStream;
		$rtmpUrl = 'rtmp://'.$this->userInfo['adminUin'].'.lssplay.aodianyun.com/'.$lssApp.'/'.$lssStream;
		$rtmpPublishAddr = 'rtmp://'.$this->userInfo['adminUin'].'.lsspublish.aodianyun.com/'.$lssApp;
		$rtmpAddr = 'rtmp://'.$this->userInfo['adminUin'].'.lssplay.aodianyun.com/'.$lssApp;
		$hlsUrl = 'http://'.$this->userInfo['adminUin'].'.lssplay.aodianyun.com/'.$lssApp.'/'.$lssStream.'.m3u8';

		//黑名单
		$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>1,'num'=>100);
		$blackList = $this->base->api('wsp/dms/getBlacklists',$param);
		$blackList = empty($blackList['List']) ? array() : $blackList['List'];
		//禁言
		$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>1,'num'=>100);
		$gapsList = $this->base->api('wsp/dms/getGaps',$param);
		$gapsList = empty($gapsList['List']) ? array() : $gapsList['List'];
		
		if(WSP_REWRITE === true){
			$channelUrl = urlencode(SYSTEM_HOST."/layout/livestream/".$channelInfo["id"]);
			$partyUrl = urlencode(SYSTEM_HOST."/layout/party/".$activePartiesInfo["partyId"]);
		}
		else{
			$channelUrl = urlencode(SYSTEM_HOST."/wsp/index.php?r=layout/livestream&id=".$this->channelInfo["id"]);
			$partyUrl = urlencode(SYSTEM_HOST."/wsp/index.php?r=layout/party&id=".$activePartiesInfo["partyId"]);
		}
		
		$this->assign('activePartiesInfo',$activePartiesInfo);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('lssApp',$lssApp);
		$this->assign('lssStream',$lssStream);
		$this->assign('rtmpPublishUrl',$rtmpPublishUrl);
		$this->assign('rtmpUrl',$rtmpUrl);
		$this->assign('rtmpPublishAddr',$rtmpPublishAddr);
		$this->assign('rtmpAddr',$rtmpAddr);
		$this->assign('hlsUrl',$hlsUrl);
		$this->assign('blackList',json_encode($blackList));
		$this->assign('gapsList',json_encode($gapsList));
		$this->assign('channelUrl',$channelUrl);
		$this->assign('partyUrl',$partyUrl);
		$this->end(array('runtime'=>false,'tplfile'=>'console/index.html'));
	}

	public function actionShare(){
		$type = empty($_GET['type']) ? 'party' : $_GET['type'];
		$url = empty($_GET['url']) ? '' : urldecode($_GET['url']);
		$title = empty($_GET['title']) ? '' : urldecode($_GET['title']);
		$desc = empty($_GET['desc']) ? '' : urldecode($_GET['desc']);
		$img = empty($_GET['img']) ? '' : urldecode($_GET['img']);

		$this->assign('type',$type);
		$this->assign('url',$url);
		$this->assign('title',$title);
		$this->assign('desc',$desc);
		$this->assign('img',$img);
		$this->end(array('runtime'=>false,'tplfile'=>'console/share.html'));
	}

	public function actionPhonePublishConfig(){
		$url = urlencode('http://wx.aodianyun.com/wsp/index.php?r=phonePublish/getPhonePublishConfig&key='.$this->channelInfo['loginKey']);
		$this->assign('url',$url);
		$this->end(array('runtime'=>false,'tplfile'=>'console/phonePublishConfig.html'));
	}
	
	public function actionOutPublishVideo(){
		$rtmpPublishUrl = urldecode($_GET['rtmpPublishUrl']);
		$rtmpUrl = urldecode($_GET['rtmpUrl']);
		
		$this->assign('rtmpPublishUrl',$rtmpPublishUrl);
		$this->assign('rtmpUrl',$rtmpUrl);
		$this->end(array('runtime'=>false,'tplfile'=>'console/outPublishVideo.html'));
	}
	
	public function actionDeleteMessage(){
		$uin = $_POST['uin'];
		$time = intval($_POST['time']);
		$partyId = intval($_POST['partyId']);
		$nick = base64_decode($_POST['nick']);
		$ava = base64_decode($_POST['ava']);
		$url = base64_decode($_POST['url']);
		$content = base64_decode($_POST['content']);

		$this->checkPartyId($partyId);
		
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'uid'=>$uin,'adminUid'=>$this->userInfo['adminUin'],'time'=>$time,'nick'=>$nick,'ava'=>$ava,'url'=>$url,'content'=>$content,'body'=>'','partyId'=>$partyId);
		$rst = $this->base->api('wsp/dms/deleteDmsMessage',$param);
		exit(json_encode($rst));
	}
	
	public function actionAddBlacklists(){
		$uin = strval($_POST['uin']);
		$nick = base64_decode($_POST['nick']);
		
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'channelId'=>$this->channelId,'uid'=>$uin,'nick'=>$nick);
		$rst = $this->base->api('wsp/dms/addBlacklists',$param);
		exit(json_encode($rst));
	}
	
	public function actionDeleteBlacklists(){
		$uin = strval($_POST['uin']);
		
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'uid'=>$uin,'channelId'=>$this->channelId);
		$rst = $this->base->api('wsp/dms/deleteBlacklists',$param);
		if($rst['Flag'] == 100){
			$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
			$blackList = $this->base->api('wsp/dms/getBlacklists',$param);
			$blackList = empty($blackList['List']) ? array() : $blackList['List'];
			$rst['blackList'] = $blackList;
		}
		exit(json_encode($rst));
	}
	
	public function actionAddGaps(){
		$uin = strval($_POST['uin']);
		$nick = base64_decode($_POST['nick']);
		
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'channelId'=>$this->channelId,'uid'=>$uin,'nick'=>$nick);
		$rst = $this->base->api('wsp/dms/addGaps',$param);
		exit(json_encode($rst));
	}
	
	public function actionDeleteGaps(){
		$uin = strval($_POST['uin']);
		
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'uid'=>$uin,'channelId'=>$this->channelId);
		$rst = $this->base->api('wsp/dms/deleteGaps',$param);
		if($rst['Flag'] == 100){
			$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
			$gapsList = $this->base->api('wsp/dms/getGaps',$param);
			$gapsList = empty($gapsList['List']) ? array() : $gapsList['List'];
			$rst['gapsList'] = $gapsList;
		}
		exit(json_encode($rst));
	}

	public function actionCloseDms(){
		$partyId = $_POST['partyId'];
		if(empty($partyId)){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}

		$this->checkPartyId($partyId);
	
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'adminUid'=>$this->userInfo['adminUin']);
		$rst = $this->base->api('wsp/dms/closeDms',$param);
		
		exit(json_encode($rst));
	}

	public function actionOpenDms(){
		$partyId = $_POST['partyId'];
		if(empty($partyId)){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}

		$this->checkPartyId($partyId);
	
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'adminUid'=>$this->userInfo['adminUin']);
		$rst = $this->base->api('wsp/dms/openDms',$param);
		
		exit(json_encode($rst));
	}
	
	public function actionCutMic(){
		$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
		$rst = $this->base->api('wsp/wspService/cutMic',$param);
		
		exit(json_encode($rst));
	}

	private function checkPartyId($id){
		$param = array('id'=>$id,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
		$partyInfo = $this->base->api('wsp/wspService/partyInfo',$param);
		$partyInfo = $partyInfo['Info'];
		if(empty($partyInfo)){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}
	}
	
}