<?php

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\RouteCollection;
use League\Route\Strategy\ParamStrategy;
use Hunter\Core\App\Application;
use Hunter\Core\App\ModuleHandler;
use Hunter\Core\Discovery\YamlDiscovery;

$container = new Container();

$container->addServiceProvider(Hunter\ServiceProvider\ConfigServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\HttpMessageServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\TemplateServiceProvider::class);

$container->inflector(Hunter\Contract\ConfigAwareInterface::class)
          ->invokeMethod('setConfig', ['config']);
$container->inflector(Hunter\Contract\TemplateAwareInterface::class)
          ->invokeMethod('setTemplateDriver', ['Twig_Environment']);

$container->delegate(new League\Container\ReflectionContainer);

$router = new RouteCollection($container);
$router->setStrategy(new ParamStrategy());

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
