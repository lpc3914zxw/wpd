<?php
/**
 * 奥点云框架核心类载入配置文件
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CConfig{
	
	private static $instance;
	private static $files = array();

	private function __clone(){}

	private function __construct(){}

	public static function init(){
		if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
	}

	public function getConfig($type, $name, $isReturn = true){
		$path = CONFIG_PATH  . '/' . $type . '/' . $name . '.php';
		if(isset($files[$type][$name])){
			return;
		}
		if(file_exists($path)){
			if($isReturn === true){
				$config = require $path;
				return $config;
			}
			else{
				require $path;
				self::$files[$type][$name] = true;
			}
		}
		else{
			new CHttpHeader('500');
			throw new CException(Base::init()->getMessage('base','Config File {type}/{name} not find',array('{type}'=>$type,'{name}'=>$name)));
		}
	}

}