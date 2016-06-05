<?php
return array (
    'mysql' => array(
        'dbhost'	=> $_SERVER['kkyooDB_HOST'],
        'dbuser'	=> $_SERVER['kkyooDB_NAME'],
        'dbpw'		=> $_SERVER['kkyooDB_PASS'],
        'dbport'	=> $_SERVER['kkyooDB_PORT'],
        'dbname'	=> 'yun_square',
        'dbcharset' => 'utf8',
        'pconnect'	=> 0,
        'debug'		=> false,
        'tablepre'	=> '',
        'time'		=> ''
    ),
    'mongo' => array (
        'dbhost'    => $_SERVER['mongoDB_HOST'],
        'dbuser'    => $_SERVER['mongoDB_NAME'],
        'dbpw'      => $_SERVER['mongoDB_PASS'],
        'dbport'    => $_SERVER['mongoDB_PORT'],
        'dbname'    => 'ktv',
        'dbcharset' => 'utf8',
        'debug'     => true,
    )
);