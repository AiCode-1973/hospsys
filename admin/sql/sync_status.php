<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

$carrinhos = $pdo->query("SELECT id FROM car_carrinhos WHERE ativo = 1")->fetchAll();

foreach ($carrinhos as $c) {
    atualizarStatusCarrinho($pdo, $c['id']);
}

echo "Status de todos os carrinhos recalculados!";
