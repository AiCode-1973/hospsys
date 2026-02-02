<?php
require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->query("SELECT * FROM modulos WHERE nome_modulo LIKE '%Carrinho%' OR nome_modulo LIKE '%Emergência%' OR rota LIKE '%car_%'");
$modulos = $stmt->fetchAll();

$output = "Módulos encontrados:\n";
foreach ($modulos as $m) {
    $output .= "ID: {$m['id']} | Nome: {$m['nome_modulo']} | Rota: {$m['rota']}\n";
}
file_put_contents(__DIR__ . '/search_results.txt', $output);
echo "Busca concluída em search_results.txt";
