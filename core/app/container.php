<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use Hunter\Core\App\Application;
use Hunter\Core\App\ModuleHandler;
use Hunter\Core\Discovery\YamlDiscovery;
use League\Route\RouteCollection;

$container = new Container();

$container->addServiceProvider(Hunter\ServiceProvider\ConfigServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\HttpMessageServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\TemplateServiceProvider::class);

$container->inflector(Hunter\Contract\ConfigAwareInterface::class)
          ->invokeMethod('setConfig', ['config']);
$container->inflector(Hunter\Contract\TemplateAwareInterface::class)
          ->invokeMethod('setTemplateDriver', ['Twig_Environment']);

$container->add(Hunter\Controller\MainController::class);
$container->add(Hunter\test\Controller\TestController::class);


$router = new RouteCollection($container);

$application = new Application();
$application->updateModules($application->getModulesList());
$moduleHandler = new ModuleHandler($application::guessApplicationRoot(), $application->getModulesList());
$moduleHandler->loadAll();

$discovery = new YamlDiscovery('routing', $moduleHandler->getModuleDirectories());

foreach ($discovery->findAll() as $routes) {
  foreach ($routes as $name => $route_info) {
    $router->get($route_info['path'], $route_info['defaults']['_controller']);
  }
}
