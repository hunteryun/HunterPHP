<?php

namespace Hunter\test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides admin module permission auth.
 */
class TestPermission {

  /**
   * Returns bool value of admin permission.
   *
   * @return bool
   */
  public function handle(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    //code...if ture
    return $next($request, $response);
  }

}
