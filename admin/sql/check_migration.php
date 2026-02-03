<?php
require_once __DIR__ . '/../../config/database.php';

function checkTable($pdo, $table) {
    echo "--- Table: $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    foreach($stmt->fetchAll() as $row) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
}

checkTable($pdo, 'car_composicao_ideal');
checkTable($pdo, 'car_estoque_atual');
checkTable($pdo, 'car_checklist_itens');
