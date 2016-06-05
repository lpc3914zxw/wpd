<?php
require dirname(dirname(__FILE__)).'/system/Base.php';
//加载核心配置
$base = Base::init();
$base->createWebApplication('wsp');