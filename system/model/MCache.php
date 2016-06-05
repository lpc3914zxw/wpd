<?php
class MCache {
	
	private function __construct() {}
	
	private function __clone() {}
	
	private static $instance = false;
	
	public static function connect($options = array(), $engine = 'json_file') {
		if ($engine == 'json_file') {
			include_once dirname(__FILE__).'/cachePack/_json_file.class.php';
			if (!(self :: $instance === _json_file::get_instance())) {
				self :: $instance = _json_file::get_instance();
				self :: $instance->set_options($options);
			}
		}
		return self :: $instance;
	}
}
