<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->setBasePath('/ranking-api/public');

$routes = require __DIR__ . '/../src/Routes.php';
$routes($app);

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    $error = [
        "error" => "Nenhum retorno disponível para esta rota.",
        "requested_route" => $request->getUri()->getPath()
    ];
    
    $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});


$app->run();
