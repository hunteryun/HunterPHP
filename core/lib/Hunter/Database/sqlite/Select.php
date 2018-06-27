<?php

namespace Hunter\Core\Database\sqlite;

use Hunter\Core\Database\Select as DatabaseSelect;

/**
 * SQLite implementation of \Hunter\Core\Database\Query\Select.
 */
class Select extends DatabaseSelect {

  public function forUpdate($set = TRUE) {
    // SQLite does not support FOR UPDATE so nothing to do.
    return $this;
  }

}
