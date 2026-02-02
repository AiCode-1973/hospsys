<?php
require_once __DIR__ . '/../../config/database.php';
$stmt = $pdo->query("DESCRIBE fugulin_setores");
foreach($stmt->fetchAll() as $row) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
