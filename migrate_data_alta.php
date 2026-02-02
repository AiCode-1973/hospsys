<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("ALTER TABLE fugulin_pacientes ADD COLUMN data_alta DATETIME AFTER ativo");
    
    // Opcional: Popular data_alta para pacientes que já tiveram alta (usando a data da última classificação)
    $pdo->exec("
        UPDATE fugulin_pacientes p
        SET p.data_alta = (
            SELECT MAX(data_registro) 
            FROM fugulin_classificacoes 
            WHERE id_paciente = p.id
        )
        WHERE p.ativo = 0 AND p.data_alta IS NULL
    ");
    
    echo "Sucesso: Coluna 'data_alta' adicionada e populada para registros existentes.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Aviso: A coluna 'data_alta' já existe.";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
