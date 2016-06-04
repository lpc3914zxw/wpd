<?php
/**
 * 奥点云框架核心配置文件
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

define('CDOMAIN', $_SERVER['HTTP_HOST']);//当前域名
define('MESSAGE_PATH', __BASE__ . '/system/messages');//消息文件路径
define('API_PATH', __BASE__ . '/system/api');//api文件路径
define('SYSTEM_LANGUAGE', 'base');//系统语言
define('CONTROLLER_EXT', 'Controller');//控制器文件扩展名
define('ACTION_EXT', 'action');//动作扩展名
define('MODEL_EXT', 'Model');//模型文件扩展名
define('API_EXT', 'api');//api扩展名
define('CRYPT_KEY', '{CRYPT_KEY}');//加解密密钥
define('WSP_SEC_KEY', '{WSP_SEC_KEY}');//微视频KEY
define('UPLOAD_PIC_PATH', __BASE__ . '/upload/pic/');//图片上传路径
define('GET_PIC_PATH', SYSTEM_HOST . '/upload/pic/');//图片获取路径
define('WSP_REWRITE', '{WSP_REWRITE}');//是否启用伪静态