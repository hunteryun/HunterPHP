<?php

/*
 * @file
 *
 * Blade Loader
 */

namespace Hunter\Core\Templating\Blade;

class Loader {

    const MAIN_NAMESPACE = '__main__';

    protected $paths = array();

    protected $cache_path;

    public function __construct($paths = array(), $cache_path) {
        if ($paths) {
            $this->setPaths($paths);
        }

        if($cache_path){
          $this->cache_path = $cache_path;
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

    public function setSource($name, $contents, $lock = false) {
        return file_put_contents($name, $contents, $lock ? LOCK_EX : 0);
    }

    public function exists($name, $cache) {
        return $this->findTemplate($name, $cache);
    }

    protected function findTemplate($name, $cache = FALSE, $namespace = self::MAIN_NAMESPACE) {
        if($cache && is_file($name)){
          return $name;
        }

        if(is_file($name)){
          return $name;
        }

        foreach ($this->paths[$namespace] as $dir) {
            $file = $dir . '/' . $name;
            if (is_file($file)) {
                return $file;
            }
        }

        return false;
    }

    public function getCachePath() {
        return $this->cache_path;
    }

}
