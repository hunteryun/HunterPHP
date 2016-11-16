<?php

/*
 * @file
 *
 * EngineInterface.php
 */
 
namespace Hunter\Core\Templating;

interface EngineInterface {

    //渲染模板
    public function render($name, array $parameters = array());
    
    //渲染模板并输出
    public function display($name, array $parameters = array());
    
    //模板是否存在
    public function exists($name);
    
    //设置模板
    public function setTemplate($name);
    
    //设置数据
    public function setParameters(array $parameters = array());
    
    //设置数据
    public function setParameter($key, $value = null);
    
    //获取数据
    public function getParameters($names = null);
    
    //获取数据
    public function getParameter($key, $default = null);

    //添加数据到全局变量
    public function addGlobal($key, $value);

}
