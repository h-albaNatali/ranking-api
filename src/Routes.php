<?php

use Slim\Factory\AppFactory;
use App\Controllers\AuthController;
use App\Controllers\RankingController;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->post('/register', [AuthController::class, 'register']);
$app->post('/login', [AuthController::class, 'login']);

$app->get('/ranking/{movement_id}', \App\Controllers\RankingController::class . ':getRanking')->add(new \App\Middleware\AuthMiddleware());


$app->run();