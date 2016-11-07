<?php

use League\Container\Container;

$container = new Container();

$container->addServiceProvider(Hunter\ServiceProvider\HttpMessageServiceProvider::class);

return $container;
