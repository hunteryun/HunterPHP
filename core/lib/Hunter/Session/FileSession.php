<?php

/**
 * @file
 *
 * Session
 */

namespace Hunter\Core\Session;

use SessionHandlerInterface;
use Exception;

/**
 * session 文件存储接管
 */
class FileSession {

      protected $savePath;

      protected $_prefix = 'hunter_';

      private $_started = false;

      public function __construct($config = array()) {
          $this->savePath = 'sites/tmp';
          if (!is_dir($this->savePath)){
            mkdir($this->savePath, 0777, true);
          }

          if (!is_dir($this->savePath)) {
              throw new Exception("Cannot use FileSessionHandler, directory '{$this->savePath}' not found", 1);
          }

          if (!is_writable($this->savePath)) {
              throw new Exception("Cannot use FileSessionHandler, directory '{$this->savePath}' is not writable", 2);
          }
      }

      public function getSessionFullName() {
          return $this->getSavePath() . $this->_prefix . $this->getId();
      }

      public function open() {
        if ($this->getIsActive()) {
            return;
        }

        $file = $this->getSessionFullName();
        if( file_exists($file) && is_file($file) ) {
            $data = file_get_contents($file);
            $_SESSION = (array) json_decode($data);
        }else{
            $_SESSION = [];
        }
        $this->_started = true;
      }

      public function getId() {
          if( isset($_COOKIE['hunter_session_id']) ){
              $id = $_COOKIE['hunter_session_id'];
          }else{
              $id = uniqid();
              setcookie('hunter_session_id', $id, time()+3600*24, '/');
          }

          return $id;
      }

      public function close() {
          return true;
      }

      public function get($key, $defaultValue = null) {
          $this->open();
          return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
      }

      public function set($key, $value) {
          $this->open();
          $_SESSION[$key] = $value;
          file_put_contents($this->getSessionFullName(), json_encode($_SESSION));
      }

      public function delete($key) {
          $this->open();
          if (isset($_SESSION[$key])) {
              $value = $_SESSION[$key];
              unset($_SESSION[$key]);
              return $value;
          }
          return null;
      }

      public function getIsActive() {
          return $this->_started;
      }

      public function getSavePath() {
          if( strrpos( $this->savePath, '/') !==0 ){
              $this->savePath .= '/';
          }
          return $this->savePath;
      }

      public function gc($lifetime) {
          foreach (glob("{$this->sess_path}/{$this->prefix}*") as $file) {
              if (filemtime($file) + $lifetime < time() && file_exists($file)) {
                  unlink($file);
              }
          }

          return true;
      }
  }
