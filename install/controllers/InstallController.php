<?php

class InstallController extends CController{
	
	public function beforeAction(){
		
	}

	public function actionStep1(){
		include(__BASE__.'/install/tmp/step1.php');
	}
	
	public function actionStep2(){
		$rewrite = empty($_POST['rewrite']) ? 'false' : 'true';
		
		$path = __BASE__.'/system/config/system/base.php';
		$code = file_get_contents($path);
		if(empty($code)){
			$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
		}
		
		$code = str_replace('\'{WSP_REWRITE}\'', $rewrite, $code);
        if(!file_put_contents($path,$code)){
        	$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
		
		include(__BASE__.'/install/tmp/step2.php');
	}

	public function actionStep3(){
		if(empty($_GET['key'])){
			$error = 1;
			$errorMsg = '服务验证失败！';
			include(__BASE__.'/install/tmp/serviceError.php');
			exit;
		}
		$key = $_GET['key'];
		$param = array('secKey'=>$key);
        $serviceInfo = $this->base->api('wsp/wspService/getServiceBySecKey',$param);
        if(empty($serviceInfo['Info'])){
			$error = 1;
			$errorMsg = '服务验证失败！';
            include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        $serviceInfo = $serviceInfo['Info'];
        if($serviceInfo['muiltUser'] != 1){
        	$error = 2;
			$errorMsg = '您还未开通多用户模式！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        if(empty($serviceInfo['wxAppid']) || empty($serviceInfo['wxAppSecret'])){
        	$error = 3;
			$errorMsg = '您还未设置微信公众号！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        if($serviceInfo['state'] != 1 || $serviceInfo['freeze'] != 0){
        	$error = 4;
			$errorMsg = '抱歉。您的服务已被冻结，无法进行安装！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        $path = __BASE__.'/system/config/system/base.php';
		$code = file_get_contents($path);
		if(empty($code)){
			$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
		}
		$arr = array('0','1','2','3','4','5','6','7','8','9','10','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$cryptKey = '';
		for($i = 0; $i <= 7; $i++){
			$cryptKey .= $arr[mt_rand(0,(count($arr)-1))];
		}
		$code = str_replace('{CRYPT_KEY}', $cryptKey, $code);
		$code = str_replace('{WSP_SEC_KEY}', $key, $code);
        if(!file_put_contents($path,$code)){
        	$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
		include(__BASE__.'/install/tmp/step3.php');
		exit;
	}

	public function actionStep4(){
		if(empty($_GET['key'])){
			$error = 1;
			$errorMsg = '服务验证失败！';
			include(__BASE__.'/install/tmp/serviceError.php');
			exit;
		}
		$key = $_GET['key'];
		$param = array('secKey'=>$key);
        $serviceInfo = $this->base->api('wsp/wspService/getServiceBySecKey',$param);
        if(empty($serviceInfo['Info'])){print_r($serviceInfo);phpinfo();exit;
			$error = 1;
			$errorMsg = '服务验证失败！';
            include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        $serviceInfo = $serviceInfo['Info'];
        if($serviceInfo['muiltUser'] != 1){
        	$error = 2;
			$errorMsg = '您还未开通多用户模式！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        if(empty($serviceInfo['wxAppid']) || empty($serviceInfo['wxAppSecret'])){
        	$error = 3;
			$errorMsg = '您还未设置微信公众号！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        if($serviceInfo['state'] != 1 || $serviceInfo['freeze'] != 0){
        	$error = 4;
			$errorMsg = '抱歉。您的服务已被冻结，无法进行安装！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
        $path = __BASE__.'/system/config/system/base.php';
		$code = file_get_contents($path);
		if(empty($code)){
			$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
		}
		$arr = array('0','1','2','3','4','5','6','7','8','9','10','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','~','!','@','#','$','%','^','&','*','(',')','_','+','-','=','|','{','}','[',']',':',';','<','>');
		$cryptKey = '';
		for($i = 0; $i <= 7; $i++){
			$cryptKey .= $arr[mt_rand(0,(count($arr)-1))];
		}
		$code = str_replace('{CRYPT_KEY}', $cryptKey, $code);
		$code = str_replace('{WSP_SEC_KEY}', $key, $code);
        if(!file_put_contents($path,$code)){
        	$error = 5;
			$errorMsg = '配置文件无法写入，请检查您的权限配置！';
	        include(__BASE__.'/install/tmp/serviceError.php');
			exit;
        }
		include(__BASE__.'/install/tmp/step3.php');
		exit;
	}
}