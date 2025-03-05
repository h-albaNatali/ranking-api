<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Database\Database;
use App\Controllers\AuthController;
use App\Controllers\RankingController;
use App\Middleware\Middleware;
use App\Middleware\AuthMiddleware; 

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = Database::getInstance()->getConnection();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->post('/register', function ($request, $response, $args) use ($db) {
    $controller = new AuthController($db);
    return $controller->register($request, $response, $args);
});

$app->post('/login', function ($request, $response, $args) use ($db) {
    $controller = new AuthController($db);
    return $controller->login($request, $response, $args);
});

$app->group('', function ($app) {
    $app->get('/ranking/{movement_id}', \App\Controllers\RankingController::class . ':getRanking');
})->add(new AuthMiddleware()); // 🔥 Adiciona autenticação

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $error = [
        "error" => "Nenhum retorno disponível para esta rota.",
        "requested_route" => $request->getUri()->getPath()
    ];
    $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();
