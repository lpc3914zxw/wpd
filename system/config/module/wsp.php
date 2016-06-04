<?php
/**
 * 奥点云框架微频道配置文件
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */
if(!defined('WSP_PIC_PATH')){
	define('WSP_PIC_PATH', __BASE__ . '/upload/pic');
}
if(!defined('WSP_ASSETS_PATH')){
	define('WSP_ASSETS_PATH', SYSTEM_HOST . '/wsp/assets/');
}
return array(
	'moduleName' => 'wsp',
	'defaultAction' => 'layout/livestream',
	'controllerDir' => __BASE__ . '/wsp/controllers/',
	'template' => array(
		'template_dir'  => __BASE__ . '/themes/tpl/wsp/',
		'cache_dir'	=> __BASE__ . '/themes/compile/wsp/',
		'cache_lifetime'=> 3600,
		'debug'		=> false
	)
);