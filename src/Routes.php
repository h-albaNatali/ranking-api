<?php

use Slim\Factory\AppFactory;
use App\Controllers\AuthController;
use App\Controllers\RankingController;

$app = AppFactory::create();

// Middleware para parsear JSON
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Rota de autenticação
$app->post('/register', [AuthController::class, 'register']);
$app->post('/login', [AuthController::class, 'login']);

// Rotas de ranking (autenticadas)
$app->group('', function ($group) {
    $group->get('/ranking/{movement_id}', [RankingController::class, 'getRanking']);
    $group->post('/personal_record', [RankingController::class, 'addPersonalRecord']);
})->add(new \App\Middleware\AuthMiddleware());

// Run app
$app->run();