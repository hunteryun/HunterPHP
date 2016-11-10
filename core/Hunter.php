<?php

require __DIR__ . '/../vendor/autoload.php';

// ENV loading
josegonzalez\Dotenv\Loader::load([
    'filepath' => __DIR__ . '/../.env',
    'toEnv'    => true
]);

$container = include __DIR__ . '/app/container.php';

$router = include __DIR__ . '/app/router.php';
