<?php

/**
 * @file
 *
 * Select
 */

namespace Hunter\Core\Database\SQLite;

use Hunter\Core\Database\Select as QuerySelect;

class Select extends QuerySelect {

    public function forUpdate($set = true) {
        return $this;
    }

}
