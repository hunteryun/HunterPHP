<?php

namespace Hunter\Core\Templating\Blade;

abstract class Compiler {

    /**
     * The template files.
     *
     * @var string
     */
    protected $files;

    /**
     * Get the cache path for the compiled views.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * The Loader instance.
     *
     * @var string
     */
    protected $loader;

    /**
     * Create a new compiler instance.
     *
     * @param  string  $cachePath
     * @return void
     */
    public function __construct($file = null, $cachePath = 'sites/cache')
    {
        $this->files = $file;
        $this->cachePath = $cachePath;
        $this->loader = new Loader('theme', 'sites/cache');
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        if(!is_dir(dirname($this->cachePath.'/'.md5($path).'.php'))){
          mkdir(dirname($this->cachePath.'/'.md5($path).'.php'), 0755, true);
        }
        return $this->cachePath.'/'.md5($path).'.php';
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!$this->cachePath || !$this->files->exists($compiled)) {
            return true;
        }

        $lastModified = $this->files->lastModified($path);

        return $lastModified >= $this->files->lastModified($compiled);
    }
}
