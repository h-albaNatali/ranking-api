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
            return $this->unauthorizedResponse("Token JWT ausente.");
        }

        $jwt = str_replace('Bearer ', '', $headers[0]);
        

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'chave_secreta_super_segura';
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));

            $parsedBody = $request->getParsedBody();

            if (!is_array($parsedBody)) {
                $parsedBody = [];
            }

            $sanitizedBody = $this->sanitizeInputs($parsedBody);

            $request = $request->withParsedBody($sanitizedBody);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse("Token inválido: " . $e->getMessage());
        }
    }

    private function sanitizeInputs(array $data): array {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInputs($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function unauthorizedResponse(string $message): ResponseInterface {
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode(["error" => $message]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
