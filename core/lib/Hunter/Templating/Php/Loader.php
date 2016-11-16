<?php

/*
 * @file
 *
 * Php Loader
 */
 
namespace Hunter\Core\Templating\Php;

class Loader {
    
    const MAIN_NAMESPACE = '__main__';
    
    protected $paths = array();
    
    public function __construct($paths = array()) {
        if ($paths) {
            $this->setPaths($paths);
        }
    }
    
    public function setPaths($paths, $namespace = self::MAIN_NAMESPACE) {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $this->paths[$namespace] = array();
        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }
    
    public function addPath($path, $namespace = self::MAIN_NAMESPACE) {
        if (!is_dir($path)) {
            throw new \Exception(sprintf('The "%s" directory does not exist.', $path));
        }
        $this->paths[$namespace][] = rtrim($path, '/\\');
    }
    
    public function getSource($name) {
        return file_get_contents($this->findTemplate($name));
    }
    
    public function exists($name) {
        return $this->findTemplate($name);
    }

    protected function findTemplate($name, $namespace = self::MAIN_NAMESPACE) {
        foreach ($this->paths[$namespace] as $dir) {
            $file = $dir . '/' . $name;
            if (is_file($file)) {
                return $file;
            }
        }

        return false;
    }

}
