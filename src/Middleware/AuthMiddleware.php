<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public function __invoke(Request $request, Handler $handler): ResponseInterface {
        $headers = $request->getHeader('Authorization');

        if (!$headers || empty($headers[0])) {
            return $this->unauthorizedResponse($request, "Token JWT ausente.");
        }

        // Obtém o token sem a palavra "Bearer"
        $jwt = str_replace('Bearer ', '', $headers[0]);

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'chave_secreta_super_segura';
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));

            // Se o token for válido, continua para a próxima requisição
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($request, "Token inválido: " . $e->getMessage());
        }
    }

    private function unauthorizedResponse(Request $request, string $message): ResponseInterface {
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode(["error" => $message]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
