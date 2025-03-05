<?php

namespace App\Controllers;

use App\Database\Database;
use App\Security\Auth;
use Psr\Http\Message\ResponseInterface as Response;
use PDO;
use Exception;

class AuthController {
    public function register($request, $response) {
        $data = $request->getParsedBody();

    
        if (!isset($data['name'], $data['email'], $data['password']) || 
            empty(trim($data['name'])) || empty(trim($data['email'])) || empty(trim($data['password']))) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write(json_encode(["error" => "Todos os campos são obrigatórios."]));
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write(json_encode(["error" => "Email inválido."]));
        }

        if (strlen($data['password']) < 6) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write(json_encode(["error" => "A senha deve ter pelo menos 6 caracteres."]));
        }

        $db = Database::getInstance()->getConnection();

    
        $stmt = $db->prepare("SELECT id FROM user_api WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);

        if ($stmt->fetch()) {
            $response->getBody()->write(json_encode(["error" => "Email já cadastrado."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        

        try {
            $stmt = $db->prepare("INSERT INTO user_api (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                'name' => htmlspecialchars(strip_tags($data['name'])),
                'email' => htmlspecialchars(strip_tags($data['email'])),
                'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            ]);

            $response->getBody()->write(json_encode(['message' => 'Usuário registrado com sucesso.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500)->write(json_encode(["error" => "Erro interno no servidor."]));
        }
    }

    public function login($request, $response) {
        $data = $request->getParsedBody();

        if (!isset($data['email'], $data['password']) || empty(trim($data['email'])) || empty(trim($data['password']))) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write(json_encode(["error" => "Email e senha são obrigatórios."]));
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id, password FROM user_api WHERE email = :email");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401)->write(json_encode(["error" => "Credenciais inválidas."]));
        }

        $token = Auth::generateToken($user['id']);
        $response->getBody()->write(json_encode(["token" => $token]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
