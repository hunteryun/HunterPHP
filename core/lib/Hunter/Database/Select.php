<?php

/**
 * @file
 *
 * Select
 */

namespace Hunter\Core\Database;

class Select extends Query {
    
    protected $fields = array();
    
    protected $expressions = array();
    
    protected $tables = array();
    
    protected $order = array();
    
    protected $group = array();
    
    protected $where;
    
    protected $having;

    protected $distinct = false;
    
    protected $range;
    
    protected $prepared = false;
    
    protected $forUpdate = false;
    
    public function __construct($table, $alias = null, Connection $connection, $options = array()) {
        $options['return'] = Database::RETURN_STATEMENT;
        parent::__construct($connection, $options);
        $conjunction  = isset($options['conjunction']) ? $options['conjunction'] : 'AND';
        $this->where  = new Condition($conjunction);
        $this->having = new Condition($conjunction);
        $this->addJoin(null, $table, $alias);
    }
    
    public function condition($field, $value = null, $operator = null) {
        $this->where->condition($field, $value, $operator);        
        return $this;
    }
    
    public function &conditions() {
        return $this->where->conditions();
    }
    
    public function where($snippet, $args = array()) {
        $this->where->where($snippet, $args);        
        return $this;
    }
    
    public function isNull($field) {
        $this->where->isNull($field);
        return $this;
    }
    
    public function isNotNull($field) {
        $this->where->isNotNull($field);
        return $this;
    }    
    
    public function forUpdate($set = true) {
        if (isset($set)) {
            $this->forUpdate = $set;
        }
        
        return $this;
    }
    
    public function extend($extender_name) {
        if (!class_exists($extender_name)) {
            $extender_name = 'Hunter\\Core\\Database\\' . ucfirst($extender_name);
        }
        return new $extender_name($this, $this->connection);
    }
    
    public function distinct($distinct = true) {
        $this->distinct = $distinct;
        return $this;
    }
    
    public function addField($table_alias, $field, $alias = null) {
        if (empty($alias)) {
            $alias = $field;
        }
        if (!empty($this->fields[$alias])) {
            $alias = $table_alias . '_' . $field;
        }
        $alias_candidate = $alias;
        $count = 2;
        while (!empty($this->fields[$alias_candidate])) {
            $alias_candidate = $alias . '_' . $count++;
        }
        $alias = $alias_candidate;

        $this->fields[$alias] = array(
            'field' => $field,
            'table' => $table_alias,
            'alias' => $alias,
        );

        return $this;
    }
    
    public function fields($table_alias, array $fields = array()) {
        if ($fields) {
            foreach ($fields as $field) {
                $this->addField($table_alias, $field);
            }
        } else {
            $this->tables[$table_alias]['all_fields'] = true;
        }

        return $this;
    }
    
    public function addExpression($expression, $alias = null, $arguments = array()) {
        if (empty($alias)) {
            $alias = 'expression';
        }

        $alias_candidate = $alias;
        $count = 2;
        while (!empty($this->expressions[$alias_candidate])) {
            $alias_candidate = $alias . '_' . $count++;
        }
        $alias = $alias_candidate;

        $this->expressions[$alias] = array(
            'expression' => $expression,
            'alias'      => $alias,
            'arguments'  => $arguments,
        );

        return $this;
    }
    
    public function join($table, $alias = null, $condition = null, $arguments = array()) {
        return $this->addJoin('INNER', $table, $alias, $condition, $arguments);
    }
    
    public function innerJoin($table, $alias = null, $condition = null, $arguments = array()) {
        return $this->addJoin('INNER', $table, $alias, $condition, $arguments);
    }
    
    public function leftJoin($table, $alias = null, $condition = null, $arguments = array()) {
        return $this->addJoin('LEFT OUTER', $table, $alias, $condition, $arguments);
    }
    
    public function rightJoin($table, $alias = null, $condition = null, $arguments = array()) {
        return $this->addJoin('RIGHT OUTER', $table, $alias, $condition, $arguments);
    }
    
    public function addJoin($type, $table, $alias = null, $condition = null, $arguments = array()) {
        if (empty($alias)) {
            if (is_object($table)) {
                $alias = 'subquery';
            } else {
                $alias = $table;
            }
        }

        $alias_candidate = $alias;
        $count = 2;
        while (!empty($this->tables[$alias_candidate])) {
            $alias_candidate = $alias . '_' . $count++;
        }
        $alias = $alias_candidate;

        if (is_string($condition)) {
            $condition = str_replace('%alias', $alias, $condition);
        }

        $this->tables[$alias] = array(
            'join type' => $type,
            'table'     => $table,
            'alias'     => $alias,
            'condition' => $condition,
            'arguments' => $arguments,
        );

        return $this;
    }
    
    public function orderBy($field, $direction = 'ASC') {
        $this->order[$field] = $direction;
        return $this;
    }
    
    public function range($start = null, $length = null) {
        $this->range = func_num_args() ? array('start' => $start, 'length' => $length) : array();
        return $this;
    }
    
    public function groupBy($field) {
        $this->group[$field] = $field;
        return $this;
    }
  
    public function countQuery($subQuery = false) {
        $count = $this->prepareCountQuery();
        if ($subQuery) {
            $count->addExpression('1');
            $query = $this->connection->select($count, null, $this->queryOptions);
            $query->addExpression('COUNT(*)');
            return $query;
        } else {
            $count->addExpression('COUNT(*)');
            return $count;
        }
    }
    
