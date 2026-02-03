<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo->beginTransaction();

    // Adiciona gaveta em car_composicao_ideal
    $pdo->query("ALTER TABLE car_composicao_ideal ADD COLUMN gaveta TINYINT DEFAULT 1 AFTER id_item");
    
    // Adiciona gaveta em car_estoque_atual
    $pdo->query("ALTER TABLE car_estoque_atual ADD COLUMN gaveta TINYINT DEFAULT 1 AFTER id_item");
    
    // Adiciona gaveta em car_checklist_itens
    $pdo->query("ALTER TABLE car_checklist_itens ADD COLUMN gaveta TINYINT DEFAULT 1 AFTER id_item");

    $pdo->commit();
    echo "Migração de gavetas concluída com sucesso!";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Erro na migração: " . $e->getMessage();
}
