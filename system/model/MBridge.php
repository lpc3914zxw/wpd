<?php
class MBridge {
	
	private function __construct() {}
	
	private function __clone() {}
	
	private static $instance = false;
	
	public static function connect($options = array(), $engine = 'wsp') {
		if ($engine == 'wsp') {
			require_once dirname(__FILE__).'/bridgePack/_wsp.class.php';
			if (!(self :: $instance === _wsp::get_instance())) {
				self :: $instance = _wsp::get_instance();
				self :: $instance->set_options($options);
			}
		}
		elseif ($engine == 'aodianyun') {
			require_once dirname(__FILE__).'/bridgePack/_aodianyun.class.php';
			if (!(self :: $instance === _aodianyun::get_instance())) {
				self :: $instance = _aodianyun::get_instance();
				self :: $instance->set_options($options);
			}
		}
		return self :: $instance;
	}
}
