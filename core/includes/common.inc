<?php

/**
 * @file
 *
 * 常用函数
 */

use Hunter\Core\Database\Database;
use Hunter\Core\CSRF\CSRF;
use Hunter\Core\Utility\Timer;
use Hunter\Core\Utility\StringConverter;
use Hunter\Core\Password\Password;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\RedirectResponse;
use Hunter\Core\FormApi\Layui;
use Hunter\Core\FormApi\NoHtml;
use voku\helper\AntiXSS;

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
function redirect($path, $status = 302, $headers = []) {
  $request = ServerRequestFactory::fromGlobals();
  if(substr($path,0,1) == '/'){
    $path = base_path().substr($path,1);
  }else {
    $path = base_path().$path;
  }
  return new RedirectResponse($path, $status, $headers);
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

//Returns the base root path.
function base_root() {
  return $GLOBALS['base_root'];
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

  if(strpos($uri,'?')){
    $uri = substr($uri, 0, strpos($uri,'?'));
  }

  return $uri;
}

/**
 * password_hash (php5.5)
 */
function hunter_password_hash($password, $algo = 1, $options = array()) {
    return Password::hash($password);
}

/**
 * password_verify (php5.5)
 */
function hunter_password_verify($password, $hash) {
    return Password::verify($password, $hash);
}

/**
 * password_needs_rehash (php5.5)
 */
function hunter_password_needs_rehash($hash, $algo = 1) {
    return Password::needsRehash($hash);
}

/**
 * 字符转换成utf-8.
 */
function hunter_convert_to_utf8($data) {
  if (function_exists('iconv')) {
    $out = @iconv('GB2312', 'utf-8', $data);
  }
  elseif (function_exists('mb_convert_encoding')) {
    $out = @mb_convert_encoding($data, 'utf-8', 'GB2312');
  }
  elseif (function_exists('recode_string')) {
    $out = @recode_string('GB2312' . '..utf-8', $data);
  }
  else {
    return FALSE;
  }

  return $out;
}

/**
 * 转换文件名.
 */
function hunter_rename($text, $ext = false, $type = 'date', $format = 'YmdHis') {
  $string = new StringConverter();
  $houzhui = substr(strrchr($text, '.'), 1);
  $basename = basename($text,".".$houzhui);
  $newname = $string->createMachineName($basename);
  if($type == 'date'){
    $newname .= '_'.date($format, time());
  }
  if($ext){
    return $newname.".".$houzhui;
  }else {
    return $newname;
  }
}

/**
 * Converts an associative array to attribute string.
 */
function hunter_attributes(array $attributes = array()) {
  foreach ($attributes as $attribute => &$data) {
    $data = implode(' ', (array) $data);
    $data = $attribute . '="' . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . '"';
  }
  return $attributes ? ' ' . implode(' ', $attributes) : '';
}

/**
 * Sets HTML attributes based on element properties.
 */
function element_set_attributes(array &$field, array $map) {
  foreach ($map as $property => $attribute) {
    // If the key is numeric, the attribute name needs to be taken over.
    if (is_int($property)) {
      $property = '#' . $attribute;
    }
    // Do not overwrite already existing attributes.
    if (isset($field[$property]) && !isset($field['#attributes'][$attribute])) {
      $field['#attributes'][$attribute] = $field[$property];
    }
  }
}

/**
 * Forms an associative array from a linear array.
 */
function hunter_map_assoc($array, $function = NULL) {
  $array = !empty($array) ? array_combine($array, $array) : array();
  if (is_callable($function)) {
    $array = array_map($function, $array);
  }
  return $array;
}

/**
 * hunter render form api
 */
function hunter_form_render($type, &$fields, $module = NULL, $parms = NULL) {
  global $app;
  $form_id = '';
  if(isset($fields['form_id'])){
    $form_id = $fields['form_id'];
    unset($fields['form_id']);
  }

  $form_redirect = '#';
  if(isset($fields['redirect'])){
    $form_redirect = $fields['redirect'];
    unset($fields['redirect']);
  }

  if($type == 'layui'){
    $form = Layui::create()->start($form_redirect, $form_id);
  }else{
    $form = NoHtml::create()->start($form_redirect, $form_id);
  }

  foreach ($app->getModuleHandle()->getImplementations('form_alter') as $module_name) {
    $args = array(&$fields, $form_id, $module, $parms);
    $app->getModuleHandle()->invoke($module_name, 'form_alter', $args);
  }

  $submit_field = array();

  if(!empty($fields)){
    foreach ($fields as $name => $field) {
      if (!isset($field['#name'])) {
        $field['#name'] = $name;
      }

      if(!isset($field['#attributes'])){
        $field['#attributes'] = array();
      }

      if(!isset($field['#value'])){
        $field['#value'] = isset($field['#default_value']) ? clean($field['#default_value']) : '';
      }

      switch ($field['#type'])
      {
      case 'textfield':
        $field['#attributes']['type'] = 'text';
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name', 'value', 'size', 'maxlength'));
        $form->input($name, $field);
        break;
      case 'radios':
        $field['#attributes']['type'] = 'radio';
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name'));
        $form->radio($name, $field);
        break;
      case 'checkboxes':
        $field['#attributes']['type'] = 'checkbox';
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name'));
        $form->checkbox($name, $field);
        break;
      case 'file':
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name', 'value', 'size'));
        $field['#attributes']['class'][] = 'layui-input layui-input-inline';
        $form->file($name, $field);
        break;
      case 'image':
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name', 'value', 'size'));
        $field['#attributes']['class'][] = 'layui-input layui-input-inline';
        $form->file($name, $field);
        break;
      case 'fieldset':
        element_set_attributes($field, array('id'));
        $form->fieldset($name, $field);
        break;
      case 'select':
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
        }
        element_set_attributes($field, array('id', 'name', 'size'));
        $form->select($name, $field);
        break;
      case 'textarea':
        if(isset($field['#disabled']) && $field['#disabled']){
          $field['#attributes']['disabled'] = 'true';
          unset($field['#attributes']['id']);
        }
        element_set_attributes($field, array('id', 'name', 'cols', 'rows'));
        $field['#attributes']['class'][] = 'layui-textarea';
        $form->textarea($name, $field);
        break;
      case 'hidden':
        $form->hidden($name, $field['#value']);
        break;
      case 'img':
        $form->img($name, $field);
        break;
      case 'captcha':
        element_set_attributes($field, array('id'));
        $form->captcha($name, $field);
        break;
      case 'markup':
        $form->markup($name, $field);
        break;
      case 'submit':
        $submit_field = $field;
        break;
      default:
        break;
      }
    }
  }

  if(!empty($submit_field)){
    $submit_field['#attributes']['type'] = 'submit';
    $form->submit($submit_field['#name'], $submit_field);
  }

  return $form->end($form_id);
}

