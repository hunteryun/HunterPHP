<?php

/*
 * @file
 *
 * Php Environment
 */
 
namespace Hunter\Core\Templating\Php;

class Environment {
    
    /**
     * Loader
     */
    protected $loader;
    
    /**
     * 配置信息
     */
    protected $options;
    
    /**
     * 全局变量
     */
    protected $globals = array();
    
    /**
     * 析构函数
     */
    public function __construct($loader = null, $options = array()) {
        $this->loader  = $loader;
        $this->options = $options + array(
            'cache' => false,
        );
    }
    
    public function render($name, $parameters = array()) {
        ob_start();
        $this->display($name, $parameters);
        return ob_get_clean();
    }
    
    public function display($name, $parameters = array()) {
        if ($file = $this->exists($name)) {
            $variables = $this->mergeGlobals($parameters);
            extract($variables);
            include $file;
        }
        return $this;
    }
    
    public function exists($name) {
        return $this->getLoader()->exists($name);
    }

    public function setLoader($loader) {
        $this->loader = $loader;
        return $this;
    }

    public function getLoader() {
        return $this->loader;
    }
    
    public function addGlobal($name, $value) {
        $this->globals[$name] = $value;
        return $this;
    }
    
    public function setGlobals(array $context) {
        $this->globals = $context + $this->globals;
        return $this;
    }
    
    public function getGlobals() {
        return $this->globals;
    }
    
    public function mergeGlobals(array $context) {
        return $context + $this->getGlobals();
    }

}
