<?php

/**
 * @file
 *
 * Query
 */

namespace Hunter\Core\Database;

class Query {
    
    /**
     * 库配置的目标
     *
     * @var string
     */
    protected $target;
    
    /**
     * 库连接
     *
     * @var Hunter\Core\Connection
     */
    protected $connection;
    
    /**
     * Query设置
     *
     * @var array
     */    
    protected $queryOptions;
    
    /**
     * 该条query的唯一码
     *
     * @var string
     */
    protected $uniqueIdentifier;
    
    /**
     * 占位符计数器
     *
     * @var integer
     */
    protected $nextPlaceholder = 0;
  
    /**
     * 析构函数
     *
     */
    public function __construct(Connection $connection, $options) {
        $this->uniqueIdentifier = uniqid('', true);
        $this->connection = $connection;
        $this->target = $this->connection->getTarget();
        $this->queryOptions = $options;
    }
    
    /**
     * 魔术方法
     *
     * 被serialize的时候,断开连接
     */
    public function __sleep() {
        $keys = get_object_vars($this);
        unset($keys['connection']);
        return array_keys($keys);
    }
    
    /**
     * 魔术方法
     *
     * 被unserialize的时候,重新连接
     */
    public function __wakeup() {
        $this->connection = Database::getConnection($this->target);
    }
    
    public function __clone() {
        $this->uniqueIdentifier = uniqid('', true);
    }
 
    /**
     * 获取该query的唯一标识码
     */
    public function uniqueIdentifier() {
        return $this->uniqueIdentifier;
    }
    
    /**
     * 获取下一个占位符计数
     */
    public function nextPlaceholder() {
        return $this->nextPlaceholder++;
    }
    
}
