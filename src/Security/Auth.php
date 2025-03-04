<?php
namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $secretKey = "SEU_SECRET_AQUI";

    public static function verifyToken($request) {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) return false;

        try {
            return JWT::decode($token, new Key(self::$secretKey, 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
