<?php

/**
 * @file
 *
 * Delete
 */

namespace Hunter\Core\Database;

class Delete extends Query {
    
    protected $table;
    
    protected $condition;
    
    public function __construct(Connection $connection, $table, array $options = array()) {
        $options['return'] = Database::RETURN_AFFECTED;
        parent::__construct($connection, $options);
        $this->table = $table;
        $this->condition = new Condition('AND');
    }
    
    public function condition($field, $value = null, $operator = null) {
        $this->condition->condition($field, $value, $operator);
        return $this;
    }
    
    public function isNull($field) {
        $this->condition->isNull($field);
        return $this;
    }
    
    public function isNotNull($field) {
        $this->condition->isNotNull($field);
        return $this;
    }
    
    public function &conditions() {
        return $this->condition->conditions();
    }
    
    public function arguments() {
        return $this->condition->arguments();
    }
    
    public function where($snippet, $args = array()) {
        $this->condition->where($snippet, $args);
        return $this;
    }
    
    public function compile(Connection $connection, $queryPlaceholder) {
        return $this->condition->compile($connection, $queryPlaceholder);
    }
    
    public function compiled() {
        return $this->condition->compiled();
    }
    
    public function execute() {
        $values = array();
        if (count($this->condition)) {
            $this->condition->compile($this->connection, $this);
            $values = $this->condition->arguments();
        }

        return $this->connection->query((string) $this, $values, $this->queryOptions);
    }
    
    public function __toString() {
        $query = 'DELETE FROM {' . $this->table . '} ';
        if (count($this->condition)) {
            $this->condition->compile($this->connection, $this);
            $query .= "\nWHERE " . $this->condition;
        }

        return $query;
    }
  
}
