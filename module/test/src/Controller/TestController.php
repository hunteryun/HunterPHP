<?php

namespace Hunter\test\Controller;

use Zend\Diactoros\ServerRequest;
use Hunter\test\Plugin\TestPlugin;

class TestController {

  /**
   * homepage.
   */
  public function index(ServerRequest $request, TestPlugin $test) {
    return view('/hunter/index.html', array('title' => 'Hello Hunter!'));
  }

  /**
   * Displays a list of materias.
   */
  public function overview() {
    return 'overview';
  }

}
