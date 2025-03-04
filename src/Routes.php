<?php

use Slim\App;
use App\Controllers\RankingController;
use App\Controllers\AuthController;

return function (App $app) {
    $app->post('/register', [AuthController::class, 'register']); // Rota para criar usuário
    $app->post('/login', [AuthController::class, 'login']); // Rota para login
    $app->get('/ranking/{movement_id}', [RankingController::class, 'getRanking']); // Ranking protegido
};
