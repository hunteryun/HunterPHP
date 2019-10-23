<?php

/**
 * @file
 *
 * template engine
 */

use Hunter\Core\Templating\Theme;

/**
 * 获取模板引擎类
 *
 * @param string $target
 */
function theme($target = 'default') {
    return Theme::getEngine($target);
}

/**
 * 返回整合后内容
 * @param $name 模板名
 * @param $parameters 变量
 */
function view($name, array $parameters = array(), $target = 'default') {
    global $default_admin_theme, $default_front_theme;

    if($default_admin_theme !== 'admin' && strpos($name, 'admin') !==false) {
      $name = str_replace("admin", $default_admin_theme, $name);
    }

    if($default_front_theme !== 'front' && strpos($name, 'front') !==false) {
      $name = str_replace("front", $default_front_theme, $name);
    }

    return theme($target)->setTemplate($name)->setParameters($parameters)->render($name, $parameters);
}

/*
 * @usage
 *
 * 获取模版渲染结果
 *   theme()->render($name, $parameters);
 *
 * 直接输出模版
 *   theme()->display($name, $parameters);
 *
 * 模版是否存在
 *   theme()->exists($name);
 */
