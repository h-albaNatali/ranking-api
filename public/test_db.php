<?php
require_once '../src/Database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "Conexão com o banco de dados bem-sucedida!";
} else {
    echo "Falha na conexão com o banco de dados.";
}
