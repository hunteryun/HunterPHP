<?php

require __DIR__ . '/core/Hunter.php';

$response = $router->dispatch(
    $container->get('Zend\Diactoros\ServerRequest'),
    $container->get('Zend\Diactoros\Response')
);

$container->get('Zend\Diactoros\Response\SapiEmitter')->emit($response);
