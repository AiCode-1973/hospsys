<?php
require_once __DIR__ . '/../../config/database.php';
$stmt = $pdo->query("DESCRIBE car_itens_mestres");
foreach($stmt->fetchAll() as $row) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
