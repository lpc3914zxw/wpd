<?php

class LayoutController extends CController{
	private $secKey;
	private $serviceInfo;
	
	public function beforeAction(){
		if(!empty($_GET['install']) && $_GET['install'] == 1 && ($_GET['r'] == 'layout/party' || $_GET['r'] == 'layout/livestream')){
			exit('success');
		}
		$environmentInfo = $this->checkEnvironment();
		if(empty($environmentInfo)){
			new CHttpHeader('404');
		}
		$this->serviceInfo = $environmentInfo['service'];
		if($this->serviceInfo['state'] != 1 || $this->serviceInfo['freeze'] != 0){
			new CHttpHeader('404');
		}
		$this->secKey = $this->serviceInfo['secKey'];
		$this->begin(array('pagecache'=>false,'template'=>'wsp'));
		$this->assign('serviceInfo',$this->serviceInfo);
	}
	
	public function actionLivestream(){
		if(empty($_GET['id'])){
			$this->showMsg('参数错误',-1);
		}
		$id = intval($_GET['id']);
		//频道详情
		$param = array('id'=>$id,'wspKey'=>$this->secKey);
		$channelInfo = $this->base->api('wsp/wspService/channelInfo',$param);
		$channelInfo = $channelInfo['Info'];
		if(empty($channelInfo)){
			$this->showMsg('此频道未开通或已关闭',-1);
		}
		
		//当前正在进行的活动
		$param = array('id'=>$id,'wspKey'=>$this->secKey);
		$activePartiesInfo = $this->base->api('wsp/wspService/activeParties',$param);
		$activePartiesInfo = $activePartiesInfo['Info'];
		if(empty($activePartiesInfo['surfaceUrl'])){
			$index = '231f17875da553c0630dbfa89bf3c648';
		}
		else{
			$index = $activePartiesInfo['surfaceUrl'];
		}
		$activePartiesInfo['carousel'] = 'http://cdn.dvr.aodianyun.com/pic/live-vod/images/live_2158.1428909213.1434531748/0/0';
		$param = array('Type'=>'wxcarousel','Index'=>$index,'w'=>400,'h'=>225);
		$rst = $this->base->api('core/pic/get',$param);
		if($rst['Flag'] == 100){
			$activePartiesInfo['carousel'] = $rst['pic'];
		}
		
		//活动列表
		$param = array('channelId'=>$id,'wspKey'=>$this->secKey,'page'=>1,'num'=>100);
		$partyList = $this->base->api('wsp/wspService/partyList',$param);
		$partyList = empty($partyList['Info']['list']) ? array() : $partyList['Info']['list'];
		foreach($partyList as $key=>$val){
			if($val['state'] == 3 || $val['state'] == 1){
				unset($partyList[$key]);
				continue;
			}
			$partyList[$key]['startTime'] = date('Y-m-d H:i',$val['startTime']);
			$partyList[$key]['endTime'] = date('Y-m-d H:i',$val['etartTime']);
			if(empty($val['surfaceUrl'])){
				$index = '231f17875da553c0630dbfa89bf3c648';
			}
			else{
				$index = $val['surfaceUrl'];
			}
			$param = array('Type'=>'wxcarousel','Index'=>$index,'w'=>400,'h'=>225);
			$rst = $this->base->api('core/pic/get',$param);
			if($rst['Flag'] == 100){
				$partyList[$key]['carousel'] = $rst['pic'];
			}
		}
		
		$this->assign('channelInfo',$channelInfo);
		$this->assign('activePartiesInfo',$activePartiesInfo);
		$this->assign('partyList',$partyList);
		$partyId = $activePartiesInfo['partyId'];
		$this->assign('partyId',$partyId);
		$redirect = urlencode(SYSTEM_HOST.'/openlogin/wspLogin.php').'?partyId='.$partyId;
		$this->assign('redirect',$redirect);
		
		$this->end(array('runtime'=>false,'tplfile'=>'layout/livestream/index.html'));
		
	}
	
