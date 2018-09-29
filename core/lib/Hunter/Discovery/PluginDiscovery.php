<?php

namespace Hunter\Core\Discovery;

use ReflectionClass;
use Hunter\Core\Utility\Unicode;
use Doctrine\Annotations\AnnotationReader;

/**
 * Provides discovery for plugin within a given set of directories.
 */
class PluginDiscovery implements DiscoverableInterface {

  /**
   * The base filename to look for in each directory.
   *
   * @var string
   */
  protected $name;

  /**
   * An array of directories to scan, keyed by the provider.
   *
   * @var array
   */
  protected $directories = array();

  /**
	 * @Inject
	 * @var Container $container
	 */
	protected $container;

	/**
	 * @Inject
	 * @var Reader $annotationReader
	 */
	protected $annotationReader;

  /**
   * Constructs a YamlDiscovery object.
   *
   * @param string $name
   *   The base filename to look for in each directory. The format will be
   *   $provider.$name.yml.
   * @param array $directories
   *   An array of directories to scan, keyed by the provider.
   */
  public function __construct(array $directories) {
    $this->directories = $directories;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll() {
    $all = array();

    foreach ($this->directories as $module => $directory) {
      $plugin_files = file_scan($directory.'/src/Plugin', "/.*(\w+).*\.php$/is", array('fullpath'=>true, 'minDepth'=>1));
      foreach ($plugin_files as $key => $f) {
        $subdir = str_replace($directory.'/src/Plugin', "", $f['dirname']);
        $namespaces = "Hunter\\$module\\Plugin";
        if(!empty($subdir)){
          $namespaces = $namespaces.str_replace('/', '\\', $subdir);
        }
        $fullspace = $namespaces.'\\'.basename($f['basename'], '.php');

        $reflClass = new ReflectionClass($fullspace);
        $reader = new AnnotationReader();
        $classAnnotations = $reader->getClassAnnotations($reflClass);
        if(!empty($classAnnotations)){
          foreach ($classAnnotations as $key => $ca) {
            $ca->setClass($fullspace);

            if (!$ca->getProvider()) {
              $ca->setProvider($this->getProviderFromNamespace($fullspace));
            }

            $all[$ca->getId()] = $ca->get();
          }
        }
      }
    }

    return $all;
  }

  /**
   * Extracts the provider name from a Hunter namespace.
   *
   * @param string $namespace
   *   The namespace to extract the provider from.
   *
   * @return string|null
   *   The matching provider name, or NULL otherwise.
   */
  protected function getProviderFromNamespace($namespace) {
    preg_match('|^Hunter\\\\(?<provider>[\w]+)\\\\|', $namespace, $matches);

    if (isset($matches['provider'])) {
      return Unicode::strtolower($matches['provider']);
    }

    return NULL;
  }

}
