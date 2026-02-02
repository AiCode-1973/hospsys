<?php
require_once __DIR__ . '/config/database.php';

echo "Tables:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

if (in_array('fugulin_pacientes', $tables)) {
    echo "\nfugulin_pacientes columns:\n";
    print_r($pdo->query("DESCRIBE fugulin_pacientes")->fetchAll(PDO::FETCH_ASSOC));
}

echo "\nfugulin_classificacoes columns:\n";
print_r($pdo->query("DESCRIBE fugulin_classificacoes")->fetchAll(PDO::FETCH_ASSOC));
