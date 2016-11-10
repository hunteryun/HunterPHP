<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;

$router = new RouteCollection(
    isset($container) && $container instanceof ContainerInterface ? $container : new Container
);

$router->get('/', 'Hunter\Controller\MainController::index');
$router->get('/hello', 'Hunter\Controller\MainController::testRoute');

return $router;
