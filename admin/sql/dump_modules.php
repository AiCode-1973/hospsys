<?php
require_once __DIR__ . '/../../config/database.php';
$modulos = $pdo->query("SELECT * FROM modulos")->fetchAll();
file_put_contents(__DIR__ . '/debug_modules.txt', print_r($modulos, true));
echo "Dump concluído em debug_modules.txt";
