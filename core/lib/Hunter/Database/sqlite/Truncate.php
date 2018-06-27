<?php

namespace Hunter\Core\Database\sqlite;

use Hunter\Core\Database\Query;

/**
 * SQLite implementation of \Hunter\Core\Database\Query\Truncate.
 *
 * SQLite doesn't support TRUNCATE, but a DELETE query with no condition has
 * exactly the effect (it is implemented by DROPing the table).
 */
class Truncate extends Query {
  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    return $comments . 'DELETE FROM {' . $this->connection->escapeTable($this->table) . '} ';
  }

}
