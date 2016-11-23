<?php

namespace Hunter\Core\App;

use Hunter\Core\Discovery\YamlDiscovery;
use Hunter\Core\App\ModuleHandlerInterface;

/**
 * Provides the available permissions based on yml files.
 */
class PermissionHandler implements PermissionHandlerInterface {

  /**
   * The module handler.
   *
   * @var \Hunter\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The YAML discovery class to find all .permissions.yml files.
   *
   * @var \Hunter\Core\Discovery\YamlDiscovery
   */
  protected $yamlDiscovery;

  /**
   * Constructs a new PermissionHandler.
   *
   * @param \Hunter\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Hunter\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Hunter\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    // @todo It would be nice if you could pull all module directories from the
    //   container.
    $this->moduleHandler = $module_handler;
  }

  /**
   * Gets the YAML discovery.
   *
   * @return \Hunter\Core\Discovery\YamlDiscovery
   *   The YAML discovery.
   */
  protected function getYamlDiscovery() {
    if (!isset($this->yamlDiscovery)) {
      $this->yamlDiscovery = new YamlDiscovery('permissions', $this->moduleHandler->getModuleDirectories());
    }
    return $this->yamlDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissions() {
    $all_permissions = $this->buildPermissionsYaml();

    return $this->sortPermissions($all_permissions);
  }

  /**
   * {@inheritdoc}
   */
  public function moduleProvidesPermissions($module_name) {
    $permissions = $this->getPermissions();

    foreach ($permissions as $permission) {
      if ($permission['provider'] == $module_name) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Builds all permissions provided by .permissions.yml files.
   *
   * @return array[]
   *   Each return permission is an array with the following keys:
   *   - title: The title of the permission.
   *   - description: The description of the permission, defaults to NULL.
   *   - provider: The provider of the permission.
   */
  protected function buildPermissionsYaml() {
    $all_permissions = array();

    foreach ($this->getYamlDiscovery()->findAll() as $provider => $permissions) {
      foreach ($permissions as &$permission) {
        if (!is_array($permission)) {
          $permission = array(
            'title' => $permission,
          );
        }
        $permission['title'] = $permission['title'];
        $permission['description'] = isset($permission['description']) ? $permission['description'] : NULL;
        $permission['provider'] = !empty($permission['provider']) ? $permission['provider'] : $provider;
      }

      $all_permissions += $permissions;
    }

    return $all_permissions;
  }

  /**
   * Sorts the given permissions by provider name and title.
   *
   * @param array $all_permissions
   *   The permissions to be sorted.
   *
   * @return array[]
   *   Each return permission is an array with the following keys:
   *   - title: The title of the permission.
   *   - description: The description of the permission, defaults to NULL.
   *   - provider: The provider of the permission.
   */
  protected function sortPermissions(array $all_permissions = array()) {
    // Get a list of all the modules providing permissions and sort by
    // display name.
    $modules = $this->getModuleNames();

    uasort($all_permissions, function (array $permission_a, array $permission_b) use ($modules) {
      if ($modules[$permission_a['provider']] == $modules[$permission_b['provider']]) {
        return $permission_a['title'] > $permission_b['title'];
      }
      else {
        return $modules[$permission_a['provider']] > $modules[$permission_b['provider']];
      }
    });
    return $all_permissions;
  }

  /**
   * Returns all module names.
   *
   * @return string[]
   *   Returns the human readable names of all modules keyed by machine name.
   */
  protected function getModuleNames() {
    $modules = array();
    foreach (array_keys($this->moduleHandler->getModuleList()) as $module) {
      $modules[$module] = $this->moduleHandler->getName($module);
    }
    asort($modules);
    return $modules;
  }

}
