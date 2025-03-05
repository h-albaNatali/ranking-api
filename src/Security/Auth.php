<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $secret = "chave_secreta_super_segura";

    public static function generateToken($user_id) {
        $payload = [
            'iss' => "localhost",
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $user_id
        ];

        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function validateToken($token) {
        try {
            return JWT::decode($token, new Key(self::$secret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
