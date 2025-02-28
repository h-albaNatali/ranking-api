<?php
use Slim\App;
use App\Controllers\RankingController;

return function (App $app) {
    $app->get('/ranking/{movement_id}', [RankingController::class, 'getRanking']);

    $app->get('/test', function ($request, $response) {
        $response->getBody()->write(json_encode(["message" => "Rota funcionando!"]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
