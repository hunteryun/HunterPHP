<?php

namespace Hunter\Core\App;

use Composer\Autoload\ClassLoader;

/**
 * The Silex framework class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application
{
    protected $files = array();
    protected $booted = false;
    protected $root;
    protected $routes = array();
    protected $classLoader;
    protected $moduleList;

    /**
     * Instantiate a new Application.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    public function __construct()
    {
      $this->root = static::guessApplicationRoot();
      $this->classLoader = new ClassLoader();
      $this->files = $this->file_scan($this->root.'/module', '/.*(\w+).*\.module/is', array('fullpath'=>true,'minDepth'=>2));
      $this->moduleList = $this->getModulesParameter($this->files);
    }

    /**
     * Determine the application root directory based on assumptions.
     *
     * @return string
     *   The application root.
     */
    public static function guessApplicationRoot()
    {
      return dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)));
    }

    /**
     * Registers a list of namespaces with PSR-4 directories for class loading.
     *
     * @param array $namespaces
     *   Array where each key is a namespace like 'Drupal\system', and each value
     *   is either a PSR-4 base directory, or an array of PSR-4 base directories
     *   associated with this namespace.
     * @param object $class_loader
     *   The class loader. Normally \Composer\Autoload\ClassLoader, as included by
     *   the front controller, but may also be decorated; e.g.,
     *   \Symfony\Component\ClassLoader\ApcClassLoader.
     */
    protected function classLoaderAddMultiplePsr4(array $namespaces = array(), $class_loader = NULL) {
      if ($class_loader === NULL) {
        $class_loader = $this->classLoader;
      }
      foreach ($namespaces as $prefix => $paths) {
        if (is_array($paths)) {
          foreach ($paths as $key => $value) {
            $paths[$key] = $this->root . '/' . $value;
          }
        }
        elseif (is_string($paths)) {
          $paths = $this->root . '/' . $paths;
        }
        $class_loader->addPsr4($prefix . '\\', $paths);
      }
    }

    /**
     * Gets the PSR-4 base directories for module namespaces.
     *
     * @param string[] $module_file_names
     *   Array where each key is a module name, and each value is a path to the
     *   respective *.info.yml file.
     *
     * @return string[]
     *   Array where each key is a module namespace like 'Drupal\system', and each
     *   value is the PSR-4 base directory associated with the module namespace.
     */
    protected function getModuleNamespacesPsr4($module_file_names) {
      $namespaces = array();
      foreach ($module_file_names as $module => $filename) {
        $namespaces["Hunter\\$module"] = dirname($filename) . '/src';
      }
      return $namespaces;
    }

    /**
     * Implements Drupal\Core\DrupalKernelInterface::updateModules().
     *
     * @todo Remove obsolete $module_list parameter. Only $module_filenames is
     *   needed.
     */
    public function updateModules(array $module_list) {
      if (!empty($module_list)) {
        $module_spaces = array();
        foreach ($module_list as $name => $info) {
          $module_spaces[$name] = $info['pathname'];
        }
        $this->classLoaderAddMultiplePsr4($this->getModuleNamespacesPsr4($module_spaces));
        $this->classLoader->register();
      }
    }

    /**
     * file scan.
     *
     * @return array
     *   The files list.
     */
    public function file_scan($dir, $regx, $options = array(), $depth = 1) {
        $options += array(
            'nomask'   => '/(\.\.?|CSV)$/',
            'recurse'  => true,
            'minDepth' => 1,
            'maxDepth' => 10,
            'fullpath' => false,
        );
        $files = array();
        if (is_dir($dir) && $depth <= $options['maxDepth'] && ($handle = opendir($dir))) {
            while (false !== ($filename = readdir($handle))) {
                if (!preg_match($options['nomask'], $filename) && $filename[0] != '.') {
                    $subdir = $dir . '/' . $filename;
                    if (is_dir($subdir) && $options['recurse']) {
                        $files = array_merge($this->file_scan($subdir, $regx, $options, $depth + 1), $files);
                    } elseif ($depth >= $options['minDepth']) {
                        if (preg_match($regx, $filename) || ($options['fullpath'] && preg_match($regx, $subdir))) {
                            $files[] = array(
                                'dirname'  => $dir,
                                'basename' => $filename,
                                'file'     => $dir . '/' . $filename,
                            );
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * Returns an array of Extension class parameters for all enabled modules.
     *
     * @return array
     */
    public function getModulesParameter($files) {
      $extensions = array();
      foreach ($files as $name => $f) {
        list ($module,) = explode('.', $f['basename']);
        $extensions[$module] = array(
          'type' => 'module',
          'pathname' => 'module/'.$module.'/'.$module.'.info.yml',
          'filename' => $f['basename'],
        );
      }
      return $extensions;
    }

    /**
     * Returns an array of Extension class parameters for all enabled modules.
     *
     * @return array
     */
    public function getModulesList() {
      return $this->moduleList;
    }

    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return Application
     */
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        $provider->register($this);

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Boots all service providers.
     *
     * This method is automatically called by handle(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot()
    {
        if (!$this->booted) {
            foreach ($this->providers as $provider) {
                $provider->boot($this);
            }

            $this->booted = true;
        }
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();
    }

    /**
     * {@inheritdoc}
     *
     * If you call this method directly instead of run(), you must call the
     * terminate() method yourself if you want the finish filters to be run.
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (!$this->booted) {
            $this->boot();
        }

        return $response;
    }
}
