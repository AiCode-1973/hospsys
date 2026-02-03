<?php
require_once __DIR__ . '/../../config/database.php';

function checkTableInfo($pdo, $table) {
    echo "--- Table: $table ---\n";
    $stmt = $pdo->query("SHOW CREATE TABLE $table");
    $row = $stmt->fetch();
    echo $row['Create Table'] . "\n\n";
}

checkTableInfo($pdo, 'fugulin_pacientes');
checkTableInfo($pdo, 'fugulin_classificacoes');
