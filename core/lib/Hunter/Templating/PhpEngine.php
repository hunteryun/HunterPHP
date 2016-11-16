<?php

/*
 * @file
 *
 * PhpEngine
 */
 
namespace Hunter\Core\Templating;

use ErrorException;

class PhpEngine implements EngineInterface {
    
    /**
     * 模版文件
     */
    protected $template;
    
    /**
     * 模版数据
     */
    protected $parameters = array();
    
    /**
     * 整体环境包
     */
    protected $environment;
    
    /**
     * 析构函数
     */
    public function __construct($environment) {
        $this->environment = $environment;
    }
    
    /**
     * 渲染模版
     */
    public function render($name, array $parameters = array()) {
        $this->setTemplate($name);
        $this->setParameters($parameters);
        return $this->environment->render($name, $parameters);
    }
    
    /**
     * 渲染并直接输出
     */
    public function display($name, array $parameters = array()) {
        $this->environment->display($name, $parameters);
    }

    /**
     * 模版是否存在
     */
    public function exists($name) {
        return $this->environment->exists((string) $name);
    }
    
    /**
     * 设置模版
     */
    public function setTemplate($name) {
        $this->template = $name;
        return $this;
    }
    
    /**
     * 设置数据
     */
    public function setParameters(array $parameters = array()) {
        $this->parameters = $parameters;
        return $this;
    }
    
    /**
     * 设置数据
     */
    public function setParameter($key, $value = null) {
        $this->parameters[$key] = $value;
        return $this;
    }
    
    /**
     * 获取数据
     */
    public function getParameters($names = null) {
        if ($names && is_array($names)) {
            foreach ($names as $name) {
                $return[$name] = isset($this->parameters[$name]) ? $this->parameters[$name] : null;
            }
            return $return;
        } elseif (!isset($names)) {
            return $this->parameters;
        }
    }
    
    /**
     * 获取数据
     */
    public function getParameter($name, $default = null) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }
    
    /**
     * 添加全局变量
     */
    public function addGlobal($key, $value) {
        $this->environment->addGlobal($key, $value);
        return $this;
    }
    
    /**
     * 获取Environment
     */
    public function getEnvironment() {
        return $this->environment;
    }
    
    //魔术方法
    public function __toString() {
      try {
          return $this->environment->render($this->template, $this->parameters);
      } catch (ErrorException $e) {
          logger()->error($e)->show();
      }
    }

}

