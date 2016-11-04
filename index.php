<?php

use HunterPHP\Core\HunterKernel;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/core/Hunter.php';

print_r($container->get('config.db.name'));

echo 'Hello 煮公';
