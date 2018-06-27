<?php

/**
 * @file
 *
 * Schema
 */

namespace Hunter\Core\Database;

class Schema extends Query {

    /**
     * 最大注释长度.
     */
    const COMMENT_MAX_TABLE = 60;

    /**
     * 数据库连接信息
     *
     * @var array
     */
    protected $options = array();

    /**
     * Schema信息
     *
     * @var array
     */
    protected $schema = array();

    /**
     * MySQL字符串类型.
     */
    protected $mysqlStringTypes = array(
      'VARCHAR',
      'CHAR',
      'TINYTEXT',
      'MEDIUMTEXT',
      'LONGTEXT',
      'TEXT',
    );

    /**
     * 析构函数
     */
    public function __construct(Connection $connection, $options) {
        parent::__construct($connection, $options);
        if (!empty($options['database'])) {
            $this->explainSchema($options);
        }
        $this->options = $options;
    }

    /**
     * 获取Schema信息
     */
    public function getSchema() {
        return $this->schema;
    }

    /**
     * 安装数据库表
     */
    public function installSchema($schema) {
        foreach ($schema as $name => $table) {
          $info = $this->createTable($name, $table);
        }
        if($info instanceof Statement){
          return TRUE;
        }else{
          return FALSE;
        }
    }

    /**
     * 执行获取Schema
     */
    public function explainSchema($options) {
        $info = $this->connection->query('
                SELECT
                    table_name, column_name, column_default, is_nullable, data_type, numeric_scale, column_comment, column_key, extra
                FROM information_schema.columns
                WHERE table_schema = :database
                ', array(':database' => $options['database']))->fetchAll();
        foreach ($info as $v) {
            $this->schema[$v->table_name][$v->column_name] = array(
                'description' => $v->column_comment,
                'type'        => $v->data_type,
                'default'     => $v->column_default,
                'not null'    => $v->is_nullable == 'NO',
                'key type'    => $v->column_key, //PRI MUL UNI
                'increment'   => $v->extra == 'auto_increment',
                'decimal'     => $v->numeric_scale ?: 0,
            );
        }

        return $this;
    }

    /**
     * 表是否存在
     */
    public function tableExists($table) {
        $table = $this->connection->replacePrefix('{' . $table . '}');
        return isset($this->schema[$table]);
    }

    /**
     * 字段是否存在
     */
    public function fieldExists($table, $column) {
        $table = $this->connection->replacePrefix('{' . $table . '}');
        return isset($this->schema[$table][$column]);
    }

    /**
     * 索引是否存在
     */
    public function indexExists($table, $name) {
        $row = $this->connection->query('SHOW INDEX FROM {' . $table . "} WHERE key_name = '$name'")->fetchAssoc();
        return isset($row['Key_name']);
    }

    /**
     * 获取表字段列表
     */
    public function fieldLists($table) {
        $table = $this->connection->replacePrefix('{' . $table . '}');
        return $this->schema[$table];
    }

    /**
     * 创建表
     */
    public function createTable($name, $table) {
        if ($this->tableExists($name)) {
            throw new SchemaException("$name already exists.");
        }
        if (is_string($table)) {
            $info = $this->connection->query($table);
        }else{
          $statements = $this->createTableSql($name, $table);
          foreach ($statements as $statement) {
            $info = $this->connection->query($statement);
          }
        }
        return $info;
    }

    /**
     * 修改表名
     */
    public function renameTable($table, $new_name) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        if ($this->tableExists($new_name)) {
            throw new SchemaException("$new_name already exists.");
        }
        return $this->connection->query('ALTER TABLE {' . $table . '} RENAME TO `{' . $new_name .'}`');
    }

