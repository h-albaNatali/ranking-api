<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!";
} catch (Exception $e) {
    echo "❌ Erro ao conectar ao banco de dados: " . $e->getMessage();
}
