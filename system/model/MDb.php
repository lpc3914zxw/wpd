<?php
class MDb {
	
	private function __construct() {}
	
	private function __clone() {}
	
	private static $instance = false;
	
	public static function connect($options = array(), $engine = '') {
		if ($engine == 'mysql') {
			if(extension_loaded('mysqli')){
				require_once dirname(__FILE__).'/dbPack/_mysqli.class.php';
				if (!(self :: $instance === _mysqli::get_instance() && self :: $instance->options == $options)) {
					self :: $instance = _mysqli::get_instance();
					self :: $instance->set_options($options);
				}
			}
			else{
				require_once dirname(__FILE__).'/dbPack/_mysql.class.php';
				if (!(self :: $instance === _mysql::get_instance() && self :: $instance->options == $options)) {
					self :: $instance = _mysql::get_instance();
					self :: $instance->set_options($options);
				}
			}
		}
		elseif ($engine == 'mongo') {
			require_once dirname(__FILE__).'/dbPack/_mongo.class.php';
			if (!(self :: $instance === _mongo::get_instance() && self :: $instance->options == $options)) {
				self :: $instance = _mongo::get_instance();
				self :: $instance->set_options($options);
			}
		}
		return self :: $instance;
	}
}
