<?php
require_once __DIR__ . '/config/database.php';

echo "Iniciando criação das tabelas Fugulin no servidor remoto...\n";

try {
    $sql = file_get_contents(__DIR__ . '/fugulin_tables.sql');
    
    // O PDO não executa múltiplos statements de uma vez por padrão em algumas configurações, 
    // então vamos dividir por ponto e vírgula e ignorar linhas vazias.
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }
    
    echo "Sucesso! Tabelas e dados Fugulin inseridos.\n";
} catch (PDOException $e) {
    echo "ERRO ao criar tabelas: " . $e->getMessage() . "\n";
}
?>
