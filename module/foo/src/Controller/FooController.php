<?php

namespace Hunter\foo\Controller;

use Zend\Diactoros\Response\JsonResponse;

class FooController {

  /**
   * Displays a list of materias.
   */
  public function foo_list() {
    return new JsonResponse(array('status' => true, 'name' => 'HunterPHP'));
  }

}
