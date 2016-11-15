<?php
/**
 * Include autoload file
 */
if (file_exists(HUNTER_ROOT . "/vendor/autoload.php")) {
    require_once HUNTER_ROOT . '/vendor/autoload.php';
} else {
    die("<pre>Run 'composer install' in root dir</pre>");
}

/**
 * Load site config file
 */
if (file_exists(HUNTER_ROOT . '/sites/config.php')) {
  include_once HUNTER_ROOT . '/sites/config.php';
}
