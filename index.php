<?php

define('HUNTER_ROOT', __DIR__);

require_once HUNTER_ROOT . '/core/Hunter.php';

$app = new Hunter\Core\App\Application();

$app->run();
