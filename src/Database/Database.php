<?php
namespace App\Database;

use PDO;
use PDOException;

class Database {
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s", getenv('DB_HOST'), getenv('DB_NAME'));
            $this->conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Erro na conexão: " . $e->getMessage());
            die("Erro interno no servidor.");
        }

        return $this->conn;
    }
}
