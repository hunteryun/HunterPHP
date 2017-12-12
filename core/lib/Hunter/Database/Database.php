<?php

/**
 * @file
 *
 * 数据库类
 */

namespace Hunter\Core\Database;

abstract class Database {

    //返回:一个NULL值
    const RETURN_NULL = 0;

    //返回:statement对象
    const RETURN_STATEMENT = 1;

    //返回:affected数目
    const RETURN_AFFECTED = 2;

    //返回:last insert id
    const RETURN_INSERT_ID = 3;

    /**
     * 数据库链接
     *
     * @var array
     */
    protected static $connections = array();

    /**
     * 是否已经手工设置过配置
     *
     * @var bool
     */
    protected static $isConfiguration = false;

    /**
     * 数据库配置
     *
     * @var array
     */
    protected static $databaseInfo = array();

    /**
     * 当前链接
     *
     * @var string
     */
    protected static $target = 'default';

    /**
     * 日志
     *
     * @var array
     */
    protected static $logs = array();

    //记录Log
    final public static function startLog($log_key, $target = 'default') {
        if (empty(self::$logs[$target])) {
            self::$logs[$target] = new Log($target);
            if (!empty(self::$connections[$target])) {
                self::$connections[$target]->setLogger(self::$logs[$target]);
            }
        }
        self::$logs[$target]->start($log_key);

        return self::$logs[$target];
    }

    //读取Log
    final public static function getLog($log_key, $target = 'default') {
        if (empty(self::$logs[$target])) {
            return null;
        }
        $queries = self::$logs[$target]->get($log_key);
        self::$logs[$target]->clear($log_key);

        return $queries;
    }

    //设置连接信息
    final public static function setConfig($target, array $config = array()) {
        if (is_string($target)) {
            self::$databaseInfo[$target] = $config;
        }
        elseif (is_array($target)) {
            self::$databaseInfo = $target + self::$databaseInfo;
        }
        self::$isConfiguration = true;
    }

    //获取连接信息
    final public static function getConfig($target = null) {
        if (!isset($target)) {
            return self::$databaseInfo;
        }

        return isset(self::$databaseInfo[$target]) ? self::$databaseInfo[$target] : null;
    }

    //获取链接
    final public static function getConnection($target = 'default') {
        if (!isset(self::$connections[$target])) {
            self::$connections[$target] = self::openConnection($target);
        }

        return self::$connections[$target];
    }

    //打开链接
    final public static function openConnection($target) {
        if (empty(self::$databaseInfo)) {
            self::parseConnectionInfo();
        }

        if(isset(self::$databaseInfo[$target]['driver']) && self::$databaseInfo[$target]['driver'] == 'sqlite'){
          $driver_class = self::$databaseInfo[$target]['namespace'].'\\Connection';
          $connection = new $driver_class(self::$databaseInfo[$target]);
        }else {
          $connection = new Connection(self::$databaseInfo[$target]);
        }

        $connection->setTarget($target);
        if (!empty(self::$logs[$target])) {
            $connection->setLogger(self::$logs[$target]);
        }

        return $connection;
    }

    //解析数据库配置信息
    final public static function parseConnectionInfo() {
        global $databases;
        if (!self::$isConfiguration) {
            $databaseInfo = is_array($databases) ? $databases : array('default'=>array());
            self::$databaseInfo = $databaseInfo;
        }
    }

    //关闭链接
    public static function closeConnection($target = null) {
        if (isset($target)) {
            if (isset(self::$connections[$target])) {
                self::$connections[$target]->destroy();
                self::$connections[$target] = null;
                unset(self::$connections[$target]);
            }
        }
        else {
            foreach (self::$connections as $target => $connection) {
                self::$connections[$target]->destroy();
                self::$connections[$target] = null;
                unset(self::$connections[$target]);
            }
        }
    }

}
