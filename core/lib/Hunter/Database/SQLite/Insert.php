<?php

/**
 * @file
 *
 * Insert
 */

namespace HunterPHP\Core\Database\SQLite;

use HunterPHP\Core\Database\Insert as QueryInsert;

class Insert extends QueryInsert {
    
    public function execute() {
        if (!$this->preExecute()) {
            return null;
        }
        if (count($this->insertFields)) {
            return parent::execute();
        } else {
            return $this->connection->query('INSERT INTO {' . $this->table . '} DEFAULT VALUES', array(), $this->queryOptions);
        }
    }
    
    public function __toString() {
        $placeholders = array_fill(0, count($this->insertFields), '?');
        if (!empty($this->fromQuery)) {
            $insert_fields_string = $this->insertFields ? ' (' . implode(', ', $this->insertFields) . ') ' : ' ';
            return 'INSERT INTO {' . $this->table . '}' . $insert_fields_string . $this->fromQuery;
        }
        return 'INSERT INTO {' . $this->table . '} (' . implode(', ', $this->insertFields) . ') VALUES (' . implode(', ', $placeholders) . ')';
    }

}
