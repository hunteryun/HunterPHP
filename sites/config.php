<?php

//配置: 根目录
$root_dir = '/';

//配置: 开发模式
$hunter_debug = true;

//配置: 数据库
$databases = array(
    'default' => array(
        'host'      => '127.0.0.1',
        'port'      => '3306',
        'database'  => 'dev',
        'username'  => 'root',
        'password'  => 'root',
        'prefix'    => '', //表前缀
        'charset'   => 'utf8mb4',//低于MySQL5.5.3的写utf8
    ),
);

//配置: Log 日志路径
$loggers = array(
    'default' => array(
        'class' => 'Hunter\Core\Logger\FileLogger', //如果记文件日志用FileLogger
        'level' => 'warn',
        'file'  => HUNTER_ROOT . '/sites/logs', //这里指定log目录
        'prefix' => 'hunter',
        'debug' => true,
    ),
);

//配置: 模板引擎
$engines = array(
    'default' => array(
        'engine'      => 'Hunter\Core\Templating\BladeEngine',
        'loader'      => 'Hunter\Core\Templating\Blade\Loader',
        'environment' => 'Hunter\Core\Templating\Blade\Environment',
        'loaderArgs'  => array(HUNTER_ROOT . '/theme'),
        'cacheDir'    => HUNTER_ROOT . '/sites/cache',
        'envArgs'     => array(),
    ),
    'command' => array(
        'engine'      => 'Hunter\Core\Templating\PhpEngine',
        'loader'      => 'Hunter\Core\Templating\Php\Loader',
        'environment' => 'Hunter\Core\Templating\Php\Environment',
        'loaderArgs'  => array(HUNTER_ROOT . '/core/command/templates'),
        'cacheDir'    => '',
        'envArgs'     => array(),
    ),
);

//配置: memcache(d)缓存
$caches = array(
    'default' => array(
        'prefix'  => '',
        'servers' => array(
            array('127.0.0.1', 11211, 0),
        ),
    ),
);
