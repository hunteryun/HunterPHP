<?php

namespace Hunter\test\Controller;

use Zend\Diactoros\ServerRequest;
use Hunter\test\Plugin\TestPlugin;

class TestController {

  /**
   * homepage.
   */
  public function index(ServerRequest $request, TestPlugin $test) {
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
