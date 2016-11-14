<?php

/**
 * @file
 */

namespace Hunter\test\Controller;

use League\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Hunter\Core\App\Application;
use Symfony\Component\HttpFoundation\Request;

class TestController {

  /**
   * homepage.
   */
  public function index(Request $request) {
    echo 'Hello HunterPHP';
  }

  /**
   * Displays a list of materias.
   */
  public function overview() {
    echo 'overview';
  }

}
