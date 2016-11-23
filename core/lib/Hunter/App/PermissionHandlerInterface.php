<?php

namespace Hunter\Core\App;

/**
 * Defines an interface to list available permissions.
 */
interface PermissionHandlerInterface {

  /**
   * Gets all available permissions.
   *
   * @return array
   */
  public function getPermissions();

  /**
   * Determines whether a module provides some permissions.
   *
   * @param string $module_name
   *   The module name.
   *
   * @return bool
   *   Returns TRUE if the module provides some permissions, otherwise FALSE.
   */
  public function moduleProvidesPermissions($module_name);

}
