<?php

/**
 * @file
 *
 * Statement
 *
 * @see http://php.net/manual/book.pdo.php
 */

namespace Hunter\Core\Database;

use PDO;
use PDOStatement;
 
class Statement extends PDOStatement {
    
    /**
     * 数据库连接
     */
    public $dbh;
    
    /**
     * 析构函数
     */
    protected function __construct(Connection $dbh) {
        $this->dbh = $dbh;
        $this->setFetchMode(PDO::FETCH_OBJ);
    }
    
    /**
     * 执行Statement
     *
     * @param $args
     *
     * @param $options
     *    配置参数
     *
     * @return true|false
     */
    public function execute($args = array(), $options = array()) {
        if (isset($options['fetch'])) {
            if (is_string($options['fetch'])) {
                $this->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $options['fetch']);
            } else {
                $this->setFetchMode($options['fetch']);
            }
        }
        //是否日志记录
        $logger = $this->dbh->getLogger();
        if (!empty($logger)) {
            $query_start = microtime(true);
        }

        $return = parent::execute($args);

        if (!empty($logger)) {
            $query_end = microtime(true);
            $logger->log($this, $args, $query_end - $query_start);
        }

        return $return;
    }
    
    /**
     * 获取QueryString
     *
     * implements PDOStatement
     */
    public function getQueryString() {
        return $this->queryString;
    }
    
    /**
     * 获取某列
     *
     * implements PDOStatement
     */
    public function fetchCol($index = 0) {
        return $this->fetchAll(PDO::FETCH_COLUMN, $index);
    }
    
    /**
     * 以某列为结果集的key
     */
    public function fetchAllAssoc($key, $fetch = null) {
        $return = array();
        if (isset($fetch)) {
            if (is_string($fetch)) {
                $this->setFetchMode(PDO::FETCH_CLASS, $fetch);
            } else {
                $this->setFetchMode($fetch);
            }
        }

        foreach ($this as $record) {
            $record_key = is_object($record) ? $record->$key : $record[$key];
            $return[$record_key] = $record;
        }

        return $return;
    }
    
    /**
     * 以某列为结果集的key, 以另外一列的结果集为value
     */
    public function fetchAllKeyed($key_index = 0, $value_index = 1) {
        $return = array();
        $this->setFetchMode(PDO::FETCH_NUM);
        foreach ($this as $record) {
            $return[$record[$key_index]] = $record[$value_index];
        }
        
        return $return;
    }
    
    /**
     * 从结果集中的下一行返回单独的一列
     *
     * implements PDOStatement
     */
    public function fetchField($index = 0) {
        return $this->fetchColumn($index);
    }
    
    /**
     * 从结果集中的下一行返回单独的一列
     *
     * implements PDOStatement
     */
    public function fetchAssoc() {
        return $this->fetch(PDO::FETCH_ASSOC);
    }
  
}
