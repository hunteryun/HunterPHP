<?php

namespace Hunter\Core\App;

use Composer\Autoload\ClassLoader;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Container\ReflectionContainer;
use League\Route\RouteCollection;
use Hunter\Core\App\Strategy\HunterStrategy;
use Symfony\Component\Console\Application as ConsoleApp;
use Hunter\Core\Discovery\YamlDiscovery;
use Hunter\Core\App\ServiceProvider\HttpMessageServiceProvider;
use Hunter\Core\App\ModuleHandler;
use Hunter\Core\App\PermissionHandler;

/**
 * The Silex framework class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application {
    protected $booted = false;
    protected $root;
    protected $classLoader;
    protected $container;
    protected $moduleList;
    protected $routers = array();
    protected $routePermission = array();
    protected $moduleHandler;
    protected $permissionHandler;

    /**
     * Instantiate a new Application.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    public function __construct() {
      $this->root = static::guessApplicationRoot();
      $this->classLoader = new ClassLoader();
    }

    /**
     * Determine the application root directory based on assumptions.
     *
     * @return string
     *   The application root.
     */
    public static function guessApplicationRoot() {
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
     * Boots all service providers.
     *
     * This method is automatically called by handle(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot() {
        if (!$this->booted) {

            // load all core file.
            $this->loadLegacyIncludes();

            // Initialize the container.
            $this->initializeContainer();

            // Initialize legacy request globals.
            $this->initializeRequestGlobals();

            // Initialize all module list.
            $this->initializeModuleList();

            // Initialize all permission list.
            $this->initializePermissionList();

            $this->booted = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadLegacyIncludes() {
      require_once $this->root . '/core/includes/session.inc';
      require_once $this->root . '/core/includes/common.inc';
      require_once $this->root . '/core/includes/database.inc';
      require_once $this->root . '/core/includes/theme.inc';
    }

    /**
     * Initializes the service container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function initializeContainer() {
        $container = new Container();

        $container->addServiceProvider(HttpMessageServiceProvider::class);

        $container->delegate(new ReflectionContainer());

        $this->container = $container;
    }

    /**
     * Bootstraps the legacy global request variables.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The current request.
     *
     * @todo D8: Eliminate this entirely in favor of Request object.
     */
    protected function initializeRequestGlobals() {
      global $base_url;
      // Set and derived from $base_url by this function.
      global $base_path, $base_root;
      global $base_secure_url, $base_insecure_url;

      $request = $this->container->get('request');

      $serverParams = $request->getServerParams();

      // Create base URL.
      $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
      $http_protocol = $is_https ? 'https' : 'http';

      if(!is_cli()){
        $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];
        $base_url = $base_root;
      }

      // For a request URI of '/index.php/foo', $_SERVER['SCRIPT_NAME'] is
      // '/index.php', whereas $_SERVER['PHP_SELF'] is '/index.php/foo'.
      if ($dir = rtrim(dirname($serverParams['SCRIPT_NAME']), '\/')) {
        // Remove "core" directory if present, allowing install.php,
        // authorize.php, and others to auto-detect a base path.
        $core_position = strrpos($dir, '/core');
        if ($core_position !== FALSE && strlen($dir) - 5 == $core_position) {
          $base_path = substr($dir, 0, $core_position);
        }
        else {
          $base_path = $dir;
        }
        $base_url .= $base_path;
        $base_path .= '/';
      }
      else {
        $base_path = '/';
      }
      $base_secure_url = str_replace('http://', 'https://', $base_url);
      $base_insecure_url = str_replace('https://', 'http://', $base_url);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeModuleList() {
      $modulefiles = file_scan($this->root.'/module', '/.*(\w+).*\.module/is', array('fullpath'=>true,'minDepth'=>2));
      $this->moduleList = $this->getModulesParameter($modulefiles);

      $this->updateModules($this->moduleList);
      $this->moduleHandler = new ModuleHandler($this->root, $this->moduleList);
      $this->moduleHandler->loadAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function initializePermissionList() {
      $modulefiles = file_scan($this->root.'/module', '/.*(\w+).*\.module/is', array('fullpath'=>true,'minDepth'=>2));
      $this->moduleList = $this->getModulesParameter($modulefiles);
      $this->permissionHandler = new PermissionHandler($this->moduleHandler);
      $permissions = $this->permissionHandler->getPermissions();
      if(!empty($permissions)){
        foreach ($permissions as $name => $info) {
          if(isset($info['_callback'])){
            list($class, $method) = explode('::', $info['_callback'], 2);
            $this->container->add($class);
            $this->container->add('hunter_permission_'.str_replace(" ", "_", $name), array('_callback' => $info['_callback']));
          }
        }
      }
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRouters($container) {
        $routers = new RouteCollection($container);
        $routers->setStrategy(new HunterStrategy());

        $discovery = new YamlDiscovery('routing', $this->moduleHandler->getModuleDirectories());

        foreach ($discovery->findAll() as $module_routers) {
          foreach ($module_routers as $name => $route_info) {
            if(isset($route_info['requirements']['_permission'])){
              $this->routePermission[$route_info['path']] = $route_info['requirements']['_permission'];
            }

            $routers->get($route_info['path'], $route_info['defaults']['_controller']);
          }
        }

        $this->routers = $routers;
        $this->container->add('routePermission', $this->routePermission);
    }

    /**
     * Load and regist Command
     */
    public function registerCommand($dir, $path, $regx = '', $options = array()) {
        if (!$this->booted) {
            $this->boot();
        }

        $options += array('fullpath'=>true, 'minDepth'=>1);
        $files = file_include($path.'/'.$dir, "/.*(\w+).*\.php$/is", $options);

        $cmdapp = new ConsoleApp();
        foreach ($files as $f) {
          require_once $f['file'];
          list ($command,) = explode('.', $f['basename']);
          $cmdapp->add(new $command());
        }

        $cmdapp->run();
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulesList() {
        return $this->moduleList;
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run() {
        if (!$this->booted) {
            $this->boot();
        }

        $request = $this->container->get('request');

        $response = $this->container->get('response');

        $this->buildRouters($this->container);

        $response = $this->routers->dispatch($request, $response);

        $this->container->get('emitter')->emit($response);
    }

}
