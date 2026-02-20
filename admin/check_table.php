<?php
require_once 'config/database.php';
$table = 'car_estoque_atual';
$stmt = $pdo->query("SHOW CREATE TABLE $table");
$row = $stmt->fetch();
echo $row['Create Table'];
