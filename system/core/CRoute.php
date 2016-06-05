<?php
/**
 * 奥点云框架核心类路由器
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CRoute{

	private static $instance;

	private function __clone(){}

	private function __construct(){}

	public static function init(){
		if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
	}

	public function getRequest($r = ''){
		if(empty($_GET['r']) && empty($r)){
			return null;
		}

		$r = empty($_GET['r']) ? $r : $_GET['r'];
    	$r = explode('/',$r);
		$exp = "/^[a-zA-Z0-9_]{1,30}$/";
		if(!preg_match($exp,$r[0]) && !preg_match($exp,$r[1])){
			return null;
		}

		return array('controller'=>ucfirst($r[0]) . CONTROLLER_EXT, 'action'=>ACTION_EXT . ucfirst($r[1]));
	}

	public function routeApplication($dir, array $route){
		$controller = $route['controller'];
		$action = $route['action'];

		if(class_exists($controller)){
			return;
		}

		$path = $dir . $controller . '.php';
		if(!file_exists($path)){
			throw new CException(Base::init()->getMessage('base','Controller {controller} is null',array('{controller}'=>$controller)));
		}

		require $path;
		if(!class_exists($controller)){
			throw new CException(Base::init()->getMessage('base','Controller {controller} is null',array('{controller}'=>$controller)));
		}
		if(!method_exists($controller,$action)){
			throw new CException(Base::init()->getMessage('base','Controller {controller}\'s {action} is null',array('{controller}'=>$controller,'{action}'=>$action)));
		}
	}

	public function getApi($api){
		if(empty($api)){
			return null;
		}

		$api = explode('/',$api);
		if(count($api) != 3){
			return null;
		}
		$exp = "/^[a-zA-Z0-9_]{1,30}$/";
		if(!preg_match($exp,$api[0]) && !preg_match($exp,$api[1]) && !preg_match($exp,$api[2])){
			return null;
		}

		return array('module'=>$api[0], 'model'=>ucfirst($api[1]) . MODEL_EXT, 'api'=>API_EXT . ucfirst($api[2]));
	}

	public function routeApi(array $route){
		$module = $route['module'];
		$model = $route['model'];
		$api = $route['api'];

		if(class_exists($model)){
			return;
		}

		$path = API_PATH . '/' . $module . '/' . $model . '.php';
		if(!file_exists($path)){
			throw new CException(Base::init()->getMessage('base','Model {model} is null',array('{model}'=>$model)));
		}

		require $path;
		if(!class_exists($model)){
			throw new CException(Base::init()->getMessage('base','Model {model} is null',array('{model}'=>$model)));
		}
		if(!method_exists($model,$api)){
			throw new CException(Base::init()->getMessage('base','Model {model}\'s {api} is null',array('{model}'=>$model,'{api}'=>$api)));
		}
	}

}