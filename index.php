<?php

use HunterPHP\Core\HunterKernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/core/Hunter.php';

$request = $container->get('Symfony\Component\HttpKernel\HttpKernel');

print_r($request);die;
echo 'Hello 煮公';