    /**
     * 删除表
     */
    public function dropTable($table) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        $this->connection->query('DROP TABLE {' . $table . '}');
        return true;
    }

    /**
     * 添加字段
     */
    public function addField($table, $field, $spec) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        if ($this->fieldExists($table, $field)) {
            throw new SchemaException("$table.$field already exists.");
        }
        if (is_string($spec)) {
            $this->connection->query($spec);
        }
        //todo spec is array
    }

    /**
     * 删除字段
     */
    public function dropField($table, $field) {
        if (!$this->fieldExists($table, $field)) {
            return false;
        }
        $this->connection->query('ALTER TABLE {' . $table . '} DROP `' . $field . '`');
        return true;
    }

    /**
     * 变更字段
     */
    public function changeField($table, $field, $field_new, $spec) {
        if (!$this->fieldExists($table, $field)) {
            throw new SchemaException("$table.$field doesn't exists.");
        }
        if (($field != $field_new) && $this->fieldExists($table, $field_new)) {
            throw new SchemaException("$table.$field_new already exists.");
        }
        if (is_string($spec)) {
            $this->connection->query($spec);
        }
        //todo spec is array
    }

    /**
     * 设置字段默认值
     */
    public function fieldSetDefault($table, $field, $default) {
        if (!$this->fieldExists($table, $field)) {
            throw new SchemaException("$table.$field doesn't exists.");
        }
        if (!isset($default)) {
            $default = 'null';
        } else {
            $default = is_string($default) ? "'$default'" : $default;
        }
        $this->connection->query('ALTER TABLE {' . $table . '} ALTER COLUMN `' . $field . '` SET DEFAULT ' . $default);
    }

    /**
     * 设置字段无默认值
     */
    public function fieldSetNoDefault($table, $field) {
        if (!$this->fieldExists($table, $field)) {
            throw new SchemaException("$table.$field doesn't exists.");
        }
        $this->connection->query('ALTER TABLE {' . $table . '} ALTER COLUMN `' . $field . '` DROP DEFAULT');
    }

    /**
     * 添加主键
     */
    public function addPrimaryKey($table, $fields) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        if ($this->indexExists($table, 'PRIMARY')) {
            throw new SchemaException("$table primary key already exists.");
        }
        $this->connection->query('ALTER TABLE {' . $table . '} ADD PRIMARY KEY (' . $this->createKeySql($fields) . ')');
    }

    /**
     * 删除主键
     */
    public function dropPrimaryKey($table) {
        if (!$this->indexExists($table, 'PRIMARY')) {
            return false;
        }
        $this->connection->query('ALTER TABLE {' . $table . '} DROP PRIMARY KEY');
        return true;
    }

    /**
     * 添加唯一索引
     */
    public function addUniqueKey($table, $name, $fields) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        if ($this->indexExists($table, $name)) {
            throw new SchemaException("$table INDEX $name already exists.");
        }
        $this->connection->query('ALTER TABLE {' . $table . '} ADD UNIQUE KEY `' . $name . '` (' . $this->createKeySql($fields) . ')');
    }

    /**
     * 删除唯一索引
     */
    public function dropUniqueKey($table, $name) {
        if (!$this->indexExists($table, $name)) {
            return false;
        }
        $this->connection->query('ALTER TABLE {' . $table . '} DROP KEY `' . $name . '`');
        return true;
    }

    /**
     * 添加索引
     */
    public function addIndex($table, $name, $fields) {
        if (!$this->tableExists($table)) {
            throw new SchemaException("$table doesn't exists.");
        }
        if ($this->indexExists($table, $name)) {
            throw new SchemaException("$table INDEX $name already exists.");
        }
        $this->connection->query('ALTER TABLE {' . $table . '} ADD INDEX `' . $name . '` (' . $this->createKeySql($fields) . ')');
    }

    /**
     * 删除索引
     */
    public function dropIndex($table, $name) {
        if (!$this->indexExists($table, $name)) {
            return false;
        }
        $this->connection->query('ALTER TABLE {' . $table . '} DROP INDEX `' . $name . '`');
        return true;
    }

    //拼缀索引SQL
    protected function createKeySql($fields) {
        $return = array();
        foreach ((array)$fields as $field) {
            if (is_array($field)) {
                $return[] = '`' . $field[0] . '`(' . $field[1] . ')';
            } else {
                $return[] = '`' . $field . '`';
            }
        }
        return implode(', ', $return);
    }

    /**
     * 创建数据表SQL
     */
    protected function createTableSql($name, $table) {
        $info = $this->options;

        // Provide defaults if needed.
        $table += array(
          'mysql_engine' => 'InnoDB',
          'mysql_character_set' => 'utf8mb4',
        );

        $sql = "CREATE TABLE {" . $name . "} (\n";

        // Add the SQL statement for each field.
        foreach ($table['fields'] as $field_name => $field) {
          $sql .= $this->createFieldSql($field_name, $this->processField($field)) . ", \n";
        }

        // Process keys & indexes.
        $keys = $this->createKeysSql($table);
        if (count($keys)) {
          $sql .= implode(", \n", $keys) . ", \n";
        }

        // Remove the last comma and space.
        $sql = substr($sql, 0, -3) . "\n) ";

        $sql .= 'ENGINE = ' . $table['mysql_engine'] . ' DEFAULT CHARACTER SET ' . $table['mysql_character_set'];
        // By default, MySQL uses the default collation for new tables, which is
        // 'utf8mb4_general_ci' for utf8mb4. If an alternate collation has been
        // set, it needs to be explicitly specified.
        // @see DatabaseConnection_mysql
        if (!empty($info['collation'])) {
          $sql .= ' COLLATE ' . $info['collation'];
        }

        // Add table comment.
        if (!empty($table['description'])) {
          $sql .= ' COMMENT ' . $this->prepareComment($table['description'], self::COMMENT_MAX_TABLE);
        }

        return array($sql);
    }

    /**
     * Set database-engine specific properties for a field.
     *
     * @param $field
     *   A field description array, as specified in the schema documentation.
     */
    protected function processField($field) {

      if (!isset($field['size'])) {
        $field['size'] = 'normal';
      }

      // Set the correct database-engine specific datatype.
      // In case one is already provided, force it to uppercase.
      if (isset($field['mysql_type'])) {
        $field['mysql_type'] = Unicode::strtoupper($field['mysql_type']);
      }
      else {
        $map = $this->getFieldTypeMap();
        $field['mysql_type'] = $map[$field['type'] . ':' . $field['size']];
      }

      if (isset($field['type']) && $field['type'] == 'serial') {
        $field['auto_increment'] = TRUE;
      }

      return $field;
    }

    public function getFieldTypeMap() {
      // Put :normal last so it gets preserved by array_flip. This makes
      // it much easier for modules (such as schema.module) to map
      // database types back into schema types.
      // $map does not use hunter_static as its value never changes.
      static $map = array(
        'varchar_ascii:normal' => 'VARCHAR',

        'varchar:normal'  => 'VARCHAR',
        'char:normal'     => 'CHAR',

        'text:tiny'       => 'TINYTEXT',
        'text:small'      => 'TINYTEXT',
        'text:medium'     => 'MEDIUMTEXT',
        'text:big'        => 'LONGTEXT',
        'text:normal'     => 'TEXT',

        'serial:tiny'     => 'TINYINT',
        'serial:small'    => 'SMALLINT',
        'serial:medium'   => 'MEDIUMINT',
        'serial:big'      => 'BIGINT',
        'serial:normal'   => 'INT',

        'int:tiny'        => 'TINYINT',
        'int:small'       => 'SMALLINT',
        'int:medium'      => 'MEDIUMINT',
        'int:big'         => 'BIGINT',
        'int:normal'      => 'INT',

        'float:tiny'      => 'FLOAT',
        'float:small'     => 'FLOAT',
        'float:medium'    => 'FLOAT',
        'float:big'       => 'DOUBLE',
        'float:normal'    => 'FLOAT',

        'numeric:normal'  => 'DECIMAL',

        'blob:big'        => 'LONGBLOB',
        'blob:normal'     => 'BLOB',
      );
      return $map;
    }

    protected function createKeysSql($spec) {
      $keys = array();

      if (!empty($spec['primary key'])) {
        $keys[] = 'PRIMARY KEY (' . $this->createKeySql($spec['primary key']) . ')';
      }
      if (!empty($spec['unique keys'])) {
        foreach ($spec['unique keys'] as $key => $fields) {
          $keys[] = 'UNIQUE KEY `' . $key . '` (' . $this->createKeySql($fields) . ')';
        }
      }
      if (!empty($spec['indexes'])) {
        $indexes = $this->getNormalizedIndexes($spec);
        foreach ($indexes as $index => $fields) {
          $keys[] = 'INDEX `' . $index . '` (' . $this->createKeySql($fields) . ')';
        }
      }

      return $keys;
    }

    /**
     * 获取正常索引.
     */
    protected function getNormalizedIndexes(array $spec) {
      $indexes = isset($spec['indexes']) ? $spec['indexes'] : [];
      foreach ($indexes as $index_name => $index_fields) {
        foreach ($index_fields as $index_key => $index_field) {
          // Get the name of the field from the index specification.
          $field_name = is_array($index_field) ? $index_field[0] : $index_field;
          // Check whether the field is defined in the table specification.
          if (isset($spec['fields'][$field_name])) {
            // Get the MySQL type from the processed field.
            $mysql_field = $this->processField($spec['fields'][$field_name]);
            if (in_array($mysql_field['mysql_type'], $this->mysqlStringTypes)) {
              // Check whether we need to shorten the index.
              if ((!isset($mysql_field['type']) || $mysql_field['type'] != 'varchar_ascii') && (!isset($mysql_field['length']) || $mysql_field['length'] > 191)) {
                // Limit the index length to 191 characters.
                $this->shortenIndex($indexes[$index_name][$index_key]);
              }
            }
          }
          else {
            throw new SchemaException("MySQL needs the '$field_name' field specification in order to normalize the '$index_name' index");
          }
        }
      }
      return $indexes;
    }

    /**
     * Helper function for normalizeIndexes().
     *
     * Shortens an index to 191 characters.
     *
     * @param array $index
     *   The index array to be used in createKeySql.
     *
     * @see Hunter\Core\Database\Driver\mysql\Schema::createKeySql()
     * @see Hunter\Core\Database\Driver\mysql\Schema::normalizeIndexes()
     */
    protected function shortenIndex(&$index) {
      if (is_array($index)) {
        if ($index[1] > 191) {
          $index[1] = 191;
        }
      }
      else {
        $index = array($index, 191);
      }
    }

    /**
     * 创建字段SQL.
     */
    protected function createFieldSql($name, $spec) {
      $sql = "`" . $name . "` " . $spec['mysql_type'];

      if (in_array($spec['mysql_type'], $this->mysqlStringTypes)) {
        if (isset($spec['length'])) {
          $sql .= '(' . $spec['length'] . ')';
        }
        if (!empty($spec['binary'])) {
          $sql .= ' BINARY';
        }
        // Note we check for the "type" key here. "mysql_type" is VARCHAR:
        if (isset($spec['type']) && $spec['type'] == 'varchar_ascii') {
          $sql .= ' CHARACTER SET ascii COLLATE ascii_general_ci';
        }
      }
      elseif (isset($spec['precision']) && isset($spec['scale'])) {
        $sql .= '(' . $spec['precision'] . ', ' . $spec['scale'] . ')';
      }

      if (!empty($spec['unsigned'])) {
        $sql .= ' unsigned';
      }

      if (isset($spec['not null'])) {
        if ($spec['not null']) {
          $sql .= ' NOT NULL';
        }
        else {
          $sql .= ' NULL';
        }
      }

      if (!empty($spec['auto_increment'])) {
        $sql .= ' auto_increment';
      }

      // $spec['default'] can be NULL, so we explicitly check for the key here.
      if (array_key_exists('default', $spec)) {
        $sql .= ' DEFAULT ' . $this->escapeDefaultValue($spec['default']);
      }

      if (empty($spec['not null']) && !isset($spec['default'])) {
        $sql .= ' DEFAULT NULL';
      }

      // Add column comment.
      if (!empty($spec['description'])) {
        $sql .= ' COMMENT ' . $this->prepareComment($spec['description'], self::COMMENT_MAX_TABLE);
      }

      return $sql;
    }

    /**
     * Prepare a table or column comment for database query.
     *
     * @param $comment
     *   The comment string to prepare.
     * @param $length
     *   Optional upper limit on the returned string length.
     *
     * @return
     *   The prepared comment.
     */
    public function prepareComment($comment, $length = NULL) {
      // Remove semicolons to avoid triggering multi-statement check.
      $comment = strtr($comment, [';' => '.']);
      return $this->connection->quote($comment);
    }

    /**
     * Return an escaped version of its parameter to be used as a default value
     * on a column.
     *
     * @param mixed $value
     *   The value to be escaped (int, float, null or string).
     *
     * @return string|int|float
     *   The escaped value.
     */
    protected function escapeDefaultValue($value) {
      if (is_null($value)) {
        return 'NULL';
      }
      return is_string($value) ? $this->connection->quote($value) : $value;
    }

}

class SchemaException extends \Exception {}
