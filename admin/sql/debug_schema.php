<?php
require_once __DIR__ . '/../../config/database.php';
$output = "";
$tables = ['fugulin_pacientes', 'fugulin_classificacoes'];
foreach ($tables as $table) {
    $output .= "COLUMNS FOR $table:\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= "- {$row['Field']} ({$row['Type']})\n";
    }
    $output .= "\n";
}
file_put_contents(__DIR__ . '/schema_output.txt', $output);
echo "SCHEMA_WRITTEN";
