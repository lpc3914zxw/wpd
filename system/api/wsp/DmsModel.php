<?php
class DmsModel extends CModel{

	private $wspHandle;

	public function beforeAction(){
		$this->wspHandle = CModel::modelHandle('WSP');
	}
	
	public function apiAddDmsUser(){
		if(empty($this->param['wspKey']) || empty($this->param['partyId']) || empty($this->param['uin']) || empty($this->param['nick']) || empty($this->param['headimgurl'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$data = array(
			'partyId'=>intval($this->param['partyId']),
			'uid'=>strval($this->param['uin']),
			'nick'=>strval($this->param['nick']),
			'url'=>strval($this->param['headimgurl'])
		);
		$url = 'users/auto';
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiDmsPublish(){
		if(empty($this->param['wspKey']) || empty($this->param['partyId']) || !is_array($this->param['data']) || empty($this->param['data']) || empty($this->param['data']['uid']) || empty($this->param['data']['nick']) || empty($this->param['data']['ava']) || empty($this->param['data']['content'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$data = array(
			'uid'=>strval($this->param['data']['uid']),
			'nick'=>strval($this->param['data']['nick']),
			'ava'=>strval($this->param['data']['ava']),
			'url'=>strval($this->param['data']['url']),
			'content'=>strval($this->param['data']['content']),
			'time'=>intval($this->param['data']['time']),
			'body'=>strval($this->param['data']['body']),
		);
		$url = 'parties/'.$this->param['partyId'].'/chats';
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiGetHistoryMessage(){
		if(empty($this->param['wspKey']) || empty($this->param['partyId']) || empty($this->param['page']) || empty($this->param['num'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$num = $this->param['num'];
		$skip = ($this->param['page'] - 1) * $num;
		
		$url = 'parties/'.$this->param['partyId'].'/chats/histories?skip='.$skip.'&num='.$num;
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'GET',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiDeleteDmsMessage(){
		if(empty($this->param['partyId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'parties/'.$this->param['partyId'].'/chats';
		$data = array(
			'uid'=>strval($this->param['uid']),
			'nick'=>strval($this->param['nick']),
			'ava'=>strval($this->param['ava']),
			'url'=>strval($this->param['url']),
			'content'=>strval($this->param['content']),
			'time'=>intval($this->param['time']),
			'body'=>strval($this->param['body']),
		);
		
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiIsBlack(){
		if(empty($this->param['channelId']) || empty($this->param['wspKey']) || empty($this->param['openid'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/blacklists/'.$this->param['openid'];
		$rst = $this->wspHandle->run($url,'',array(),'GET',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiGetBlacklists(){
		if(empty($this->param['channelId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/blacklists';
		if(isset($this->param['page'])){
			$num = empty($this->param['num']) ? 10 : $this->param['num'];
			$skip = ($this->param['page']-1) * $num;
			$url = $url.'?skip='.$skip.'&num='.$num;
		}
		$rst = $this->wspHandle->run($url,'',array(),'GET',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'查询失败','List'=>array());
		}
		$listTmp = $rst['Info']['list'];
		$list = array();
		if(!empty($listTmp)){
			foreach($listTmp as $key=>$val){
				$list[$val['uid']] = $val;
			}
		}
		return array('Flag'=>100,'FlagString'=>'查询成功','List'=>$list);
	}
	
	public function apiAddBlacklists(){
		if(empty($this->param['channelId']) || empty($this->param['uid']) || !isset($this->param['nick']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/blacklists/'.$this->param['uid'];
		$data = array(
			'nick'=>$this->param['nick']
		);
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiDeleteBlacklists(){
		if(empty($this->param['channelId']) || empty($this->param['uid']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/blacklists/'.$this->param['uid'];
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiIsGaps(){
		if(empty($this->param['channelId']) || empty($this->param['wspKey']) || empty($this->param['openid'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/gaps/'.$this->param['openid'];
		$rst = $this->wspHandle->run($url,'',array(),'GET',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiGetGaps(){
		if(empty($this->param['channelId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/gaps';
		if(isset($this->param['page'])){
			$num = empty($this->param['num']) ? 10 : $this->param['num'];
			$skip = ($this->param['page']-1) * $num;
			$url = $url.'?skip='.$skip.'&num='.$num;
		}
		
		$rst = $this->wspHandle->run($url,'',array(),'GET',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'查询失败','List'=>array());
		}
		$listTmp = $rst['Info']['list'];
		$list = array();
		if(!empty($listTmp)){
			foreach($listTmp as $key=>$val){
				$list[$val['uid']] = $val;
			}
		}
		return array('Flag'=>100,'FlagString'=>'查询成功','List'=>$list);
	}
	
	public function apiAddGaps(){
		if(empty($this->param['channelId']) || empty($this->param['uid']) || !isset($this->param['nick']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/gaps/'.$this->param['uid'];
		$data = array(
			'nick'=>$this->param['nick']
		);
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiDeleteGaps(){
		if(empty($this->param['channelId']) || empty($this->param['uid']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'channels/'.$this->param['channelId'].'/gaps/'.$this->param['uid'];
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiCloseDms(){
		if(empty($this->param['partyId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'parties/'.$this->param['partyId'].'/chatopt';
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiOpenDms(){
		if(empty($this->param['partyId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'parties/'.$this->param['partyId'].'/chatopt';
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'PUT',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
}