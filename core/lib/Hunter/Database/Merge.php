<?php

/**
 * @file
 *
 * Merge
 */

namespace Hunter\Core\Database;

use InvalidArgumentException;
use RuntimeException;
use Exception;

class Merge extends Query {
    
    const STATUS_INSERT = 1;
    
    const STATUS_UPDATE = 2;
    
    protected $table;
    
    protected $conditionTable;
    
    protected $insertFields = array();
    
    protected $defaultFields = array();
    
    protected $insertValues = array();
    
    protected $updateFields = array();
    
    protected $expressionFields = array();
    
    protected $needsUpdate = false;
    
    
    public function __construct(Connection $connection, $table, array $options = array()) {
        $options['return'] = Database::RETURN_AFFECTED;
        parent::__construct($connection, $options);
        $this->table = $table;
        $this->conditionTable = $table;
        $this->condition = new Condition('AND');
    }
    
    protected function conditionTable($table) {
        $this->conditionTable = $table;
        return $this;
    }
    
    public function updateFields(array $fields) {
        $this->updateFields = $fields;
        $this->needsUpdate = true;
        
        return $this;
    }
    
    public function expression($field, $expression, array $arguments = null) {
        $this->expressionFields[$field] = array(
            'expression' => $expression,
            'arguments' => $arguments,
        );
        $this->needsUpdate = true;
        
        return $this;
    }
    
    public function insertFields(array $fields, array $values = array()) {
        if ($values) {
            $fields = array_combine($fields, $values);
        }
        $this->insertFields = $fields;
       
        return $this;
    }
    
    public function useDefaults(array $fields) {
        $this->defaultFields = $fields;
        
        return $this;
    }
  
    public function fields(array $fields, array $values = array()) {
        if ($values) {
            $fields = array_combine($fields, $values);
        }
        foreach ($fields as $key => $value) {
            $this->insertFields[$key] = $value;
            $this->updateFields[$key] = $value;
        }
        $this->needsUpdate = true;
        
        return $this;
    }
    
    public function key(array $fields, array $values = array()) {
        if ($values) {
            $fields = array_combine($fields, $values);
        }
        foreach ($fields as $key => $value) {
            $this->insertFields[$key] = $value;
            $this->condition($key, $value);
        }
        
        return $this;
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
    
    public function __toString() {}
  
    public function execute() {
        $this->queryOptions += array(
            'throw_exception' => true,
        );

        try {
            if (!count($this->condition)) {
                throw new InvalidArgumentException('Invalid merge query: no conditions');
            }
            $select = $this->connection->select($this->conditionTable)
                                       ->condition($this->condition);
            $select->addExpression('1');
            if (!$select->execute()->fetchField()) {
                try {
                    $insert = $this->connection->insert($this->table)->fields($this->insertFields);
                    if ($this->defaultFields) {
                        $insert->useDefaults($this->defaultFields);
                    }
                    $insert->execute();
                    return self::STATUS_INSERT;
                }
                catch (RuntimeException $e) {
                    if (!$select->execute()->fetchField()) {
                        throw $e;
                    }
                }
            }
            if ($this->needsUpdate) {
                $update = $this->connection->update($this->table)
                                           ->fields($this->updateFields)
                                           ->condition($this->condition);
                if ($this->expressionFields) {
                    foreach ($this->expressionFields as $field => $data) {
                        $update->expression($field, $data['expression'], $data['arguments']);
                    }
                }
                $update->execute();
                return self::STATUS_UPDATE;
            }
        }
        catch (Exception $e) {
            if ($this->queryOptions['throw_exception']) {
                throw $e;
            } else {
                return null;
            }
        }
    }
  
}
