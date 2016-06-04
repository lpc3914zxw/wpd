<?php

class UserController extends CController{
	
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

		$this->begin(array('pagecache'=>false,'template'=>'wpd'));
		$this->assign('actionType','user');
		$this->assign('channelId',$this->channelId);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('channelInfo',$this->channelInfo);
		$this->assign('serviceInfo',$this->serviceInfo);
	}

	public function actionIndex(){
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$type = !empty($_GET['type']) && in_array($_GET['type'],array('gap','black')) ? $_GET['type'] : '';
		$num = 10;

		if($type == 'black'){
			$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>$page,'num'=>$num);
			$userList = $this->base->api('wsp/dms/getBlacklists',$param);
			$userList = empty($userList['List']) ? array() : $userList['List'];
		}
		elseif($type == 'gap'){
			$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>$page,'num'=>$num);
			$userList = $this->base->api('wsp/dms/getGaps',$param);
			$userList = empty($userList['List']) ? array() : $userList['List'];
		}
		else{
			$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'channelId'=>$this->channelId,"page"=>$page,'num'=>$num);
			$userList = $this->base->api('wsp/wspService/userListByChannel',$param);
			$userList = empty($userList['Info']['list']) ? array() : $userList['Info']['list'];
		}
		
		foreach ($userList  as $key => $value) {
			$userList[$key]['nick'] = urldecode($value['nick']);
			$userList[$key]['url'] = urldecode($value['url']);
			$userList[$key]['time']=date('y-m-d,H:i',$value['time']);
		}

		$url = SYSTEM_HOST . '/index.php?';
		foreach ($_GET as $key => $value) {
			if($key != "page"){
				$url = $url . $key . '=' . $value . "&";
			}
		}
		$nextUrl = "";
		$preUrl = "";
		if(count($userList) == $num){
			$nextUrl = $url . 'page=' . ($page + 1);
		}
		if($page > 1){
			$preUrl = $url . 'page=' . ($page - 1);
		}

		$this->assign('page',$page);
		$this->assign('type',$type);
		$this->assign('indexUrl',$url);
		$this->assign('preUrl',$preUrl);
		$this->assign('nextUrl',$nextUrl);
		$this->assign('userList',$userList);
		$this->end(array('runtime'=>false,'tplfile'=>'user/list.html'));
	}
	
}