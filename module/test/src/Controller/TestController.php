<?php

namespace Hunter\test\Controller;

use League\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Hunter\Core\App\Application;

class TestController {

  /**
   * homepage.
   */
  public function index(Application $app) {
    return 'Hello HunterPHP';
  }

  /**
   * Displays a list of materias.
   */
  public function overview() {
    return 'overview';
  }

}
