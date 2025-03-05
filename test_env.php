<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;


$dotenvPath = __DIR__;
if (file_exists($dotenvPath . '/.env')) {
    echo "✅ O arquivo .env foi encontrado!<br>";
    $dotenv = Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
} else {
    die("❌ Erro: Arquivo .env não encontrado!");
}

