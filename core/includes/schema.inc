<?php

/**
 * @file
 * Schema API handling functions.
 */

/**
 * @addtogroup schemaapi
 * @{
 */

/**
 * Indicates that a module has not been installed yet.
 */
const SCHEMA_UNINSTALLED = -1;

/**
 * Creates all tables defined in a module's hook_schema().
 *
 * @param string $module
 *   The module for which the tables will be created.
 */
function hunter_install_schema($module) {
  $schema = hunter_get_module_schema($module);
  _hunter_schema_initialize($schema, $module, FALSE);

  foreach ($schema as $name => $table) {
    db_create_table($name, $table);
  }
}

/**
 * Removes all tables defined in a module's hook_schema().
 *
 * @param string $module
 *   The module for which the tables will be removed.
 */
function hunter_uninstall_schema($module) {
  $schema = hunter_get_module_schema($module);
  _hunter_schema_initialize($schema, $module, FALSE);

  foreach ($schema as $table) {
    if (db_table_exists($table['name'])) {
      db_drop_table($table['name']);
    }
  }
}

/**
 * Returns a module's schema.
 *
 * This function can be used to retrieve a schema specification in
 * hook_schema(), so it allows you to derive your tables from existing
 * specifications.
 *
 * @param string $module
 *   The module to which the table belongs.
 * @param string $table
 *   The name of the table. If not given, the module's complete schema
 *   is returned.
 */
function hunter_get_module_schema($module, $table = NULL) {
  // Load the .install file to get hook_schema.
  module_load_install($module);
  $schema = \hunter::moduleHandler()->invoke($module, 'schema');

  if (isset($table)) {
    if (isset($schema[$table])) {
      return $schema[$table];
    }
    return array();
  }
  elseif (!empty($schema)) {
    return $schema;
  }
  return array();
}

/**
 * Fills in required default values for table definitions from hook_schema().
 *
 * @param array $schema
 *   The schema definition array as it was returned by the module's
 *   hook_schema().
 * @param string $module
 *   The module for which hook_schema() was invoked.
 * @param bool $remove_descriptions
 *   (optional) Whether to additionally remove 'description' keys of all tables
 *   and fields to improve performance of serialize() and unserialize().
 *   Defaults to TRUE.
 */
function _hunter_schema_initialize(&$schema, $module, $remove_descriptions = TRUE) {
  // Set the name and module key for all tables.
  foreach ($schema as $name => &$table) {
    if (empty($table['module'])) {
      $table['module'] = $module;
    }
    if (!isset($table['name'])) {
      $table['name'] = $name;
    }
    if ($remove_descriptions) {
      unset($table['description']);
      foreach ($table['fields'] as &$field) {
        unset($field['description']);
      }
    }
  }
}

/**
 * Typecasts values to proper datatypes.
 *
 * MySQL PDO silently casts, e.g. FALSE and '' to 0, when inserting the value
 * into an integer column, but PostgreSQL PDO does not. Look up the schema
 * information and use that to correctly typecast the value.
 *
 * @param array $info
 *   An array describing the schema field info.
 * @param mixed $value
 *   The value to be converted.
 *
 * @return mixed
 *   The converted value.
 */
function hunter_schema_get_field_value(array $info, $value) {
  // Preserve legal NULL values.
  if (isset($value) || !empty($info['not null'])) {
    if ($info['type'] == 'int' || $info['type'] == 'serial') {
      $value = (int) $value;
    }
    elseif ($info['type'] == 'float') {
      $value = (float) $value;
    }
    elseif (!is_array($value)) {
      $value = (string) $value;
    }
  }
  return $value;
}

/**
 * @} End of "addtogroup schemaapi".
 */
