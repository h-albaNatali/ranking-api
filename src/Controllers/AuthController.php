<?php

namespace App\Controllers;

use App\Database\Database;
use App\Security\Auth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Exception;

class AuthController {
    public function register(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (!isset($data['name'], $data['email'], $data['password']) || 
            empty(trim($data['name'])) || empty(trim($data['email'])) || empty(trim($data['password']))) {
            return $this->jsonResponse($response, ["error" => "Todos os campos são obrigatórios."], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse($response, ["error" => "Email inválido."], 400);
        }

        if (strlen($data['password']) < 6) {
            return $this->jsonResponse($response, ["error" => "A senha deve ter pelo menos 6 caracteres."], 400);
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id FROM user_api WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);

        if ($stmt->fetch()) {
            return $this->jsonResponse($response, ["error" => "Email já cadastrado."], 400);
        }

        try {
            $stmt = $db->prepare("INSERT INTO user_api (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                'name' => htmlspecialchars(strip_tags($data['name'])),
                'email' => htmlspecialchars(strip_tags($data['email'])),
                'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            return $this->jsonResponse($response, ['message' => 'Usuário registrado com sucesso.'], 201);
        } catch (Exception $e) {
            return $this->jsonResponse($response, ["error" => "Erro interno no servidor."], 500);
        }
    }

    public function login(Request $request, Response $response) {
        session_start(); 
        $data = $request->getParsedBody();

        if (!isset($data['email'], $data['password']) || empty(trim($data['email'])) || empty(trim($data['password']))) {
            return $this->jsonResponse($response, ["error" => "Email e senha são obrigatórios."], 400);
        }

        $email = trim($data['email']);
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id, password FROM user_api WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            $_SESSION['login_attempts'][$clientIP][$email]++; 
            return $this->jsonResponse($response, ["error" => "Credenciais inválidas."], 401);
        }

        $token = Auth::generateToken($user['id']);
        return $this->jsonResponse($response, ["token" => $token], 200);
    }

    private function jsonResponse(Response $response, array $data, int $statusCode): Response {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}
