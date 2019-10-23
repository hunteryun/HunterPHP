<?php

/**
 * @file
 *
 * FastCache
 */
namespace Hunter\Core\Cache;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;

class FastCache {

  private static $instance = null;
  private $conn;

  private function __construct(){
    $cache_dir = $GLOBALS['cache_dir'];
    $this->conn = CacheManager::getInstance('files', new Config(["path" => $cache_dir, "itemDetailedDate" => false]));
  }

  public static function getInstance(){
    if(!self::$instance){
      self::$instance = new FastCache();
    }
    return self::$instance;
  }

  public function getConnection(){
    return $this->conn;
  }

  public function isCached($key){
    $CachedString = $this->conn->getItem($key);
    return $CachedString->isHit();
  }

  public function set($key, $data, $expire = 60){
    $CachedString = $this->conn->getItem($key);
    $CachedString->set($data)->expiresAfter($expire);
    $this->conn->save($CachedString);
  }

  public function get($key){
    return $this->conn->getItem($key)->get();
  }

  private function __clone(){
  }

}
