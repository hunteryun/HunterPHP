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
