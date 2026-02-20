<?php
include 'config/database.php';

$id_carrinho = 9;

echo "=== COMPOSICAO ATUAL (Carrinho 9) ===\n";
$stmt = $pdo->prepare("
    SELECT c.id_item, i.nome, c.quantidade_ideal, c.quantidade_minima, c.gaveta
    FROM car_composicao_ideal c
    JOIN car_itens_mestres i ON c.id_item = i.id
    WHERE c.id_carrinho = ?
    ORDER BY c.gaveta, i.nome
");
$stmt->execute([$id_carrinho]);
$itens = $stmt->fetchAll();

foreach ($itens as $i) {
    echo "G{$i['gaveta']} | {$i['nome']} | Ideal: {$i['quantidade_ideal']} | Min: {$i['quantidade_minima']}\n";
}

echo "\n=== GAVETAS ATUAIS ===\n";
$stmt = $pdo->prepare("SELECT num_gaveta, descricao FROM car_gavetas_config WHERE id_carrinho = ?");
$stmt->execute([$id_carrinho]);
$gavetas = $stmt->fetchAll();
foreach ($gavetas as $g) {
    echo "N{$g['num_gaveta']}: {$g['descricao']}\n";
}
