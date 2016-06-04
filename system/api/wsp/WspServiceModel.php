<?php
class WspServiceModel extends CModel{

	private $wspHandle;

	public function beforeAction(){
		$this->wspHandle = CModel::modelHandle('WSP');
	}

    public function apiGetServiceByUser(){
    	if(empty($this->param['uid'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'services/'.$this->param['uid'];
		$rst = $this->wspHandle->run($url);
		if($rst['Flag'] != 100){
			$msg = json_decode($rst['Info'],true);
			if($msg['code'] == 500){
				return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>'');
			}
			else{
				return array('Flag'=>101,'FlagString'=>'查询失败');
			}
		}
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$rst['Info']);
    }

    public function apiGetServiceByDomain(){
    	if(empty($this->param['domain'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'services/domain/'.$this->param['domain'];
		$rst = $this->wspHandle->run($url);
		if($rst['Flag'] != 100){
			$msg = json_decode($rst['Info'],true);
			if($msg['code'] == 500){
				return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>'');
			}
			else{
				return array('Flag'=>101,'FlagString'=>'查询失败');
			}
		}
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$rst['Info']);
    }

    public function apiGetServiceBySecKey(){
    	if(empty($this->param['secKey'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'services/key/'.$this->param['secKey'];
		$rst = $this->wspHandle->run($url);
		if($rst['Flag'] != 100){
			$msg = json_decode($rst['Info'],true);
			if($msg['code'] == 500){
				return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>'');
			}
			else{
				return array('Flag'=>101,'FlagString'=>'查询失败');
			}
		}
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$rst['Info']);
    }

    public function apiLoginChannel(){
    	if(empty($this->param['wxOpenId']) || empty($this->param['wxUid']) || empty($this->param['wxNick']) || empty($this->param['wspKey'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$wxOpenId = $this->param['wxOpenId'];
		$wxUid = $this->param['wxUid'];
		$wxNick = $this->param['wxNick'];
		$wspKey = $this->param['wspKey'];

		$data = array(
			'wspKey'=>strval($wspKey),
			'nick'=>strval(urlencode($wxNick))
		);

		$url = 'channels/login';
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$wspKey,$wxUid,$wxOpenId);
		return $rst;
    }
	
	public function apiActiveParties(){
		if(empty($this->param['id']) || empty($this->param['wspKey'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/active?cid='.$this->param['id'];
		$rst = $this->wspHandle->run($url,array(),'','GET',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>102,'FlagString'=>'查询失败','Info'=>array());
		}
		$info = $rst['Info'];
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$info);
	}
	
	public function apiCutMic(){
		if(empty($this->param['channelId']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$channelId = $this->param['channelId'];
		$url = '/channels/'.$channelId.'/lss';
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	//添加lcsp
	public function apiAddLcps(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['channelId']) || empty($this->param['lcpsName']) || empty($this->param['expire'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$data = array(
			'lcpsExpire'=>intval($this->param['expire'])
		);

		$url = 'channels/'.$this->param['channelId'].'/lcps/'.$this->param['lcpsName'];
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	//删除lcsp
	public function apiRemoveLcps(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['channelId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$url = 'channels/'.$this->param['channelId'].'/lcps';
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiAddParties(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['channelId']) || $this->param['title'] == '' || mb_strlen($this->param['title'],'UTF-8') > 20 || empty($this->param['startTime']) || empty($this->param['endTime']) || $this->param['startTime'] >= $this->param['endTime'] || empty($this->param['pic']) || empty($this->param['picType'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		//上传图片
		$index = md5(uniqid(mt_rand(0,1000)));
		$param = array('Type'=>'wxcarousel','Index'=>$index,'pic'=>$this->param['pic'],'picType'=>$this->param['picType']);
		$rst = $this->base->api('core/pic/upload',$param);
		if($rst['Flag'] != 100){
			return array('Flag'=>102,'FlagString'=>'图片上传失败，请重试！');
		}
		$surfaceUrl = $index;
		if(!empty($rst['upload'])){
			$surfaceUrl = $rst['upload'];
		}

		//关闭当前正在进行的活动
		$param = array('id'=>$this->param['channelId'],'wspKey'=>$this->param['wspKey'],'wxUid'=>$this->param['wxUid'],'wxOpenId'=>$this->param['wxOpenId']);
		$activePartiesInfo = $this->base->api('wsp/wspService/activeParties',$param);
		$activePartiesInfo = $activePartiesInfo['Info'];
		if(!empty($activePartiesInfo) && $activePartiesInfo['state'] != 3){
			$param = array('id'=>$activePartiesInfo['partyId'],'wspKey'=>$this->param['wspKey'],'wxUid'=>$this->param['wxUid'],'wxOpenId'=>$this->param['wxOpenId']);
			$rst = $this->base->api('wsp/wspService/closeParties',$param);
			if($rst['Flag'] != 100){
				return array('Flag'=>103,'FlagString'=>'关闭当前正在进行的活动失败，请重试！');
			}
		}
		
		$url = 'parties/auto';
		$data = array(
			'channelId'=>intval($this->param['channelId']),
			'title'=>strval($this->param['title']),
			'startTime'=>strval($this->param['startTime']),
			'endTime'=>strval($this->param['endTime']),
			'surfaceUrl'=>strval($surfaceUrl)
		);
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiCloseParties(){
		if(empty($this->param['id']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/op';
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		$info = $rst['Info'];
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'操作失败','Info'=>$info);
		}
		return array('Flag'=>100,'FlagString'=>'操作成功','Info'=>$info);
	}

	public function apiPartyList(){
		if(empty($this->param['wspKey'])|| empty($this->param['channelId']) || empty($this->param['page'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$num = empty($this->param['num']) ? 7 : $this->param['num'];
		
		$skip = ($this->param['page'] - 1) * $num;
		$url = 'parties?cid=' . $this->param['channelId'] . '&skip=' . $skip . '&num='.$num;
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'GET',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'查询失败','Info'=>array());
		}
		$info = $rst['Info'];
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$info);
	}
	
	public function apiPartyInfo(){
		if(empty($this->param['id']) || empty($this->param['wspKey'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'];
		$rst = $this->wspHandle->run($url,array(),'','GET',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'查询失败','Info'=>$rst);
		}
		$info = $rst['Info'];
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$info);
	}

	public function apiDeleteParties(){
		if(empty($this->param['id']) || empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$param = array('id'=>$this->param['id'],'wspKey'=>$this->param['wspKey'],'wxUid'=>$this->param['wxUid'],'wxOpenId'=>$this->param['wxOpenId']);
		$partyInfo = $this->base->api('wsp/wspService/partyInfo',$param);
		if(empty($partyInfo['Info'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		if(!empty($partyInfo['Info']['surfaceUrl'])){
			$param = array('Type'=>'wxcarousel','Index'=>$partyInfo['Info']['surfaceUrl']);
			$rst = $this->base->api('core/pic/delete',$param);
			if($rst['Flag'] != 100){
				return array('Flag'=>101,'FlagString'=>'图片删除失败','Info'=>array());
			}
		}

		$url = 'parties/'.$this->param['id'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		$info = $rst['Info'];
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'操作失败','Info'=>$info);
		}
		return array('Flag'=>100,'FlagString'=>'操作成功','Info'=>$info);
	}

	public function apiEditParty(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['id']) || $this->param['title'] == '' || mb_strlen($this->param['title'],'UTF-8') > 20){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$param = array('id'=>$this->param['id'],'wspKey'=>$this->param['wspKey'],'wxUid'=>$this->param['wxUid'],'wxOpenId'=>$this->param['wxOpenId']);
		$partyInfo = $this->base->api('wsp/wspService/partyInfo',$param);
		if(empty($partyInfo['Info'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$partyInfo = $partyInfo['Info'];
		$surfaceUrl = $partyInfo['surfaceUrl'];
		//上传图片
		if(!empty($this->param['pic']) && !empty($this->param['picType'])){
			//上传
			$index = md5(uniqid(mt_rand(0,1000)));
			$param = array('Type'=>'wxcarousel','Index'=>$index,'pic'=>$this->param['pic'],'picType'=>$this->param['picType']);
			$rst = $this->base->api('core/pic/upload',$param);
			if($rst['Flag'] != 100){
				return array('Flag'=>102,'FlagString'=>'图片上传失败，请重试！');
			}
			$surfaceUrl = $index;
			if(!empty($rst['upload'])){
				$surfaceUrl = $rst['upload'];
			}
			//删除原图
			if(!empty($partyInfo['surfaceUrl'])){
				$param = array('Type'=>'wxcarousel','Index'=>$partyInfo['surfaceUrl']);
				$rst = $this->base->api('core/pic/delete',$param);
				if($rst['Flag'] != 100){
					return array('Flag'=>103,'FlagString'=>'原图片删除失败','Info'=>array());
				}
			}
		}
		
		$data = array(
			'title'=>strval($this->param['title']),
			'surfaceUrl'=>strval($surfaceUrl)
		);
		$url = 'parties/'.$this->param['id'];
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'PATCH',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiVodEdit(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['partyId']) || empty($this->param['vodId']) || !isset($this->param['state'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}		
		$data = array(
			'state'=>intval($this->param['state'])
		);
		$url = 'parties/'.$this->param['partyId'].'/vods/'.$this->param['vodId'];
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'PATCH',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiVodDelete(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['partyId']) || empty($this->param['vodId'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$url = 'parties/'.$this->param['partyId'].'/vods/'.$this->param['vodId'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'DELETE',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiVodAdd(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['partyId']) || empty($this->param['vodId']) || empty($this->param['url']) || empty($this->param['m3u8']) || empty($this->param['start']) || empty($this->param['end']) || empty($this->param['title']) || empty($this->param['surfaceUrl'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$data = array(
			'url'=>strval($this->param['url']),
			'start'=>intval($this->param['start']),
			'end'=>intval($this->param['end']),
			'title'=>strval(addslashes($this->param['title'])),
			'm3u8'=>strval($this->param['m3u8']),
			'surfaceUrl'=>strval($this->param['surfaceUrl'])
		);
		
		$url = 'parties/'.$this->param['partyId'].'/vods/'.$this->param['vodId'];
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'POST',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiUserListByChannel(){
		if(empty($this->param['channelId']) || empty($this->param['page'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$num = empty($this->param['num']) ? 10 : $this->param['num'];
		$skip = ($this->param['page'] - 1) * $num;
		$url = 'users?cid='.$this->param['channelId'].'&skip='.$skip.'&num='.$num;
		$rst = $this->wspHandle->run($url,array(),array('Content-Type: application/json'),'GET',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}

	public function apiChannelEdit(){
		if(empty($this->param['wspKey']) || empty($this->param['wxUid']) || empty($this->param['wxOpenId']) || empty($this->param['id']) || $this->param['title'] == '' || mb_strlen($this->param['title'],'UTF-8') > 20){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}

		$param = array('id'=>$this->param['id'],'wspKey'=>$this->param['wspKey'],'wxUid'=>$this->param['wxUid'],'wxOpenId'=>$this->param['wxOpenId']);
		$channelInfo = $this->base->api('wsp/wspService/channelInfo',$param);
		if(empty($channelInfo['Info'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		$channelInfo = $channelInfo['Info'];
		$surfaceUrl = $channelInfo['surfaceUrl'];
		//上传图片
		if(!empty($this->param['pic']) && !empty($this->param['picType'])){
			//上传
			$index = md5(uniqid(mt_rand(0,1000)));
			$param = array('Type'=>'wxcarousel','Index'=>$index,'pic'=>$this->param['pic'],'picType'=>$this->param['picType']);
			$rst = $this->base->api('core/pic/upload',$param);
			if($rst['Flag'] != 100){
				return array('Flag'=>102,'FlagString'=>'图片上传失败，请重试！');
			}
			$surfaceUrl = $index;
			if(!empty($rst['upload'])){
				$surfaceUrl = $rst['upload'];
			}
			//删除原图
			if(!empty($channelInfo['surfaceUrl'])){
				$param = array('Type'=>'wxcarousel','Index'=>$channelInfo['surfaceUrl']);
				$rst = $this->base->api('core/pic/delete',$param);
				if($rst['Flag'] != 100){
					return array('Flag'=>103,'FlagString'=>'原图片删除失败','Info'=>array());
				}
			}
		}
		
		$data = array(
			'title'=>strval($this->param['title']),
			'surfaceUrl'=>strval($surfaceUrl)
		);
		$url = 'channels/'.$this->param['id'];
		$rst = $this->wspHandle->run($url,$data,array('Content-Type: application/json'),'PATCH',$this->param['wspKey'],$this->param['wxUid'],$this->param['wxOpenId']);
		return $rst;
	}
	
	public function apiChannelInfo(){
		if(empty($this->param['wspKey']) || empty($this->param['id'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'channels/'.$this->param['id'];
		$rst = $this->wspHandle->run($url,array(),'','GET',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'查询失败','Info'=>array());
		}
		$info = $rst['Info'];
		return array('Flag'=>100,'FlagString'=>'查询成功','Info'=>$info);
	}
	
	public function apiAddPartyPv(){
		if(empty($this->param['wspKey']) || empty($this->param['id'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/pv';
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'POST',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'操作失败','Info'=>$rst['Info']);
		}
		return array('Flag'=>100,'FlagString'=>'操作成功','Info'=>$rst['Info']);
	}
	
	public function apiCheckUserPraises(){
		if(empty($this->param['wspKey']) || empty($this->param['id']) || empty($this->param['uin'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/praises/'.$this->param['uin'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'GET',$this->param['wspKey']);
		return $rst;
	}
	
	public function apiAddPartyPraises(){
		if(empty($this->param['wspKey']) || empty($this->param['id']) || empty($this->param['uin'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/praises/'.$this->param['uin'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'POST',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'操作失败','Info'=>$rst['Info']);
		}
		return array('Flag'=>100,'FlagString'=>'操作成功','Info'=>$rst['Info']);
	}
	
	public function apiAddPartyShares(){
		if(empty($this->param['wspKey']) || empty($this->param['id']) || empty($this->param['uin'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/shares/'.$this->param['uin'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'POST',$this->param['wspKey']);
		if($rst['Flag'] != 100){
			return array('Flag'=>101,'FlagString'=>'操作失败','Info'=>$rst['Info']);
		}
		return array('Flag'=>100,'FlagString'=>'操作成功','Info'=>$rst['Info']);
	}
	
	public function apiCheckUserShares(){
		if(empty($this->param['wspKey']) || empty($this->param['id']) || empty($this->param['uin'])){
			return array('Flag'=>101,'FlagString'=>'参数错误');
		}
		
		$url = 'parties/'.$this->param['id'].'/shares/'.$this->param['uin'];
		$rst = $this->wspHandle->run($url,'',array('Content-Type: application/json'),'GET',$this->param['wspKey']);
		return $rst;
	}
	
}