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

        $jwt = str_replace('Bearer ', '', filter_var($headers[0], FILTER_SANITIZE_STRING));

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'chave_secreta_super_segura';
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));

            $sanitizedParams = $this->sanitizeInputs($request->getParsedBody());
            $request = $request->withParsedBody($sanitizedParams);

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

    private function sanitizeInputs(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInputs($value);
            } else {
                $sanitized[$key] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }
        return $sanitized;
    }
}
