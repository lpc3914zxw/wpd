<?php
/**
 * 奥点云框架核心类数据模型
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CModel{

	protected $base;
	protected $route;
	protected $param;
	private static $modelItem = array('MYSQL'=>'db','MONGO'=>'db','WSP'=>'bridge','AODIANYUN'=>'bridge','JSON_FILE'=>'cache');
	protected static $connectItem = array();

	public function __construct(array $route = array(), array $param = array()){
		$this->base = Base::init();
		$this->route = $route;
		$this->param = $param;
	}

	public static function modelHandle($model){
		if(!isset(self::$modelItem[$model])){
			throw new CException(Base::init()->getMessage('base','Model {model} is undefined',array('{model}'=>$model)));
		}

		$type = self::$modelItem[$model];
		$model = strtolower($model);
		$modelConfig = CConfig::init()->getConfig('model',$type);
		$modelConfig = $modelConfig[$model];

		if(isset(self::$connectItem[$model])){
			return self::$connectItem[$model];
		}

		switch($type){
			case 'db':
				self::$connectItem[$model] = MDb::connect($modelConfig, $model);
				break;
			case 'cache':
				self::$connectItem[$model] = MCache::connect($modelConfig, $model);
				break;
			case 'bridge':
				self::$connectItem[$model] = MBridge::connect($modelConfig, $model);
				break;
			default:
				break;
		}
		return self::$connectItem[$model];
	}

	public static function encrypt($string = '') {
	    $skey = array_reverse(str_split(CRYPT_KEY));
	    $strArr = str_split(base64_encode($string));
	    $strCount = count($strArr);
	    foreach ($skey as $key => $value) {
	        $key < $strCount && $strArr[$key].=$value;
	    }
	    return str_replace('=', 'O0O0O', join('', $strArr));
	}

	public static function decrypt($string = '') {
	    $skey = array_reverse(str_split(CRYPT_KEY));
	    $strArr = str_split(str_replace('O0O0O', '=', $string), 2);
	    $strCount = count($strArr);
	    foreach ($skey as $key => $value) {
	        $key < $strCount && $strArr[$key] = rtrim($strArr[$key], $value);
	    }
	    return base64_decode(join('', $strArr));
	}

	public static function curl($query_url, $data = '', $is_url = true, $timeout = 10) {
		$url = @parse_url($query_url);
		if(empty($url)) {
			return 'Request URL can not be empty!';
		}
		$url['path'] = empty($url['path'])? '/' : $url['path'];
		$url['port'] = empty($url['port'])? 80  : $url['port'];
		$scheme = $url['scheme'];
		$method = 'GET';
		$host = $is_url == true ? $url['host'] : gethostbyname($url['host']);
		$port = $url['port'];
		$path = $url['path'] . (isset($url['query'])? '?' . $url['query'] : '') . (isset($url['fragment'])? '#' . $url['fragment'] : '');
		
		if(!empty($data) || is_array($data)) {
			$content = is_array($data) ? http_build_query($data,'','&') : $data;
			$method = 'POST';
		}
		$query_url = $scheme.'://'.$host.$path;
		
		//变量初始化
		$cookie = empty($_SERVER['HTTP_COOKIE']) ? null : $_SERVER['HTTP_COOKIE'];
		$useragent = empty($_SERVER['HTTP_USER_AGENT']) ? null : $_SERVER['HTTP_USER_AGENT'];
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$query_url);
		curl_setopt($ch,CURLOPT_PORT,$port);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		curl_setopt($ch,CURLOPT_AUTOREFERER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch,CURLOPT_FRESH_CONNECT,true);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_USERAGENT,$useragent);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
	        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if($method == 'POST') {
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$content);
		}
		//execute post
		$response = curl_exec($ch);
		//get response code
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//close connection 
		curl_close($ch);
		//return result
		if($response_code == 200) {
			return $response;
		} else {
			return false;
		}
	}

}