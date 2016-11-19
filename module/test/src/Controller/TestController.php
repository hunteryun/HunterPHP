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
    session()->set("user", "Drupal Hunter");
    return view('/hunter/index.html', array('title' => 'Hello HunterPHP!', 'session_value' => session()->get("user")));
  }

  /**
   * Displays a list of materias.
   */
  public function overview() {
    return 'overview';
  }

}
