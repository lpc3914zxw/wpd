<?php
/**
 * 奥点云框架核心类创建web实例
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CWebApplication{

	private $moduleConfig;

	public function __construct($module){
		$this->moduleConfig = CConfig::init()->getConfig('module',$module);
	}

	public function run(){
		$r = empty($this->moduleConfig['defaultAction']) ? '' : $this->moduleConfig['defaultAction'];
		$route = CRoute::init()->getRequest($r);
		if($route === null){
			throw new CException(Base::init()->getMessage('base','Module is null'));
		}

		CRoute::init()->routeApplication($this->moduleConfig['controllerDir'],$route);
		$run = new $route['controller']($this->moduleConfig, $route);
		if(method_exists($run, 'beforeAction')){
			$run->beforeAction();
		}
		$run->$route['action']();
	}

	public function runAction($r = ''){
		$route = CRoute::init()->getRequest($r);
		if($route === null){
			throw new CException(Base::init()->getMessage('base','Module is null'));
		}

		CRoute::init()->routeApplication($this->moduleConfig['controllerDir'],$route);
		$run = new $route['controller']($this->moduleConfig, $route);
		$run->$route['action']();
	}

}