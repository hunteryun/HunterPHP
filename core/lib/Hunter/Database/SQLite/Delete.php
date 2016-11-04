<?php

/**
 * @file
 *
 * Delete
 */

namespace Hunter\Core\Database\SQLite;

use Hunter\Core\Database\Delete as QueryDelete;

class Delete extends QueryDelete {
    
    public function execute() {
        if (!count($this->condition)) {
            $rows = $this->connection->query('SELECT COUNT(*) FROM {' . $this->table . '}')->fetchField();
            parent::execute();
            return $rows;
        } else {
            return parent::execute();
        }
    }

}
