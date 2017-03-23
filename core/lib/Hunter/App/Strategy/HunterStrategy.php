<?php

namespace Hunter\Core\App\Strategy;

use \Exception;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Middleware\ExecutionChain;
use League\Route\Route;
use League\Route\Strategy\StrategyInterface;
use League\Route\Strategy\ApplicationStrategy;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HunterStrategy extends ApplicationStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCallable(Route $route, array $vars)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($route, $vars) {
            $permissions = $route->getContainer()->get('routePermission');
            $routeTitles = $route->getContainer()->get('routeTitles');
            $path = $route->getPath();
            $callback_permissions = FALSE;

            if(isset($routeTitles[$path])){
              theme()->getEnvironment()->addGlobal('page_title', $routeTitles[$path]);
            }

            if(isset($permissions[$path])){
              $perm_name = 'hunter_permission_'.str_replace(" ", "_", $permissions[$path]);
              $callback = $route->getContainer()->get($perm_name);
              $permission_controller = $route->getCallable($callback['_callback']);
              $callback_permissions = $route->getContainer()->call($permission_controller, $vars);

              if (method_exists($route->getContainer(), 'call')) {
                  if($callback_permissions === TRUE){
                      $body = $route->getContainer()->call($route->getCallable(), $vars);

                      if ($response->getBody()->isWritable()) {
                          $response->getBody()->write($body);
                      }
                      return $response;
                  }else {
                      $response->getBody()->write('Sorry, you do not have permission to access this page!');
                      return $response;
                  }
              }
            }else {
                $body = $route->getContainer()->call($route->getCallable(), $vars);

                if ($response->getBody()->isWritable()) {
                    $response->getBody()->write($body);
                }
                return $response;
            }

            throw new RuntimeException(
                sprintf(
                    'To use the parameter strategy, the container must implement the (::call) method. (%s) does not.',
                    get_class($route->getContainer())
                )
            );

            return $next($request, $response);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getNotFoundDecorator(NotFoundException $exception)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            $response->getBody()->write('Sorry, this page '.$exception->getMessage());
            return $response;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            $response->getBody()->write('Sorry, this page '.$exception->getMessage());
            return $response;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptionDecorator(Exception $exception)
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($exception) {
            $response->getBody()->write('Sorry, this page '.$exception->getMessage());
            return $response;
        };
    }

}
