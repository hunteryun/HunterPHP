<?php

/**
 * @file
 *
 * Log
 */

namespace Hunter\Core\Database;

class Log {

    protected $queryLog = array();
    protected $target   = 'default';

    /**
     * 析构函数
     *
     * @param $key
     *   数据库链接
     */
    public function __construct($target = 'default') {
        $this->target = $target;
    }

    /**
     * 开始记录
     *
     * @param $log_key
     *   记录标识
     */
    public function start($log_key) {
        if (empty($this->queryLog[$log_key])) {
            $this->init($log_key);
        }
    }

    /**
     * 获取记录
     *
     * @param $log_key
     *   记录标识
     */
    public function get($log_key) {
        return $this->queryLog[$log_key];
    }

    /**
     * 重置记录
     *
     * @param $log_key
     *   记录标识
     */
    public function init($log_key) {
        $this->queryLog[$log_key] = array();
    }

    /**
     * 清空记录
     *
     * @param $log_key
     *   记录标识
     */
    public function clear($log_key) {
        unset($this->queryLog[$log_key]);
    }

    /**
     * 执行记录
     *
     * @param $log_key
     *   记录标识
     */
    public function log(Statement $statement, $args, $time) {
        foreach (array_keys($this->queryLog) as $key) {
            $this->queryLog[$key][] = array(
                'query'  => $statement->getQueryString(),
                'args'   => $args,
                'target' => $statement->dbh->getTarget(),
                'time'   => $time,
            );
        }
    }

}
