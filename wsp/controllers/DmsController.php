<?php

class DmsController extends CController{
	private $secKey;
	private $serviceInfo;

	public function beforeAction(){
		$environmentInfo = $this->checkEnvironment();
		if(empty($environmentInfo)){
			new CHttpHeader('404');
		}
		$this->serviceInfo = $environmentInfo['service'];
		if($this->serviceInfo['state'] != 1 || $this->serviceInfo['freeze'] != 0){
			new CHttpHeader('404');
		}
		$this->secKey = $this->serviceInfo['secKey'];
	}
	
	public function actionPublish(){
		$partyId = empty($_POST['partyId']) ? '' : $_POST['partyId'];
		$data = empty($_POST['data']) ? '' : $_POST['data'];
		if(empty($partyId) || empty($data['uid']) || empty($data['nick']) || empty($data['ava']) || empty($data['content'])){
			$rst = array(
				'Flag'=>101,
				'FlagString'=>'参数失败'
			);
			echo json_encode($rst);
			exit;
		}
		$data['time'] = time();
		$data['body'] = '';
		$param = array('wspKey'=>$this->secKey,'data'=>$data,'partyId'=>$partyId);
		$rst = $this->base->api('wsp/dms/dmsPublish',$param);
		echo json_encode($rst);
		exit;
	}

	public function actionGetHistoryMessage(){
		$partyId = empty($_POST['partyId']) ? '' : $_POST['partyId'];
		$page = empty($_POST['page']) ? '' : $_POST['page'];
		$num = empty($_POST['num']) ? '' : $_POST['num'];
		if(empty($partyId) || empty($page) || empty($num)){
			$rst = array(
				'Flag'=>101,
				'FlagString'=>'参数失败'
			);
			echo json_encode($rst);
			exit;
		}
		$param = array('wspKey'=>$this->secKey,'partyId'=>$partyId,'page'=>$page,'num'=>$num);
		$rst = $this->base->api('wsp/dms/getHistoryMessage',$param);
		if($rst['Flag'] == 100){
			echo json_encode(array('Flag'=>100,'List'=>$rst['Info']));
			exit;
		}
		else{
			echo json_encode(array('Flag'=>100,'List'=>array()));
			exit;
		}
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