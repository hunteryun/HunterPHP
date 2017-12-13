<?php

/**
 * @file
 *
 * DB Connection
 */

namespace Hunter\Core\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO抽象层
 *
 * @see http://php.net/manual/book.pdo.php
 */
class Connection extends PDO {

    /**
     * 库配置的目标
     *
     * @var string
     */
    protected $target = null;

    /**
     * 库配置
     *
     * @var array
     */
    protected $options = array();

    /**
     * 日志记录器
     *
     * @var Hunter\Core\Database\Log
     */
    protected $logger = null;

    /**
     * 表前缀
     *
     * @var array
     */
    protected $prefixes = array();

    /**
     * {table}前缀替换前
     *
     * @var array
     */
    protected $prefixSearch = array();

    /**
     * {table}前缀替换后
     *
     * @var array
     */
    protected $prefixReplace = array();

    /**
     * Statement
     *
     * @var string
     */
    protected $statementClass = 'Hunter\\Core\\Database\\Statement';

    /**
     * 是否支持事务
     *
     * @var bool
     */
    protected $transactionSupport = true;

    /**
     * 事务层级数组
     *
     * @var array
     */
    protected $transactionLayers = array();

    /**
     * 析构函数
     *
     * @param array
     *   数据连接配置数组
     */
    public function __construct(array $options = array()) {
        $this->transactionSupport = !isset($options['transactions']) || ($options['transactions'] !== false);
        if (isset($options['driver']) && $options['driver'] == 'sqlite') {
            $dsn = 'sqlite:' . $options['database'];
            $this->statementClass = 'Hunter\\Core\\Database\\sqlite\\Statement';
        } elseif(isset($options['unix_socket'])) {
            $dsn = 'mysql:unix_socket=' . $options['unix_socket'];
        } else {
            $dsn = 'mysql:host=' . $options['host'] . ';port=' . (empty($options['port']) ? 3306 : $options['port']);
        }
        if (!empty($options['database']) && !isset($options['driver'])) {
            $dsn .= ';dbname=' . $options['database'];
        }
        $options += array(
            'pdo' => array(),
        );
        $options['pdo'] += array(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES         => true,
            PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        );

        if (isset($options['driver']) && $options['driver'] == 'sqlite') {
          $options['pdo'] += array(
            // Convert numeric values to strings when fetching.
            PDO::ATTR_STRINGIFY_FETCHES => TRUE,
          );
        }

        $this->options = $options;
        $this->setPrefix(isset($options['prefix']) ? $options['prefix'] : '');

        if (isset($options['driver']) && $options['driver'] == 'sqlite') {
          parent::__construct($dsn, '', '', $options['pdo']);
        }else{
          parent::__construct($dsn, $options['username'], $options['password'], $options['pdo']);
          $charset = empty($options['charset']) ? 'utf8' : $options['charset'];
          if (!empty($options['collation'])) {
              $this->exec('SET NAMES ' . $charset . ' COLLATE ' . $options['collation']);
          } else {
              $this->exec('SET NAMES ' . $charset);
          }
        }

        if (!empty($this->statementClass)) {
            $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array($this->statementClass, array($this)));
        }
    }

    /**
     * 读取该连接的库配置信息
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * 销毁这个连接对象
     */
    public function destroy() {
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatement', array()));
    }

    /**
     * 设置库目标
     */
    public function setTarget($target = null) {
        if (!isset($this->target)) {
            $this->target = $target;
        }
    }

    /**
     * 获取库目标
     *
     * @see Log.log
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * 设置日志记录器
     *
     * @param object $logger
     */
    public function setLogger(Log $logger) {
        $this->logger = $logger;
    }

    /**
     * 获取日志记录器
     *
     * @var Hunter\Core\Database\Log
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * 直接SQL
     *
     * @see db_query
     */
    public function query($query, array $args = array(), $options = array()) {
        $options += $this->defaultOptions();
        try {
            if (is_object($query)) {
                $stmt = $query;
                $stmt->execute(null, $options);
            } else {
                if(isset($this->options['driver']) && $this->options['driver'] == 'sqlite'){
                  $query = str_replace('ESCAPE \'\\\\\'', 'ESCAPE \'/\'', $query);
                }
                $this->expandArguments($query, $args);
                $stmt = $this->prepareQuery($query);
                $stmt->execute($args, $options);
            }
            switch ($options['return']) {
                case Database::RETURN_STATEMENT:
                    return $stmt;
                case Database::RETURN_AFFECTED:
                    return $stmt->rowCount();
                case Database::RETURN_INSERT_ID:
                    return $this->lastInsertId();
                case Database::RETURN_NULL:
                    return null;
                default:
                    throw new PDOException('Invalid return directive: ' . $options['return']);
            }
        }
        catch (PDOException $e) {
            if ($options['throw_exception']) {
                throw $e;
            }
            return null;
        }
    }

    /**
     * 范围查询SQL
     *
     * @see db_query_range
     */
    public function queryRange($query, $from, $count, array $args = array(), array $options = array()) {
      return $this->query($query . ' LIMIT ' . (int) $from . ', ' . (int) $count, $args, $options);
    }

    /**
     * 前置query处理
     *
     * @return Hunter\Core\Database\Statement
     */
    public function prepareQuery($sql) {
        $sql = $this->replacePrefix($sql);
        return parent::prepare($sql);
    }

    /**
     * 转换前缀
     */
    public function replacePrefix($str) {
        return str_replace($this->prefixSearch, $this->prefixReplace, $str);
    }

    /**
     * SELECT
     *
     * @return Hunter\Core\Database\Select
     */
    public function select($table, $alias = null, array $options = array()) {
        return new Select($table, $alias, $this, $options);
    }

    /**
     * INSERT
     *
     * @return Hunter\Core\Database\Insert
     */
    public function insert($table, array $options = array()) {
        return new Insert($this, $table, $options);
    }

    /**
     * UPDATE
     *
     * @return Hunter\Core\Database\Update
     */
    public function update($table, array $options = array()) {
        return new Update($this, $table, $options);
    }

    /**
     * DELETE
     *
     * @return Hunter\Core\Database\Delete
     */
    public function delete($table, array $options = array()) {
        return new Delete($this, $table, $options);
    }

    /**
     * MERGE
     *
     * @return Hunter\Core\Database\Merge
     */
    public function merge($table, array $options = array()) {
        return new Merge($this, $table, $options);
    }

    /**
     * Schema
     *
     * @return Hunter\Core\Database\Schema
     */
    public function schema(array $options = array()) {
        $options = $this->options + $options;
        return new Schema($this, $options);
    }

    /**
     * 返回数据库版本信息
     *
     * implements PDO
     */
    public function version() {
        return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * 操作符关联
     */
    public function mapConditionOperator($operator) {
        return null;
    }

    /**
     * 转义LIKE时的一些特殊字符
     */
    public function escapeLike($string) {
        return addcslashes($string, '\%_');
    }

    /**
     * 开始一个事务
     */
    public function startTransaction($name = '') {
        return new Transaction($this, $name);
    }

    /**
     * 是否已经在事务中
     */
    public function inTransaction() {
        return ($this->transactionDepth() > 0);
    }

    /**
     * 读取事务的层级数
     */
    public function transactionDepth() {
        return count($this->transactionLayers);
    }

    /**
     * 执行事务query
     */
    public function pushTransaction($name) {
        if (!$this->transactionSupport) {
            return;
        }
        if (isset($this->transactionLayers[$name])) {
            throw new RuntimeException($name . ' is already in use.');
        }
        if ($this->inTransaction()) {
            $this->query('SAVEPOINT ' . $name);
        } else {
            parent::beginTransaction();
        }
        $this->transactionLayers[$name] = $name;
    }

    /**
     * 提交事务
     */
    public function popTransaction($name) {
        if (!$this->transactionSupport) {
            return;
        }
        if (!isset($this->transactionLayers[$name])) {
            return;
        }
        $this->transactionLayers[$name] = false;
        $this->popCommittableTransactions();
    }

    /**
     * 事务回滚
     */
    public function rollback($savepoint_name = 'default_transaction') {
        if (!$this->transactionSupport) {
            return;
        }
        if (!$this->inTransaction()) {
            throw new RuntimeException('not in transaction.');
        }
        if (!isset($this->transactionLayers[$savepoint_name])) {
            throw new RuntimeException('savepoint is not exists.');
        }
        $rolled_back_other_active_savepoints = false;
        while ($savepoint = array_pop($this->transactionLayers)) {
            if ($savepoint == $savepoint_name) {
                if (empty($this->transactionLayers)) {
                    break;
                }
                $this->query('ROLLBACK TO SAVEPOINT ' . $savepoint);
                $this->popCommittableTransactions();
                if ($rolled_back_other_active_savepoints) {
                    throw new RuntimeException();
                }
                return;
            }
            else {
                $rolled_back_other_active_savepoints = true;
            }
        }
        parent::rollBack();
        if ($rolled_back_other_active_savepoints) {
            throw new RuntimeException();
        }
    }

    /**
     * 执行事务query
     */
    protected function popCommittableTransactions() {
        foreach (array_reverse($this->transactionLayers) as $name => $active) {
            if ($active) {
                break;
            }
            unset($this->transactionLayers[$name]);
            if (empty($this->transactionLayers)) {
                if (!parent::commit()) {
                    throw new RuntimeException('commit failed.');
                }
            } else {
                $this->query('RELEASE SAVEPOINT ' . $name);
            }
        }
    }

    /**
     * 默认的返回结果配置
     */
    protected function defaultOptions() {
        return array(
          'target'          => 'default',
          'fetch'           => PDO::FETCH_OBJ,
          'return'          => Database::RETURN_STATEMENT,
          'throw_exception' => true,
        );
    }

    /**
     * 替换参数占位符
     */
    protected function expandArguments(&$sql, &$args) {
        $modified = false;
        //为子层生成占位符
        foreach (array_filter($args, 'is_array') as $key => $data) {
            $new_keys = array();
            foreach (array_values($data) as $i => $value) {
                $new_keys[$key . '_' . $i] = $value;
            }
            $sql = preg_replace('#' . $key . '\b#', implode(', ', array_keys($new_keys)), $sql);
            unset($args[$key]);
            $args += $new_keys;
            $modified = true;
        }

        return $modified;
    }

    /**
     * 设置{table}替换列表
     */
    protected function setPrefix($prefix) {
        if (is_array($prefix)) {
            $this->prefixes = $prefix + array('default' => '');
        } else {
            $this->prefixes = array('default' => $prefix);
        }

        $this->prefixSearch  = array();
        $this->prefixReplace = array();
        foreach ($this->prefixes as $key => $val) {
            if ($key != 'default') {
                $this->prefixSearch[]  = '{' . $key . '}';
                $this->prefixReplace[] = $val . $key;
            }
        }

        $this->prefixSearch[]  = '{';
        $this->prefixReplace[] = $this->prefixes['default'];
        $this->prefixSearch[]  = '}';
        $this->prefixReplace[] = '';
    }

}
