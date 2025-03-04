<?php
namespace App\Controllers;

use App\Database\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private static $secretKey = "SEU_SECRET_AQUI";

    public function register(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) {
            return $this->jsonResponse($response, ["error" => "Preencha todos os campos."], 400);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("INSERT INTO user (name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
            $stmt->execute();

            return $this->jsonResponse($response, ["message" => "Usuário cadastrado com sucesso."]);
        } catch (\PDOException $e) {
            return $this->jsonResponse($response, ["error" => "Erro ao cadastrar usuário."], 500);
        }
    }

    public function login(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->jsonResponse($response, ["error" => "E-mail e senha são obrigatórios."], 400);
        }

        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT id, password FROM user WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->jsonResponse($response, ["error" => "Usuário ou senha inválidos."], 401);
        }

        $token = JWT::encode([
            "iat" => time(),
            "exp" => time() + (60 * 60), // Expira em 1 hora
            "userId" => $user['id']
        ], self::$secretKey, 'HS256');

        return $this->jsonResponse($response, ["token" => $token]);
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