    protected function prepareCountQuery() {
        $count = clone($this);
        $group_by = $count->getGroupBy();
        $having   = $count->havingConditions();

        if (!$count->distinct && !isset($having[0])) {
            //去掉所有select字段
            $fields = &$count->getFields();
            foreach (array_keys($fields) as $field) {
                if (empty($group_by[$field])) {
                    unset($fields[$field]);
                }
            }
            //去掉所有expressions字段
            $expressions = &$count->getExpressions();
            foreach (array_keys($expressions) as $field) {
                if (empty($group_by[$field])) {
                    unset($expressions[$field]);
                }
            }
            //去掉*字段
            foreach ($count->tables as $alias => &$table) {
                unset($table['all_fields']);
            }
        }

        $orders = &$count->getOrderBy();
        $orders = array();

        if ($count->distinct && !empty($group_by)) {
            $count->distinct = false;
        }

        return $count;
    }
    
    public function havingCondition($field, $value = null, $operator = null) {
        $this->having->condition($field, $value, $operator);
        return $this;
    }

    public function &havingConditions() {
        return $this->having->conditions();
    }

    public function havingArguments() {
        return $this->having->arguments();
    }

    public function having($snippet, $args = array()) {
        $this->having->where($snippet, $args);
        return $this;
    }

    public function havingCompile(Connection $connection) {
        return $this->having->compile($connection, $this);
    }
    
    public function havingIsNull($field) {
        $this->having->isNull($field);
        return $this;
    }

    public function havingIsNotNull($field) {
        $this->having->isNotNull($field);
        return $this;
    }

    public function &getFields() {
        return $this->fields;
    }
    
    public function &getExpressions() {
        return $this->expressions;
    }
    
    public function &getOrderBy() {
        return $this->order;
    }
    
    public function &getGroupBy() {
        return $this->group;
    }
    
    public function &getTables() {
        return $this->tables;
    }
    
    public function isPrepared() {
        return $this->prepared;
    }
    
    public function preExecute($query = null) {
        if (!isset($query)) {
            $query = $this;
        }
        if ($query->isPrepared()) {
            return true;
        }
        $this->prepared = true;

        return $this->prepared;
    }
    
    public function execute() {
        if (!$this->preExecute()) {
            return null;
        }
        $args = $this->getArguments();

        return $this->connection->query((string) $this, $args, $this->queryOptions);
    }
    
    public function getArguments($queryPlaceholder = null) {
        if (!isset($queryPlaceholder)) {
            $queryPlaceholder = $this;
        }
        $this->compile($this->connection, $queryPlaceholder);
        
        return $this->arguments();
    }
    
    public function compile(Connection $connection, $queryPlaceholder) {
        $this->where->compile($connection, $queryPlaceholder);
        $this->having->compile($connection, $queryPlaceholder);
        foreach ($this->tables as $table) {
            if (is_object($table['table'])) {
                $table['table']->compile($connection, $queryPlaceholder);
            }
        }
    }
    
    public function compiled() {
        if (!$this->where->compiled() || !$this->having->compiled()) {
            return false;
        }
        
        return true;
    }
  
    public function arguments() {
        if (!$this->compiled()) {
            return null;
        }
        $args = $this->where->arguments() + $this->having->arguments();
        foreach ($this->tables as $table) {
            if ($table['arguments']) {
                $args += $table['arguments'];
            }
            if (is_object($table['table'])) {
                $args += $table['table']->arguments();
            }
        }
        foreach ($this->expressions as $expression) {
            if ($expression['arguments']) {
                $args += $expression['arguments'];
            }
        }
        
        return $args;
    }
    
    public function __toString() {
        if (!$this->compiled()) {
            $this->compile($this->connection, $this);
        }
        // SELECT
        $query = 'SELECT ';
        if ($this->distinct) {
            $query .= 'DISTINCT ';
        }
        // FIELDS and EXPRESSIONS
        $fields = array();
        foreach ($this->tables as $alias => $table) {
            if (!empty($table['all_fields'])) {
                $fields[] = $alias . '.*';
            }
        }
        foreach ($this->fields as $alias => $field) {
            $fields[] = (isset($field['table']) ? $field['table'] . '.' : '') . $field['field'] . ' AS ' . $field['alias'];
        }
        foreach ($this->expressions as $alias => $expression) {
            $fields[] = $expression['expression'] . ' AS ' . $expression['alias'];
        }
        $query .= implode(', ', $fields);
        // FROM
        $query .= "\nFROM ";
        foreach ($this->tables as $alias => $table) {
            $query .= "\n";
            if (isset($table['join type'])) {
                $query .= $table['join type'] . ' JOIN ';
            }
            if (is_object($table['table'])) {
                $subquery = $table['table'];
                $subquery->preExecute();
                $table_string = '(' . (string) $subquery . ')';
            } else {
                $table_string = '{' . $table['table'] . '}';
            }
            $query .= $table_string . ' ' . $table['alias'];
            if (!empty($table['condition'])) {
                $query .= ' ON ' . $table['condition'];
            }
        }
        // WHERE
        if (count($this->where)) {
            $query .= "\nWHERE " . $this->where;
        }
        // GROUP BY
        if ($this->group) {
            $query .= "\nGROUP BY " . implode(', ', $this->group);
        }
        // HAVING
        if (count($this->having)) {
            $query .= "\nHAVING " . $this->having;
        }
        // ORDER BY
        if ($this->order) {
            $query .= "\nORDER BY ";
            $fields = array();
            foreach ($this->order as $field => $direction) {
                $fields[] = $field . ' ' . $direction;
            }
            $query .= implode(', ', $fields);
        }        
        // RANGE
        if (!empty($this->range)) {
            $query .= "\nLIMIT " . (int) $this->range['length'] . " OFFSET " . (int) $this->range['start'];
        }

        if ($this->forUpdate) {
            $query .= ' FOR UPDATE';
        }

        return $query;
    }
  
    public function __clone() {
        $this->where  = clone($this->where);
        $this->having = clone($this->having);
    }
    
}
