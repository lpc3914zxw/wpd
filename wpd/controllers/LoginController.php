<?php

class LoginController extends CController{
	
	public function actionIndex(){
		$environmentInfo = $this->checkEnvironment();
		if(empty($environmentInfo)){
			new CHttpHeader('404');
		}
		$serviceInfo = $environmentInfo['service'];
		if($serviceInfo['state'] != 1 || $serviceInfo['freeze'] != 0){
			new CHttpHeader('404');
		}
		$secKey = $serviceInfo['secKey'];

		if(empty($serviceInfo['bgUrl'])){
			$serviceInfo['bgUrl'] = '';
		}
		else{
			$param = array('Type'=>'wxcarousel','Index'=>$this->moduleConfig['picPrefix']['wspuserbg'].$serviceInfo['bgUrl']);
			$rst = $this->base->api('core/pic/get',$param);
			if($rst['Flag'] == 100){
				$serviceInfo['bgUrl'] = $rst['pic'];
			}
			else{
				$serviceInfo['bgUrl'] = '';
			}
		}

		if(empty($serviceInfo['logoUrl'])){
			$serviceInfo['logoUrl'] = '';
		}
		else{
			$param = array('Type'=>'wxcarousel','Index'=>$this->moduleConfig['picPrefix']['wspuserlogo'].$serviceInfo['logoUrl']);
			$rst = $this->base->api('core/pic/get',$param);
			if($rst['Flag'] == 100){
				$serviceInfo['logoUrl'] = $rst['pic'];
			}
			else{
				$serviceInfo['logoUrl'] = '';
			}
		}

		$topic = strtoupper(md5(uniqid(time()).$secKey));
		$url = urlencode(SYSTEM_HOST.'/wpd/index.php?r=login/wxScan&secKey='.$secKey.'&topic='.$topic);

		$this->begin(array('pagecache'=>false,'template'=>'wpd'));
		$this->assign('serviceInfo',$serviceInfo);
		$this->assign('topic',$topic);
		$this->assign('url',$url);

		if(empty($_GET['show'])){
			$userInfo = $this->base->api('core/sso/getLogin',array());
			if($userInfo['Flag'] == 100){
				if($userInfo['channelInfo']['state'] == 1){
					$this->showMsg('',SYSTEM_HOST.'/wpd/index.php?r=console/index&channelId='.$userInfo['channelInfo']['id']);
				}
				elseif($userInfo['channelInfo']['state'] == 2){
					$this->showMsg('您已成功提交频道创建申请，待审核通过后方进行可操作。',SYSTEM_HOST.'/wpd/index.php?r=login/loginOut&channelId='.$userInfo['channelInfo']['id']);
				}
				elseif($userInfo['channelInfo']['state'] == 0){
					$this->showMsg('您的频道已被关闭，不能进行操作。',SYSTEM_HOST.'/wpd/index.php?r=login/loginOut&channelId='.$userInfo['channelInfo']['id']);
				}
				elseif($userInfo['channelInfo']['state'] == 3){
					$this->showMsg('您的创建频道申请已被否决。',SYSTEM_HOST.'/wpd/index.php?r=login/loginOut&channelId='.$userInfo['channelInfo']['id']);
				}
			}
			else{
				$this->end(array('runtime'=>false,'tplfile'=>'login/index.html'));
			}
		}
		else{
			$this->end(array('runtime'=>false,'tplfile'=>'login/index.html'));
		}
	}

	public function actionWxScan(){
		if(empty($_GET['topic'])){
			new CHttpHeader('404');
		}

		$environmentInfo = $this->checkEnvironment();
		if(empty($environmentInfo)){
			new CHttpHeader('404');
		}

		$environment = $environmentInfo['environment'];
		$serviceInfo = $environmentInfo['service'];
		
		$topic = $_GET['topic'];
		$state = $serviceInfo['secKey'].','.$topic;
		$serviceInfo['wxAppid'] = trim($serviceInfo['wxAppid']);
		if(empty($serviceInfo['wxAppid']) || empty($serviceInfo['wxAppSecret'])){
			die('微信公众号配置错误！');
		}

		$redirect = SYSTEM_HOST.'%2fopenlogin%2fwpdScanBack.php';

		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$serviceInfo['wxAppid'].'&redirect_uri='.$redirect.'&response_type=code&scope=snsapi_userinfo&state='.$state.'&connect_redirect=1#wechat_redirect';
		header("Location:$url");
	}

	public function actionLoginOut(){
		$rst = $this->base->api('core/sso/loginOut',array());
		if($rst['Flag'] != 100){
			$this->showMsg('操作失败，请重试',-1);
		}
		$this->showMsg('',SYSTEM_HOST.'/wpd/index.php?r=login/index');
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
	
}