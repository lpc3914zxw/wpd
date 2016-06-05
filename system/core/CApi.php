<?php
/**
 * 奥点云框架核心类数据模型
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CApi{
	
	private static $instance;

 	private function __clone(){}

	private function __construct(){}

	public static function init(){
		if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
	}

	public function run($api, array $param = array()){
		if(empty($api)){
			throw new CException(Base::init()->getMessage('base','Api is null'));
		}
		$route = CRoute::init()->getApi($api);
		if($route === null){
			throw new CException(Base::init()->getMessage('base','Api is null'));
		}

		CRoute::init()->routeApi($route);
		$run = new $route['model']($route, $param);
		if(method_exists($run, 'beforeAction')){
			$run->beforeAction();
		}
		return $run->$route['api']();
	}

}