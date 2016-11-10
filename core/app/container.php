<?php

use League\Container\Container;

$container = new Container();

$container->addServiceProvider(Hunter\ServiceProvider\ConfigServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\HttpMessageServiceProvider::class);
$container->addServiceProvider(Hunter\ServiceProvider\TemplateServiceProvider::class);

$container->inflector(Hunter\Contract\ConfigAwareInterface::class)
          ->invokeMethod('setConfig', ['config']);
$container->inflector(Hunter\Contract\TemplateAwareInterface::class)
          ->invokeMethod('setTemplateDriver', ['Twig_Environment']);

$container->add(Hunter\Controller\MainController::class);

return $container;
