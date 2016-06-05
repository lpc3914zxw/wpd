<?php
class PicModel extends CModel{
	
	private $pic_host = '';
	
	public function beforeAction(){
		$this->pic_host = 'http://pic.aodianyun.com/aodianyun';
	}

	public function apiUpload(){
		if(!empty($this->param['Type']) && !empty($this->param['Index']) && !empty($this->param['pic']) && !empty($this->param['picType'])){
			if(strpos($this->param['picType'], 'image') === false){
				return array('Flag'=>101,'FlagString'=>'上传图片格式必须为jpg，png，gif格式');
			}
			$imgType = '';
			if($this->param['picType'] == 'image/gif'){
				$imgType = 'gif';
			}
			elseif($this->param['picType'] == 'image/jpeg'){
				$imgType = 'jpg';
			}
			elseif($this->param['picType'] == 'image/pjpeg'){
				$imgType = 'jpg';
			}
			elseif($this->param['picType'] == 'image/png'){
				$imgType = 'png';
			}
			if(empty($imgType)){
				return array('Flag'=>101,'FlagString'=>'上传图片格式必须为jpg，png，gif格式');
			}

			$imgPath = UPLOAD_PIC_PATH . $this->param['Index'] . '.' . $imgType;
			$url = GET_PIC_PATH . $this->param['Index'] . '.' . $imgType;
			$upload = $this->param['Index'] . '.' . $imgType;

			if(is_uploaded_file($this->param['pic'])){ 
				if(!move_uploaded_file($this->param['pic'],$imgPath)){ 
					return array('Flag'=>101,'FlagString'=>'上传失败');
				} 
			} 
			else{ 
				return array('Flag'=>101,'FlagString'=>'上传失败');
			}
			return array('Flag'=>100,'FlagString'=>'上传成功','path'=>$imgPath,'url'=>$url,'upload'=>$upload);
		}
		return array('Flag'=>101,'FlagString'=>'上传失败');
	}
	
	public function apiGet(){
		$exp = "/^[a-zA-Z0-9_\/\-]{1,64}$/";
		if(preg_match($exp,$this->param['Index'])){
			if(!empty($this->param['Type']) && !empty($this->param['Index'])){
				if(empty($this->param['w'])){
					$this->param['w'] = 0;
				}
				if(empty($this->param['h'])){
					$this->param['h'] = 0;
				}
				$pic = $this->pic_host.'/'.$this->param['Type'].'/'.$this->param['Index'].'/'.$this->param['w'].'/'.$this->param['h'];
				return array('Flag'=>100,'FlagString'=>'成功','pic'=>$pic);
			}
			return array('Flag'=>101,'FlagString'=>'参数有误');
		}
		else{
			$pic = GET_PIC_PATH . $this->param['Index'];
			return array('Flag'=>100,'FlagString'=>'成功','pic'=>$pic);
		}
	}

	public function apiDelete(){
		$exp = "/^[a-zA-Z0-9_\/\-]{1,64}$/";
		if(preg_match($exp,$this->param['Index'])){
			if(!empty($this->param['Type']) && !empty($this->param['Index'])){
				$data = array(
					'UPLOAD_KEY' => 'e22488067969afd2c63f722d5727f192',
					'Type'  => $this->param['Type'],
					'Index' => $this->param['Index']
				);
				$rst = json_decode(CModel::curl($this->pic_host.'/delete',$data),true);
				if($rst['rst'] == 100){
					return array('Flag'=>100,'FlagString'=>'删除成功');
				}
			}
			return array('Flag'=>101,'FlagString'=>'删除失败');
		}
		else{
			$pic = UPLOAD_PIC_PATH . $this->param['Index'];
			if(is_file($pic)){
				if(!unlink($pic)){
					return array('Flag'=>101,'FlagString'=>'删除失败');
				}
			}
			return array('Flag'=>100,'FlagString'=>'删除成功');
		}
	}
}
