<?php

namespace Hunter\Core\Category;

class Category implements CategoryInterface{

  /**
   * Gets the name of the term.
   *
   * @return string
   *   The name of the term.
   */
  public function getCat($tid) {
      $cat = db_query("select * from {category} where tid=:tid", array(':tid'=>$tid))->fetchObject();
      if ($cat) {
          return $cat;
      }
      return false;
  }

  /**
   * Sets the name of the term.
   *
   * @param int $name
   *   The term's name.
   *
   * @return $this
   */
  public function addCat($cat) {
      $cid = db_insert('category')
              ->fields(array(
                'ctypeid' => 0,
                'name' => $cat['name'],
                'description' => $cat['description'],
              ))
              ->execute();
      if ($cid) {
          return $cid;
      }
      return false;
  }

}