/**
 * hunter submit form api
 */
function hunter_form_submit($parms, $module = NULl, $result = NULL) {
  global $app;

  foreach ($app->getModuleHandle()->getImplementations('form_submit') as $module_name) {
    $args = array($parms, $module, $result);
    $call_result = $app->getModuleHandle()->invoke($module_name, 'form_submit', $args);
    if($call_result){
      return $call_result;
    }
  }

  return $result;
}

/**
 * Get all talbes.
 */
function hunter_get_schema($table = NULL) {
  $result = db_query("SHOW TABLES")->fetchAll();
  $tables = array();
  $schemas = array();
  foreach($result as $row){
    $tables[] = $row->Tables_in_dibiao;
  }

  if(!empty($tables)){
    foreach ($tables as $module) {
      $install_file = 'module/'.$module.'/'.$module.'.install';
      if(file_exists($install_file)){
        require_once $install_file;

        $schema_fun = $module.'_schema';
        if (function_exists($schema_fun)) {
          $schemas[$module] = $schema_fun();
        }
      }
    }
  }
  return $table ? $schemas[$table] : $schemas;
}

/**
 * Download the dump file
 */
function hunter_download($filename, $conent) {
    if (!is_dir(dirname($filename))){
      mkdir(dirname($filename), 0755, true);
    }
    // Save content in file
    file_put_contents($filename, $conent);
    header('Content-disposition: attachment; filename="'.basename($filename).'"');
    header('Content-type: application/octet-stream');

    readfile($filename);
}

/**
 * Determines whether a given module exists.
 *
 * @param string $module
 *   The name of the module (without the .module extension).
 *
 * @return bool
 *   TRUE if the module is both installed and enabled, FALSE otherwise.
 */
function module_exists($module) {
  global $app;
  return $app->getModuleHandle()->moduleExists($module);
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

if (!function_exists('clean')) {
    /**
     * filter the string.
     *
     * @return string
     */
    function clean($value) {
      $antiXss = new AntiXSS();
      return $antiXss->xss_clean($value);
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
      $csrf = new CSRF();
      return $csrf->getHiddenInputString();
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    function csrf_token() {
      $csrf = new CSRF();
      return $csrf->getToken();
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
