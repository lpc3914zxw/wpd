<?php

class ChannelController extends CController{
	
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
		if(!empty($this->channelInfo['surfaceUrl'])){
			$param = array('Type'=>'wxcarousel','Index'=>$this->channelInfo['surfaceUrl'],'w'=>400,'h'=>225);
			$rst = $this->base->api('core/pic/get',$param);
			if($rst['Flag'] == 100){
				$this->channelInfo['surfaceUrl'] = $rst['pic'];
			}
		}
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
		$this->assign('actionType','channel');
		$this->assign('channelId',$this->channelId);
		$this->assign('userInfo',$this->userInfo);
		$this->assign('channelInfo',$this->channelInfo);
		$this->assign('serviceInfo',$this->serviceInfo);
	}

	public function actionIndex(){
		$this->end(array('runtime'=>false,'tplfile'=>'channel/index.html'));
	}
	
	public function actionEdit(){
		$title = $_POST['title'];
		
		if($title == ''){
			$this->showMsg('请输入频道主题',-1);
		}
		if(mb_strlen($title,'UTF-8') > 20){
			$this->showMsg('频道主题不能超过20个字',-1);
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

		$param = array('wspKey'=>$this->userInfo['key'],'wxUid'=>$this->userInfo['unionid'],'wxOpenId'=>$this->userInfo['openid'],'id'=>$this->channelId,'title'=>$title,'pic'=>$pic,'picType'=>$picType);
		$rst = $this->base->api('wsp/wspService/channelEdit',$param);
        if($rst['Flag'] == 100){
        	$this->showMsg('修改成功！','index.php?r=channel/index&channelId='.$this->channelId);
        }
        else{
        	$this->showMsg($rst['FlagString'],'index.php?r=channel/index&channelId='.$this->channelId);
        }
	}
	
}