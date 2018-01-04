<?php

/**
 * @file
 *
 * Session
 */

namespace Hunter\Core\Session;

class Session {

    /**
     * Handler
     */
    public static $handlers = array();

    /**
     * 配置
     */
    public static $configs = array();

    /**
     * 是否被加载过
     */
    public static $isLoaded = false;

    /**
     * 获得handler
     */
    public static function getHandler($config = array()) {
        static $prefix;
        if (!$prefix) {
            $prefix = md5(__DIR__);
        }
        if (!self::$isLoaded) {
            self::createFromGlobals();
        }
        $config = self::$configs + $config + array(
            'class'  => 'Hunter\Core\Session\PhpSession',
            'prefix' => $prefix,
            'expire' => 0,
        );
        $handler = $config['class'];
        if (!isset(self::$handlers[$handler])) {
            self::$handlers[$handler] = new $handler($config);
        }
        return self::$handlers[$handler];
    }

    /**
     * 从Global获取配置
     */
    public static function createFromGlobals() {
        global $sessions;
        if (!empty($sessions) && is_array($sessions)) {
            self::$configs  = $sessions;
            self::$isLoaded = true;
            return $sessions;
        } else {
            return array();
        }
    }

}
