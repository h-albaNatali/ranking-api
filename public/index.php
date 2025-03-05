<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Database\Database;
use App\Controllers\AuthController;
use App\Controllers\RankingController;
use App\Middleware\Middleware;
use App\Middleware\AuthMiddleware; // Adicione esta linha no topo do index.php

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Cria a conexão com o banco de dados usando sua classe Database
$db = Database::getInstance()->getConnection();

// Cria o aplicativo Slim
$app = AppFactory::create();


// Adiciona o middleware para fazer o parse do corpo da requisição (JSON, form data, etc.)
$app->addBodyParsingMiddleware();


// Registra as rotas utilizando closures para injetar a dependência no AuthController
$app->post('/register', function ($request, $response, $args) use ($db) {
    $controller = new AuthController($db);
    return $controller->register($request, $response, $args);
});

$app->post('/login', function ($request, $response, $args) use ($db) {
    $controller = new AuthController($db);
    return $controller->login($request, $response, $args);
});

// Para o RankingController

$app->group('', function ($app) {
    $app->get('/ranking/{movement_id}', \App\Controllers\RankingController::class . ':getRanking');
})->add(new AuthMiddleware()); // 🔥 Adiciona autenticação


// Rota fallback para requisições não definidas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $error = [
        "error" => "Nenhum retorno disponível para esta rota.",
        "requested_route" => $request->getUri()->getPath()
    ];
    $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();
