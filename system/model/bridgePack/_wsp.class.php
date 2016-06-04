<?php
class _wsp {
	
	protected $options = array ();
	
	private $host = null;
	
	private static $instance = false;
	
	private function __construct() {
	}
	
	private function __clone() {
	}
	
	public static function get_instance() {
		if (!self :: $instance instanceof self) {
			self :: $instance = new self();
		}
		return self :: $instance;
	}
	
	public function set_options(array $options) {
		if ($options && is_array($options)) {
			foreach ($options as $name => $value) {
				$this->options[$name] = $value;
			}
		}
		$this->host = $this->options['host'];
	}

	public function run($query_url, $data = '', $header = '', $method = 'GET', $wspKey = '', $wxUid ='' , $wxOpenId ='', $is_url = true, $timeout = 10) {
		if(empty($query_url)) {
			return 'Request URL can not be empty!';
		}
		$url = @parse_url($this->host.$query_url);
		if(empty($url)) {
			return 'Request URL can not be empty!';
		}
		$url['path'] = empty($url['path'])? '/' : $url['path'];
		$url['port'] = empty($url['port'])? 80  : $url['port'];
		$scheme = $url['scheme'];
		$host = $is_url == true ? $url['host'] : gethostbyname($url['host']);
		$port = $url['port'];
		$path = $url['path'] . (isset($url['query'])? '?' . $url['query'] : '') . (isset($url['fragment'])? '#' . $url['fragment'] : '');

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
		curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
		// curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if($method == 'POST' || $method == 'PATCH' || $method == 'DELETE' || $method == 'PUT') {
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
		}
		if(!empty($wspKey) || !empty($wxUid) || !empty($wxOpenId)){
			$authorization = 'Authorization: wsp '.base64_encode('wspKey='.$wspKey.'&wxUid='.$wxUid.'&wxOpenId='.$wxOpenId);
			if(empty($header)){
				$header = array($authorization);
			}
			else{
				$header[] = $authorization;
			}
		}
		if(!empty($header)){
			curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
		}
		//execute post
		$response = curl_exec($ch);
		//get response code
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//close connection 
		curl_close($ch);
		//return result
		if($response_code == 200 || $response_code == 201 || $response_code == 204) {
			$rst = array(
				'Flag'=>100,
				'FlagString'=>'success',
				'Info'=>json_decode(trim($response),true)
			);
		} else {
			$rst = array(
				'Flag'=>110,
				'FlagString'=>'faile',
				'Info'=>trim($response)
			);
		}
		return $rst;
	}

}
