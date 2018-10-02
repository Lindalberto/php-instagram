<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', app\Controller\App::class . ':index');

$app->get('/require', app\Controller\App::class . ':request');
$app->get('/response', app\Controller\App::class . ':response');
$app->get('/teste', app\Controller\App::class . ':teste');