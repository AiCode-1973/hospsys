<?php
require_once __DIR__ . '/../config/database.php';
$res = $pdo->query("SHOW CREATE TABLE car_estoque_atual")->fetch();
echo $res['Create Table'];
