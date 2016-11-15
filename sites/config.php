<?php

//配置: session
$sessions = array(
    'prefix' => '',
);

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
        'class' => 'core\Component\Logger\FileLogger', //如果记文件日志用FileLogger
        'level' => 'warn',
        'file'  => HUNTER_ROOT . '/sites/logs', //这里指定log目录
        'prefix' => 'hunter',
        'debug' => true,
    ),
);

//配置: 模板引擎
$engines = array(
    'default' => array(
        'engine'      => 'core\Component\Templating\BladeEngine',
        'loader'      => 'core\Component\Templating\Blade\Loader',
        'environment' => 'core\Component\Templating\Blade\Environment',
        'loaderArgs'  => array(HUNTER_ROOT . '/theme'),
        'cacheDir'    => HUNTER_ROOT . '/sites/cache',
        'envArgs'     => array(),
    ),
    'command' => array(
        'engine'      => 'core\Component\Templating\PhpEngine',
        'loader'      => 'core\Component\Templating\Php\Loader',
        'environment' => 'core\Component\Templating\Php\Environment',
        'loaderArgs'  => array(HUNTER_ROOT . '/core/Command/templates'),
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
