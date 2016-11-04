<?php

use League\Container\Container;
use TomPHP\ContainerConfigurator\Configurator;

$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
];

$container = new Container();
Configurator::apply()->configFromArray($config)->to($container);
