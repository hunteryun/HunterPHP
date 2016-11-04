<?php

/**
 * @file
 *
 * Update
 */

namespace Hunter\Core\Database;

class Update extends Query {
    
    protected $table;
    
    protected $fields = array();
    
    protected $arguments = array();
    
    protected $condition;
    
    protected $expressionFields = array();
    
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
    
    public function fields(array $fields) {
        $this->fields = $fields;
        return $this;
    }
  
    public function expression($field, $expression, array $arguments = null) {
        $this->expressionFields[$field] = array(
            'expression' => $expression,
            'arguments'  => $arguments,
        );

        return $this;
    }
  
    public function execute() {
        $fields = $this->fields;
        $update_values = array();
        foreach ($this->expressionFields as $field => $data) {
            if (!empty($data['arguments'])) {
                $update_values += $data['arguments'];
            }
            if (is_object($data['expression'])) {
                $data['expression']->compile($this->connection, $this);
                $update_values += $data['expression']->arguments();
            }
            unset($fields[$field]);
        }

        $max_placeholder = 0;
        foreach ($fields as $field => $value) {
            $update_values[':db_update_placeholder_' . ($max_placeholder++)] = $value;
        }

        if (count($this->condition)) {
            $this->condition->compile($this->connection, $this);
            $update_values = array_merge($update_values, $this->condition->arguments());
        }

        return $this->connection->query((string) $this, $update_values, $this->queryOptions);
    }
    
    public function __toString() {
        $fields = $this->fields;
        $update_fields = array();
        foreach ($this->expressionFields as $field => $data) {
            if (is_object($data['expression'])) {
                $data['expression']->compile($this->connection, $this);
                $data['expression'] = ' (' . $data['expression'] . ')';
            }
            $update_fields[] = $field . '=' . $data['expression'];
            unset($fields[$field]);
        }

        $max_placeholder = 0;
        foreach ($fields as $field => $value) {
            $update_fields[] = $field . '=:db_update_placeholder_' . ($max_placeholder++);
        }

        $query = 'UPDATE {' . $this->table . '} SET ' . implode(', ', $update_fields);

        if (count($this->condition)) {
            $this->condition->compile($this->connection, $this);
            $query .= "\nWHERE " . $this->condition;
        }

        return $query;
    }
  
}
