<?php

/**
 * @file
 *
 * Insert
 */

namespace Hunter\Core\Database;

use InvalidArgumentException;

class Insert extends Query {
    
    protected $table;
    
    protected $insertFields = array();
    
    protected $defaultFields = array();
    
    protected $insertValues = array();
    
    protected $fromQuery;
    
    public function __construct($connection, $table, array $options = array()) {
        if (!isset($options['return'])) {
            $options['return'] = Database::RETURN_INSERT_ID;
        }
        parent::__construct($connection, $options);
        $this->table = $table;
    }
    
    public function fields(array $fields, array $values = array()) {
        if (empty($this->insertFields)) {
            if (empty($values)) {
                if (!is_numeric(key($fields))) {
                    $values = array_values($fields);
                    $fields = array_keys($fields);
                }
            }
            $this->insertFields = $fields;
            if (!empty($values)) {
                $this->insertValues[] = $values;
            }
        }

        return $this;
    }
    
    public function values(array $values) {
        if (is_numeric(key($values))) {
            $this->insertValues[] = $values;
        } else {
            foreach ($this->insertFields as $key) {
                $insert_values[$key] = $values[$key];
            }
            $this->insertValues[] = array_values($insert_values);
        }
        return $this;
    }
    
    public function useDefaults(array $fields) {
        $this->defaultFields = $fields;
        return $this;
    }
    
    public function from($query) {
        $this->fromQuery = $query;
        return $this;
    }
    
    public function preExecute() {
        if (array_intersect($this->insertFields, $this->defaultFields)) {
            throw new InvalidArgumentException('You may not specify the same field to have a value and a schema-default value.');
        }
        if (!empty($this->fromQuery)) {
            $this->fields(array_merge(array_keys($this->fromQuery->getFields()), array_keys($this->fromQuery->getExpressions())));
        }
        if (count($this->insertFields) + count($this->defaultFields) == 0) {
            throw new InvalidArgumentException('There are no fields available to insert with.');
        }

        if (!isset($this->insertValues[0]) && count($this->insertFields) > 0 && empty($this->fromQuery)) {
            return false;
        }
        
        return true;
    }
    
    public function execute() {
        if (!$this->preExecute()) {
            return null;
        }
        if (empty($this->fromQuery)) {
            $max_placeholder = 0;
            $values = array();
            foreach ($this->insertValues as $insert_values) {
                foreach ($insert_values as $value) {
                    $values[':db_insert_placeholder_' . $max_placeholder++] = $value;
                }
            }
        } else {
            $values = $this->fromQuery->getArguments();
        }

        $last_insert_id = $this->connection->query((string) $this, $values, $this->queryOptions);

        $this->insertValues = array();

        return $last_insert_id;
    }
    
    public function __toString() {
        $insert_fields = array_merge($this->defaultFields, $this->insertFields);
        if (!empty($this->fromQuery)) {
            return 'INSERT INTO {' . $this->table . '} (' . implode(', ', $insert_fields) . ') ' . $this->fromQuery;
        }

        $query = 'INSERT INTO {' . $this->table . '} (' . implode(', ', $insert_fields) . ') VALUES ';
        $max_placeholder = 0;
        $values = array();
        if (count($this->insertValues)) {
            foreach ($this->insertValues as $insert_values) {
                $placeholders = array();
                $placeholders = array_pad($placeholders, count($this->defaultFields), 'default');
                $new_placeholder = $max_placeholder + count($insert_values);
                for ($i = $max_placeholder; $i < $new_placeholder; ++$i) {
                    $placeholders[] = ':db_insert_placeholder_' . $i;
                }
                $max_placeholder = $new_placeholder;
                $values[] = '(' . implode(', ', $placeholders) . ')';
            }
        }
        else {
            $placeholders = array_fill(0, count($this->defaultFields), 'default');
            $values[] = '(' . implode(', ', $placeholders) . ')';
        }

        $query .= implode(', ', $values);

        return $query;
    }
    
}
