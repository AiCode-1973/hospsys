<?php
require_once __DIR__ . '/../../config/database.php';

$tables = ['fugulin_pacientes', 'fugulin_classificacoes'];

foreach ($tables as $table) {
    echo "COLUMNS FOR $table:\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    echo "\n";
}
