<?php

//配置: 根目录
$root_dir = '/';

//配置: 安全key
$safe_key = '6kUxOb';

//配置: 开发模式
$hunter_debug = true;

//配置: 静态缓存
$hunter_static = false;

//配置: 默认缓存
$default_cache = 'file';

//配置: 缓存目录
$cache_dir = HUNTER_ROOT . '/sites/files';

//配置: 默认主题
$default_theme = 'hunter';

//配置: 默认语言
$default_language = 'zh';

//配置: 启用图片自动压缩
$auto_image_compress = array(
  'enable' => true,
  'quality' => 70,
);

//配置: ImageStyle, 格式dir => size
$image_style = array(
  'product' =>array(
    '90_90' => array(
      'size' => '90*90',
      'method' => 'resize',
      'background' => '#FFFFFF'
    ),
    '190_190' => array(
      'size' => '190*190',
      'method' => 'resize',
      'background' => '#FFFFFF'
    ),
    '570_570' => array(
      'size' => '570*570',
      'method' => 'scaleResize',
      'background' => '#F4F4F4'
    ),
    '1200_1200' => array(
      'size' => '1200*1200',
      'method' => 'scaleResize',
      'background' => '#F4F4F4'
    ),
  ),
  'news' => '100*100',
  'page' => '100*100'
);

//配置: session
$sessions = array(
    'prefix' => '',
    'expire' => 0,
    'class'  => 'Hunter\Core\Session\PhpSession',
    'path'   => '', //phpsess存储路径
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
    'sqlite' => array(
        'database' => 'sites/files/test.sqlite',
        'prefix' => '',
        'namespace' => 'Hunter\\Core\\Database\\sqlite',
        'driver' => 'sqlite',
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
