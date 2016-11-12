<?php

/**
 * @file
 */

namespace Hunter\test\Controller;

use League\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController {

  /**
   * Displays a list of materias.
   */
  public function overview() {
    echo 'overview';
  }

}
