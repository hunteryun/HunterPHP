<?php

namespace Hunter\Core\App\Strategy;

use League\Route\Strategy\AbstractStrategy;
use League\Route\Strategy\StrategyInterface;
use League\Route\Route;
use RuntimeException;

class HunterStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(callable $controller, array $vars, Route $route = null)
    {
        $permissions = $this->getContainer()->get('routePermission');
        $path = $route->getPath();
        $callback_permissions = FALSE;

        if(isset($permissions[$path])){
          $perm_name = 'hunter_permission_'.str_replace(" ", "_", $permissions[$path]);
          $callback = $this->getContainer()->get($perm_name);
          $permission_controller = $this->getCallable($callback['_callback']);
          $callback_permissions = $this->getContainer()->call($permission_controller, $vars);

          if (method_exists($this->getContainer(), 'call')) {
              if($callback_permissions === TRUE){
                  $response = $this->getContainer()->call($controller, $vars);
                  return $this->determineResponse($response);
              }else {
                  $response = $this->getResponse();
                  $response->getBody()->write('Sorry, you do not have permission to access this page!');
                  return $response;
              }
          }
        }else {
            $response = $this->getContainer()->call($controller, $vars);
            return $this->determineResponse($response);
        }

        throw new RuntimeException(
            sprintf(
                'To use the parameter strategy, the container must implement the (::call) method. (%s) does not.',
                get_class($this->getContainer())
            )
        );
    }

    protected function getCallable($callable)
    {
        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
        }

        if (is_array($callable) && isset($callable[0]) && is_object($callable[0])) {
            $callable = [$callable[0], $callable[1]];
        }

        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            $class = ($this->getContainer()->has($callable[0]))
                   ? $this->getContainer()->get($callable[0])
                   : new $callable[0];

            $callable = [$class, $callable[1]];
        }

        return $callable;
    }
}
