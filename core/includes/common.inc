<?php

/**
 * @file
 *
 * 常用函数
 */

use Hunter\Core\Database\Database;
use Hunter\Core\CSRF\CSRF;
use Hunter\Core\Utility\Timer;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\RedirectResponse;

//file scan
function file_scan($dir, $regx, $options = array(), $depth = 1) {
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
                    $files = array_merge(file_scan($subdir, $regx, $options, $depth + 1), $files);
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

//file scan & include
function file_include($dir, $regx, $options = array()) {
    $files = file_scan($dir, $regx, $options);
    foreach ($files as $f) {
        require_once $f['file'];
    }
    return $files;
}

// Redirect URL path.
function redirect($path) {
  $request = ServerRequestFactory::fromGlobals();
  return new RedirectResponse($path);
}

/**
 * 开始计时器
 *
 * @param $name
 *   计时名
 */
if (!function_exists('timer_start')) {
  function timer_start($name = 'default') {
      Timer::start($name);
  }
}

/**
 * 读取计时器
 *
 * @param $name
 *   计时名
 *
 * @return
 *  时间(ms)
 */
if (!function_exists('timer_read')) {
  function timer_read($name = 'default') {
      return Timer::read($name);
  }
}

/**
 * 停止计时器
 *
 * @param $name
 *   计时名
 *
 * @return array
 *   时间数组(ms)
 */
if (!function_exists('timer_stop')) {
  function timer_stop($name = 'default') {
      return Timer::stop($name);
  }
}

//是否运行在cli模式
function is_cli() {
  return (!isset($_SERVER['SERVER_SOFTWARE'])
           && (PHP_SAPI == 'cli' || (is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0)));
}

// set session message.
function hunter_set_message($message = NULL, $type = 'status', $repeat = FALSE) {
  $message_list = session()->get('messages');
  if (isset($message)) {
    if (!isset($message_list[$type])) {
      $message_list[$type] = array();
    }

    if ($repeat || !in_array($message, $message_list[$type])) {
      $message_list[$type][] = $message;
      session()->set('messages', $message_list);
    }
  }

  // Messages not set when DB connection fails.
  return isset($message_list) ? $message_list : NULL;
}

// Get all session message.
function hunter_get_messages($type = NULL, $clear_queue = TRUE) {
  $message_list = session()->get('messages');
  if ($messages = hunter_set_message()) {
    if ($type) {
      if ($clear_queue) {
        unset($message_list[$type]);
      }
      if (isset($messages[$type])) {
        return array($type => $messages[$type]);
      }
    }
    else {
      if ($clear_queue) {
        unset($message_list);
        session()->delete('messages');
      }
      return $messages;
    }
  }
  return array();
}

//Returns the base URL path.
function base_path() {
  return $GLOBALS['base_path'];
}

//Get current path.
function request_uri() {
  if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
  }
  else {
    if (isset($_SERVER['argv'])) {
      $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
    }
    elseif (isset($_SERVER['QUERY_STRING'])) {
      $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
    }
    else {
      $uri = $_SERVER['SCRIPT_NAME'];
    }
  }
  // Prevent multiple slashes to avoid cross site requests via the Form API.
  $uri = '/' . ltrim($uri, '/');

  return $uri;
}

/**
 * Returns a persistent variable.
 */
function variable_get($name, $default = NULL) {
  global $conf;

  return isset($conf[$name]) ? $conf[$name] : $default;
}

/**
 * Sets a persistent variable.
 */
function variable_set($name, $value) {
  global $conf;

  db_merge('variable')->key(array('name' => $name))->fields(array('value' => serialize($value)))->execute();

  $conf[$name] = $value;
}

/**
 * Unsets a persistent variable.
 */
function variable_del($name) {
  global $conf;

  db_delete('variable')
    ->condition('name', $name)
    ->execute();

  unset($conf[$name]);
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param  string  $value
     * @return string
     */
    function e($value) {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array) {
        return $array['variables'];
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     */
    function csrf_field() {
        return CSRF::getHiddenInputString();
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    function csrf_token() {
        $csrf_token = session()->get(CSRF::TOKEN_NAME);

        if (!empty($csrf_token)) {
            return $csrf_token;
        }

        return CSRF::getToken();
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     */
    function dd($array) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
        die(1);
    }
}
