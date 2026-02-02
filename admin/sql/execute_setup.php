<?php
require_once __DIR__ . '/../../config/database.php';

// Proteção básica: Apenas administradores logados (se não for CLI)
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'Administrador') {
        die("Acesso negado.");
    }
}

$sqlFile = __DIR__ . '/setup_carrinhos.sql';

if (!file_exists($sqlFile)) {
    die("Arquivo SQL não encontrado.");
}

$sql = file_get_contents($sqlFile);

try {
    // Executa as queries uma por uma (caso haja múltiplas instruções)
    // Para simplificar, vamos usar o exec que lida com blocos de SQL
    $pdo->exec($sql);
    echo "Sucesso: Tabelas do Carrinho de Parada criadas e módulo registrado.";
} catch (PDOException $e) {
    echo "Erro ao executar SQL: " . $e->getMessage();
}
