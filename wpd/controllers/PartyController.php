<?php

class PartyController extends CController{
	
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
		$this->assign('actionType','party');
		$this->assign('channelId',$this->channelId);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('channelInfo',$this->channelInfo);
		$this->assign('serviceInfo',$this->serviceInfo);
	}
	
	public function actionCreate(){
		if($_POST){
			$title = $_POST['title'];
			$sTime = strtotime($_POST['sTime']);
			$eTime = strtotime($_POST['eTime']);
			
			if($title == ''){
				$this->assign('msg','请输入活动主题');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			if(mb_strlen($title,'UTF-8') > 20){
				$this->assign('msg','活动主题不能超过20个字');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			if(empty($sTime) || empty($eTime) || $sTime >= $eTime){
				$this->assign('msg','请选择正确的时间范围');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			if(empty($_FILES['pic'])){
				$this->assign('msg','请上传图片');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			if(strpos($_FILES['pic']['type'], 'image') === false){
				$this->assign('msg','上传图片格式必须为jpg，png，gif格式');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			$size = $_FILES['pic']['size']/(pow(1024, 2));
			if($size > 2){
				$this->assign('msg','图片不能大于2MB');
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}

			$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'channelId'=>$this->channelId,'startTime'=>$sTime,'endTime'=>$eTime,'title'=>$title,'pic'=>$_FILES['pic']['tmp_name'],'picType'=>$_FILES['pic']['type']);
			$rst = $this->base->api('wsp/wspService/addParties',$param);
			if($rst['Flag'] != 100){
				$this->assign('msg',$rst['FlagString']);
				$this->end(array('runtime'=>false,'tplfile'=>'party/createFail.html'));
				exit;
			}
			$this->end(array('runtime'=>false,'tplfile'=>'party/createSuccess.html'));
		}
		$this->end(array('runtime'=>false,'tplfile'=>'party/create.html'));
	}

	public function actionClose(){
		$id = $_POST['id'];
		if(empty($id)){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
		}
	
		$param = array('id'=>$id,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid']);
		$rst = $this->base->api('wsp/wspService/closeParties',$param);
		exit(json_encode($rst));
	}

	public function actionIndex(){
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'channelId'=>$this->channelId,'page'=>$page);
		$partiesList = $this->base->api('wsp/wspService/partyList',$param);
		$partiesList = empty($partiesList['Info']['list']) ? array() : $partiesList['Info']['list'];

		foreach ($partiesList as $key => $value) {
			$param = array('Type'=>'wxcarousel','Index'=>$value['surfaceUrl'],'w'=>75,'h'=>45);
			$rst = $this->base->api('core/pic/get',$param);
			$partiesList[$key]['startTime'] = date('m-d,H:i',$value['startTime']);
			$partiesList[$key]['etartTime'] = date('m-d,H:i',$value['etartTime']);
			$partiesList[$key]['surfaceUrl'] = $rst['pic'];
			if(WSP_REWRITE === true){
				$partyUrl = urlencode(SYSTEM_HOST."/layout/party/".$value["partyId"]);
			}
			else{
				$partyUrl = urlencode(SYSTEM_HOST."/wsp/index.php?r=layout/party&id=".$value["partyId"]);
			}
			$partiesList[$key]['partyUrl'] = $partyUrl;
		}

	 	$url = SYSTEM_HOST . '/index.php?';
		foreach ($_GET as $key => $value) {
			if($key != "page"){
				$url = $url . $key . '=' . $value . "&";
			}
		}
		$nextUrl = "";
		$preUrl = "";
		if(count($partiesList) == 7){
			$nextUrl = $url . 'page=' . ($page + 1);
		}
		if($page > 1){
			$preUrl = $url . 'page=' . ($page - 1);
		}
		
		$this->assign('indexUrl',$url);
		$this->assign('preUrl',$preUrl);
		$this->assign('nextUrl',$nextUrl);
		$this->assign('partiesList',$partiesList);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('page',$page);

		$this->assign('count',count($partiesList));		
		$this->end(array('runtime'=>false,'tplfile'=>'party/list.html'));
	}

	public function actionDelete(){
		if(empty($_POST['partyId'])){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误。')));
		}
		$partyId = $_POST['partyId'];
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'id'=>$partyId);
		$rst = $this->base->api('wsp/wspService/deleteParties',$param);
		exit(json_encode($rst));
	}

	public function actionEditParty(){
		//活动详情
		$partyId = $_GET['partyId'];	
		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'id'=>$partyId);
		$partyInfo = $this->base->api('wsp/wspService/partyInfo',$param);
		$partyInfo = empty($partyInfo['Info']) ? array() : $partyInfo['Info'];
		if(empty($partyInfo)){
			$this->showMsg('',-1);
		}
		if(!empty($partyInfo['surfaceUrl'])){
			$param = array('Type'=>'wxcarousel','Index'=>$partyInfo['surfaceUrl'],'w'=>400,'h'=>225);		
			$rst = $this->base->api('core/pic/get',$param);
			if($rst['Flag'] == 100){
				$partyInfo['surfaceUrl'] = $rst['pic'];
			}
		}

		//活动视频列表
		$vodList = array();
		if(!empty($partyInfo['vodList'])){
			foreach ($partyInfo['vodList'] as $key => $value) {
				$partyInfo['vodList'][$key]['start'] = date('Y-m-d H:i',$value['start']);
				$partyInfo['vodList'][$key]['end'] = date('Y-m-d H:i',$value['end']);
				$partyInfo['vodList'][$key]['surfaceUrl'] = $value['surfaceUrl'].'/0/0';
				$vodList[$value['id']] = $partyInfo['vodList'][$key];
			}
		}

		//黑名单
		$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>1,'num'=>100);
		$blackList = $this->base->api('wsp/dms/getBlacklists',$param);
		$blackList = empty($blackList['List']) ? array() : $blackList['List'];
		//禁言
		$param = array('channelId'=>$this->channelId,'wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],"page"=>1,'num'=>100);
		$gapsList = $this->base->api('wsp/dms/getGaps',$param);
		$gapsList = empty($gapsList['List']) ? array() : $gapsList['List'];

		$this->assign('partyInfo',$partyInfo);
		$this->assign('vodList',$vodList);
		$this->assign('blackList',json_encode($blackList));
		$this->assign('gapsList',json_encode($gapsList));
		$this->end(array('runtime'=>false,'tplfile'=>'party/info.html'));					
	}

	public function actionReditParty(){
		$partyId = $_POST['partyId'];
		$title = $_POST['title'];
		
		if($title == ''){
			$this->showMsg('请输入活动主题',-1);
		}
		if(mb_strlen($title,'UTF-8') > 20){
			$this->showMsg('活动主题不能超过20个字',-1);
		}
		$pic = '';
		$picType = '';
		if(!empty($_FILES['pic']['tmp_name'])){
			if(strpos($_FILES['pic']['type'], 'image') === false){
				$this->showMsg('上传图片格式必须为jpg，png，gif格式',-1);
			}
			$size = $_FILES['pic']['size']/(pow(1024, 2));
			if($size > 2){
				$this->showMsg('图片不能大于2MB',-1);
			}
			$pic = $_FILES['pic']['tmp_name'];
			$picType = $_FILES['pic']['type'];
		}

		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'id'=>$partyId,'title'=>$title,'pic'=>$pic,'picType'=>$picType);
		$rst = $this->base->api('wsp/wspService/editParty',$param);
        if($rst['Flag'] == 100){
        	$this->showMsg('修改成功！','index.php?r=party/editParty&channelId='.$this->channelId.'&partyId='.$partyId);
        }
        else{
        	$this->showMsg($rst['FlagString'],'index.php?r=party/editParty&channelId='.$this->channelId.'&partyId='.$partyId);
        }
    }

	public function actionEditVod(){
        $partyId = $_POST['partyId'];
        $vodId = $_POST['vodId'];
        $state = $_POST['state'];
        if(empty($partyId) || empty($vodId) || !isset($state)){
        	exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
        }

        $param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'vodId'=>$vodId,'state'=>$state);
        $rst = $this->base->api('wsp/wspService/vodEdit',$param);
        exit(json_encode($rst));
    }

    public function actionEditVods(){
        $partyId = $_POST['partyId'];
        $vodIds = $_POST['vodIds'];
        $state = $_POST['state'];
        if(empty($partyId) || empty($vodIds) || !isset($state)){
        	exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
        }

        foreach($vodIds as $vodId){
			$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'vodId'=>$vodId,'state'=>$state);
        	$rst = $this->base->api('wsp/wspService/vodEdit',$param);
        	if($rst['Flag'] != 100){
        		exit(json_encode($rst));
        	}
		}
        exit(json_encode($rst));
    }

    public function actionDeleteVod(){
    	$partyId = $_POST['partyId'];
        $vodId = $_POST['vodId'];
        if(empty($partyId) || empty($vodId)){
        	exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
        }
        $param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'vodId'=>$vodId);
        $rst = $this->base->api('wsp/wspService/vodDelete',$param);       
        exit(json_encode($rst));
    }

	public function actionGetUserUpload(){
		$page = !empty($_POST['page']) && intval($_POST['page']) > 0 ? $_POST['page'] : 1;
		$param = array('uin'=>$this->userInfo['adminUin'],'filename'=>'','num'=>9,'page'=>$page);
        $list = $this->base->api('aodianyun/dvr/upDvrList',$param);
        $list = !empty($list['List'])?$list['List']:array();
        if(!empty($list)){
	     	foreach ($list as $key => $value) {
				$list[$key]['uptime'] = date('Y-m-d H:i',$value['uptime']);
				if(!empty($value['m3u8'])){
					$list[$key]['m3u8'] = $value['m3u8'];
				}
				if(!empty($value['adaptive'])){
					$list[$key]['m3u8'] = $value['adaptive'];
				}
				if(!empty($value['m3u8_240'])){
					$list[$key]['m3u8'] = $value['m3u8_240'];
				}
				if(!empty($value['m3u8_360'])){
					$list[$key]['m3u8'] = $value['m3u8_360'];
				}
				if(!empty($value['m3u8_480'])){
					$list[$key]['m3u8'] = $value['m3u8_480'];
				}
				if(!empty($value['m3u8_720'])){
					$list[$key]['m3u8'] = $value['m3u8_720'];
				}
				if(!empty($value['m3u8_1080'])){
					$list[$key]['m3u8'] = $value['m3u8_1080'];
				}
			}
		}
    	exit(json_encode(array('Flag'=>100,'FlagString'=>'成功','List'=>$list)));
	}

    public function actionTranscodingConfig(){
	    $videoRate = array('0'=>'0x0','4'=>'480x360','5'=>'320x240');
	    $param = array('uin'=>$this->userInfo['adminUin'],'videoRate'=>$videoRate);
	    $rst = $this->base->api('aodianyun/dvr/dvrTranscodingConfigSet',$param);
	    exit(json_encode($rst));
    }

    public function actionVodAdd(){
    	$partyId = $_POST['partyId'];
        $vodId = $_POST['vodId'];
        $url = $_POST['url'];
        if(empty($partyId) || empty($vodId) || empty($url)){
        	exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
        }

		if(!is_array($url)){
			$url = array($url);
		}
		if(!is_array($vodId)){
			$vodId = array($vodId);
		}

		$param = array('uin'=>$this->userInfo['adminUin'],'url'=>$url);
		$rst = $this->base->api('aodianyun/dvr/syncVod',$param);
		if($rst['Flag'] != 100){
			exit(json_encode(array('Flag'=>101,'FlagString'=>'视频同步失败，请稍后再试')));
		}

		foreach($vodId as $value){
			$param = array('id'=>$value);
			$info = $this->base->api('aodianyun/dvr/upVodDetail',$param);
			$info = !empty($info['List'])?$info['List']:array();
			if(empty($info)){
				exit(json_encode(array('Flag'=>102,'FlagString'=>'视频同步查询失败，请稍后再试')));
			}
			$m3u8 = '';
			if(!empty($info['m3u8_360'])){
				$m3u8 = $info['m3u8_360'];
			}
			else{
				if(!empty($info['m3u8'])){
					$m3u8 = $info['m3u8'];
				}
				if(!empty($info['adaptive'])){
					$m3u8 = $info['adaptive'];
				}
				if(!empty($info['m3u8_1080'])){
					$m3u8 = $info['m3u8_1080'];
				}
				if(!empty($info['m3u8_720'])){
					$m3u8 = $info['m3u8_720'];
				}
				if(!empty($info['m3u8_480'])){
					$m3u8 = $info['m3u8_480'];
				}
				if(!empty($info['m3u8_240'])){
					$m3u8 = $info['m3u8_240'];
				}
			}
			if(empty($m3u8)){
				exit(json_encode(array('Flag'=>103,'FlagString'=>'无效的视频')));
			}
			$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'vodId'=>$value,'url'=>$info['url'],'m3u8'=>$m3u8,'start'=>$info['uptime'],'end'=>$info['uptime'],'title'=>$info['title'],'surfaceUrl'=>$info['thumbnail']);
	        $rst = $this->base->api('wsp/wspService/vodAdd',$param);
	        if($rst['Flag'] != 100){
				exit(json_encode(array('Flag'=>104,'FlagString'=>'视频同步添加失败，请稍后再试')));
			}
    	}
        exit(json_encode(array('Flag'=>100,'FlagString'=>'操作成功')));
	}
	
	public function actionUploadVodDelete(){
		$partyId = $_POST['partyId'];
        $vodId = $_POST['vodId'];
        $url = $_POST['url'];
        if(empty($partyId) || empty($vodId) || empty($url)){
        	exit(json_encode(array('Flag'=>101,'FlagString'=>'参数错误')));
        }

		$param = array('uin'=>$this->userInfo['adminUin'],'url'=>array($url));
        $rst = $this->base->api('aodianyun/dvr/delDvrFiling',$param);
        if($rst['Flag'] != 100){
			exit(json_encode(array('Flag'=>102,'FlagString'=>'视频同步删除失败，请稍后再试')));
		}

        $param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'partyId'=>$partyId,'vodId'=>$vodId);
        $rst = $this->base->api('wsp/wspService/vodDelete',$param);       
        exit(json_encode($rst));
	}

	private function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;

		$key = md5($key ? $key : UC_KEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
	
}