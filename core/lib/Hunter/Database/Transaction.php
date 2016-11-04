<?php

/**
 * @file
 *
 * Transaction
 */

namespace Hunter\Core\Database;

class Transaction {
    
    protected $connection;
    
    protected $rolledBack = false;
    
    protected $name;
    
    public function __construct(Connection $connection, $name = null) {
        $this->connection = $connection;
        if (!$depth = $connection->transactionDepth()) {
            $this->name = 'default_transaction';
        } elseif (!$name) {
            $this->name = 'savepoint_' . $depth;
        } else {
            $this->name = $name;
        }
        $this->connection->pushTransaction($this->name);
    }
    
    public function __destruct() {
        if (!$this->rolledBack) {
            $this->connection->popTransaction($this->name);
        }
    }
    
    public function name() {
        return $this->name;
    }
    
    public function rollback() {
        $this->rolledBack = true;
        $this->connection->rollback($this->name);
    }

}
