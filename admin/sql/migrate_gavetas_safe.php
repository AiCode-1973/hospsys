<?php
require_once __DIR__ . '/../../config/database.php';

function addColumn($pdo, $table, $column, $after) {
    try {
        $pdo->query("ALTER TABLE $table ADD COLUMN $column TINYINT DEFAULT 1 AFTER $after");
        echo "Coluna '$column' adicionada à tabela '$table'.\n";
    } catch (PDOException $e) {
        echo "Aviso na tabela '$table': " . $e->getMessage() . "\n";
    }
}

addColumn($pdo, 'car_composicao_ideal', 'gaveta', 'id_item');
addColumn($pdo, 'car_estoque_atual', 'gaveta', 'id_item');
addColumn($pdo, 'car_checklist_itens', 'gaveta', 'id_item');

echo "Processo concluído.";
