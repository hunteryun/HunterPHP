<?php

namespace Hunter\Core\Database\sqlite;

use Hunter\Core\Database\Connection as DatabaseConnection;

/**
 * PDO抽象层
 *
 * @see http://php.net/manual/book.pdo.php
 */
class Connection extends DatabaseConnection {
  /**
   * Schema
   *
   * @return Hunter\Core\Database\Schema
   */
  public function schema(array $options = array()) {
      $options = $this->options + $options;
      return new Schema($this, $options);
  }
}
