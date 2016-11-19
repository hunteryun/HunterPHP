<?php

namespace Hunter\Core\Category;

/**
 * Provides an interface defining a taxonomy term entity.
 */
interface CategoryInterface {

  /**
   * Gets the term's description.
   *
   * @return string
   *   The term description.
   */
  public function getDescription();

  /**
   * Sets the term's description.
   *
   * @param string $description
   *   The term's description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the name of the term.
   *
   * @return string
   *   The name of the term.
   */
  public function getCat();

  /**
   * Sets the name of the term.
   *
   * @param int $name
   *   The term's name.
   *
   * @return $this
   */
  public function addCat($name);

  /**
   * Gets the weight of this term.
   *
   * @return int
   *   The weight of the term.
   */
  public function getWeight();

  /**
   * Gets the weight of this term.
   *
   * @param int $weight
   *   The term's weight.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Get the taxonomy vocabulary id this term belongs to.
   *
   * @return int
   *   The id of the vocabulary.
   */
  public function getVocabularyId();

}
