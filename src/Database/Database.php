<?php
namespace App\Database;

use PDO;
use PDOException;

class Database {
    private $host = "localhost"; 
    private $db_name = "ranking_db"; 
    private $username = "root"; 
    private $password = ""; 
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }

        return $this->conn;
    }
}
