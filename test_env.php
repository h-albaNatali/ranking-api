<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Testa se o arquivo .env existe
$dotenvPath = __DIR__;
if (file_exists($dotenvPath . '/.env')) {
    echo "✅ O arquivo .env foi encontrado!<br>";
    $dotenv = Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
} else {
    die("❌ Erro: Arquivo .env não encontrado!");
}

// Exibe as variáveis carregadas
echo "<pre>";
var_dump(getenv('DB_DATABASE'));
var_dump($_ENV['DB_DATABASE'] ?? 'Não definido');
echo "</pre>";
