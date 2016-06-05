<?php
/**
 * 奥点云框架微频道配置文件
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */
if(!defined('WPD_ASSETS_PATH')){
	define('WPD_ASSETS_PATH', SYSTEM_HOST . '/wpd/assets/');
}
return array(
	'moduleName' => 'wpd',
	'defaultAction' => 'login/index',
	'controllerDir' => __BASE__ . '/wpd/controllers/',
	'template' => array(
		'template_dir'  => __BASE__ . '/themes/tpl/wpd/',
		'cache_dir'	=> __BASE__ . '/themes/compile/wpd/',
		'cache_lifetime'=> 3600,
		'debug'		=> false
	),
	'picPrefix' => array(
		'wspuserbg' => 'wspuserbg/',
		'wspuserlogo' => 'wspuserlogo/'
	)
);