<?php

namespace Hunter\Core\Database\sqlite;

use Hunter\Core\Database\Database;
use Hunter\Core\Database\Query;
use Hunter\Core\Database\Condition;

/**
 * SQLite implementation of \Drupal\Core\Database\Query\Select.
 */
class Select extends Query {

  protected $fields = array();

  protected $expressions = array();

  protected $tables = array();

  protected $order = array();

  protected $group = array();

  protected $where;

  protected $having;

  protected $distinct = false;

  protected $range;

  protected $prepared = false;

  protected $forUpdate = false;

  public function __construct($table, $alias = null, Connection $connection, $options = array()) {
      $options['return'] = Database::RETURN_STATEMENT;
      parent::__construct($connection, $options);
      $conjunction  = isset($options['conjunction']) ? $options['conjunction'] : 'AND';
      $this->where  = new Condition($conjunction);
      $this->having = new Condition($conjunction);
      $this->addJoin(null, $table, $alias);
  }

  public function forUpdate($set = TRUE) {
    // SQLite does not support FOR UPDATE so nothing to do.
    return $this;
  }

}