	public function actionWinAuth()
	{
		if(empty($_GET['id'])){
			$this->showMsg('参数错误',-1);
		}
		$id = intval($_GET['id']);
		
	}
	
	
	public function actionParty(){
		if(empty($_GET['id'])){
			$this->showMsg('参数错误',-1);
		}
		$id = intval($_GET['id']);
		
		//party详情
		$param = array('id'=>$id,'wspKey'=>$this->secKey);
		$partyInfo = $this->base->api('wsp/wspService/partyInfo',$param);
		$partyInfo = $partyInfo['Info'];
		if(empty($partyInfo)){
			$this->showMsg('此活动未开通或已关闭',-1);
		}
		
		$partyInfo['title'] = trim($partyInfo['title']);
		$partyInfo['carousel'] = 'http://cdn.dvr.aodianyun.com/pic/live-vod/images/live_2158.1428909213.1434531748/0/0';
		//封面图
		if(empty($partyInfo['surfaceUrl'])){
			$index = '231f17875da553c0630dbfa89bf3c648';
		}
		else{
			$index = $partyInfo['surfaceUrl'];
		}
		$param = array('Type'=>'wxcarousel','Index'=>$index,'w'=>400,'h'=>225);
		$rst = $this->base->api('core/pic/get',$param);
		if($rst['Flag'] == 100){
			$partyInfo['carousel'] = $rst['pic'];
		}

		//是否登陆
		$param = array();
		$userInfo = $this->base->api('core/sso/getWspLogin');
		$isBlack = 'false';
		$isGaps = 'false';
		$isPraises = false;
		if($userInfo['Flag'] == 100){
			//增加用户
			$param = array('wspKey'=>$this->secKey,'partyId'=>$id,'uin'=>$userInfo['openid'],'nick'=>urlencode($userInfo['nick']),'headimgurl'=>urlencode($userInfo['ava']));
			$rst = $this->base->api('wsp/dms/addDmsUser',$param);
			//是否是黑名单
			$param = array('wspKey'=>$this->secKey,'channelId'=>$partyInfo['channelId'],'openid'=>$userInfo['openid']);
			$rst = $this->base->api('wsp/dms/isBlack',$param);
			$isBlack = $rst['Flag'] == 100 ? 'true' : 'false';
			//是否是禁言
			$param = array('wspKey'=>$this->secKey,'channelId'=>$partyInfo['channelId'],'openid'=>$userInfo['openid']);
			$rst = $this->base->api('wsp/dms/isGaps',$param);
			$isGaps = $rst['Flag'] == 100 ? 'true' : 'false';
			//是否点赞
			$param = array('id'=>$id,'uin'=>$userInfo['openid'],'wspKey'=>$this->secKey);
			$rst = $this->base->api('wsp/wspService/checkUserPraises',$param);
			$isPraises = $rst['Flag'] == 100 ? true : false;
		}

		if($partyInfo['state'] == 0){
			$partyInfo['living'] = 0;
		}
		if($partyInfo['state'] != 0){
			$partyInfo['vodList'] = array();
		}

		$wx = new LWxjssdk($this->serviceInfo['wxAppid'],$this->serviceInfo['wxAppSecret']);
		$signPackage = $wx->getSignPackage();
		$this->assign('signPackage',$signPackage);
		
		//增加浏览量
		$param = array('id'=>$id,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/addPartyPv',$param);
		
		//如果点赞就点赞
		$praises = 0;
		if(!empty($_GET['p'])){
			$praises = 1;
		}

		//VOD列表
		$videoList = array();
		$k = 0;
		if(!empty($partyInfo['vodList'])){
			foreach($partyInfo['vodList'] as $key=>$val){
				if($val['state'] != 1){
					unset($partyInfo['vodList'][$key]);
					continue;
				}
				$videoList[] = array(
					'href' => 'javascript:showVideo('.$k.',\''.$val['m3u8'].'\')',
					'pic' => $val['surfaceUrl'].'/145/80',
					'title' => ''
				);
				$k++;
			}
		}
		$partyInfo['vodList'] = array_values($partyInfo['vodList']);
		$videoList = addslashes(json_encode($videoList));
		
		$videoUrl = 'http://'.$this->serviceInfo['uid'].'.hlsplay.aodianyun.com/'.$partyInfo['lssApp'].'/'.$partyInfo['lssStream'].'.m3u8';
		$redirect = urlencode(SYSTEM_HOST.'/openlogin/wspLogin.php').'?partyId='.$id;
		
		$this->assign('userInfo',$userInfo);
		$this->assign('isBlack',$isBlack);
		$this->assign('isGaps',$isGaps);
		$this->assign('isPraises',$isPraises);
		$this->assign('partyInfo',$partyInfo);
		$this->assign('videoList',$videoList);
		$this->assign('videoUrl',$videoUrl);
		$this->assign('redirect',$redirect);
		
		$this->end(array('runtime'=>false,'tplfile'=>'layout/livestream/party.html'));
	}
	
	public function actionAddPartyPraises(){
		if(empty($_POST['uin']) || empty($_POST['partyId'])){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}
		$uin = strval($_POST['uin']);
		$id = intval($_POST['partyId']);
		//判断是否点赞
		$param = array('id'=>$id,'uin'=>$uin,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/checkUserPraises',$param);
		if($rst['Flag'] == 100){
			exit(json_encode(array('Flag'=>102,'FlagString'=>'您已经点过赞啦！')));
		}
		//点赞
		$param = array('id'=>$id,'uin'=>$uin,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/addPartyPraises',$param);
		exit(json_encode($rst));
	}
	
	public function actionCheckUserPraises(){
		if(empty($_POST['uin']) || empty($_POST['partyId'])){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}
		$uin = strval($_POST['uin']);
		$id = intval($_POST['partyId']);
		$param = array('id'=>$id,'uin'=>$uin,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/checkUserPraises',$param);
		exit(json_encode($rst));
	}
	
	public function actionAddPartyShares(){
		if(empty($_POST['uin']) || empty($_POST['partyId'])){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}
		$uin = strval($_POST['uin']);
		$id = intval($_POST['partyId']);
		//判断是否分享
		$param = array('id'=>$id,'uin'=>$uin,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/checkUserShares',$param);
		if($rst['Flag'] == 100){
			exit(json_encode(array('Flag'=>102,'FlagString'=>'已分享！')));
		}
		//分享
		$param = array('id'=>$id,'uin'=>$uin,'wspKey'=>$this->secKey);
		$rst = $this->base->api('wsp/wspService/addPartyShares',$param);
		exit(json_encode($rst));
	}

	private function checkEnvironment(){
		$mode = '#^([0-9]+)\.wx\.cdn\.aodianyun\.com#';
		if(preg_match($mode,CDOMAIN,$arr)){
			$uid = $arr[1];
			$param = array('uid'=>$uid);
			$serviceInfo = $this->base->api('wsp/wspService/getServiceByUser',$param);
			if(!empty($serviceInfo['Info'])){
				return array('service'=>$serviceInfo['Info'],'environment'=>'wsp');
			}
		}

		$param = array('domain'=>CDOMAIN);
		$serviceInfo = $this->base->api('wsp/wspService/getServiceByDomain',$param);
		if(!empty($serviceInfo['Info'])){
			return array('service'=>$serviceInfo['Info'],'environment'=>'cname');
		}

		if(defined('WSP_SEC_KEY') && WSP_SEC_KEY != '{WSP_SEC_KEY}'){
			$param = array('secKey'=>WSP_SEC_KEY);
			$serviceInfo = $this->base->api('wsp/wspService/getServiceBySecKey',$param);
			if(!empty($serviceInfo['Info'])){
				return array('service'=>$serviceInfo['Info'],'environment'=>'server');
			}
		}

		return null;
	}

	private function get_by_curl($url,$post = false){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
}