<?php
include 'config/database.php';

echo "=== SETORES ===\n";
$setores = $pdo->query("SELECT id, nome FROM fugulin_setores")->fetchAll();
foreach ($setores as $s) {
    echo "ID: {$s['id']} | Nome: {$s['nome']}\n";
}

echo "\n=== CARRINHOS ===\n";
$carrinhos = $pdo->query("SELECT id, nome, id_setor FROM car_carrinhos WHERE ativo = 1")->fetchAll();
foreach ($carrinhos as $c) {
    echo "ID: {$c['id']} | Nome: {$c['nome']} | Setor: {$c['id_setor']}\n";
}

echo "\n=== CATALOGO (Primeiros 20) ===\n";
$itens = $pdo->query("SELECT id, nome FROM car_itens_mestres LIMIT 20")->fetchAll();
foreach ($itens as $i) {
    echo "ID: {$i['id']} | Nome: {$i['nome']}\n";
}
