<?php

namespace Hunter\Core\App;

/**
 * The Silex framework class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application
{
    protected $providers = array();
    protected $booted = false;
    protected $root;
    protected $routes = array();

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

        $this['module_handler'] = $this->share(function () use ($app) {
            $files = $this->file_scan($this->root.'/module', '/.*(\w+).*\.module/is', array('fullpath'=>true,'minDepth'=>2));
            $module_list = $this->getModulesParameter($files);
            return new ModuleHandler($this->root, $module_list);
        });
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
     * file scan.
     *
     * @return array
     *   The files list.
     */
    protected function file_scan($dir, $regx, $options = array(), $depth = 1) {
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
    protected function getModulesParameter($files) {
      $extensions = array();
      foreach ($files as $name => $f) {
        list ($module,) = explode('.', $f['basename']);
        $extensions[$name] = array(
          'type' => 'module',
          'pathname' => 'module/'.$module.'/'.$module.'.info',
          'filename' => $f['basename'],
        );
      }
      return $extensions;
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
