<?php

namespace Hunter\test\Controller;

use Zend\Diactoros\ServerRequest;
use Hunter\test\Plugin\TestPlugin;

class TestController {

  /**
   * homepage.
   */
  public function index(ServerRequest $request, TestPlugin $test) {
    session()->set("test", "Welcome to use the best PHP framework : HunterPHP!");
    return view('/hunter/index.html', array('title' => $test->bar->sayhello(), 'session_value' => session()->get("test")));
  }

  /**
   * Displays a list of materias.
   */
  public function overview() {
    return 'overview';
  }

}
