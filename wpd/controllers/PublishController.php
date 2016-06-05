<?php

class PublishController extends Controller{

	public function actionGetPhonePublishConfig(){
		if(empty($_GET['key'])){
			exit(json_encode(array('error'=>'param error')));
		}
		$param = array('loginKey'=>$_GET['key']);
		$info = $this->main->api('wsp/wspService/getPhonePublishConfig',$param);
		if($info['Flag'] != 100){
			exit(json_encode(array('error'=>'param error')));
		}
		$info = $info['Info'];
		$param = array('Tag'=>'get','Type'=>'wxcarousel','Index'=>$info['pic']);
		$rst = $this->main->api('core/pic/pic',$param);
		if($rst['Flag'] == 100){
			$info['pic'] = $rst['pic'];
		}
		exit(json_encode($info));
	}
	
}