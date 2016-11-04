<?php

/**
 * @file
 *
 * Select
 */

namespace HunterPHP\Core\Database\SQLite;

use HunterPHP\Core\Database\Select as QuerySelect;

class Select extends QuerySelect {

    public function forUpdate($set = true) {
        return $this;
    }

}
