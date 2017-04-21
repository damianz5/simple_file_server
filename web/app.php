<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/MicroKernel.php';

$kernel = new MicroKernel('prod', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
