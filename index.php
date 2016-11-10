<?php

require __DIR__ . '/core/Hunter.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Hunter\Core\App\Application();

$app->map('/', function () {
	return new Response('This is the home page');
});

$app->map('/about', function () {
	return new Response('This is the about page');
});

$app->map('/hello/{name}', function ($name) {
	return new Response('Hello '.$name);
});

$app->run();
