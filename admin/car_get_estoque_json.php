<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

$id_carrinho = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_carrinho) {
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

try {
    $sql = "
        SELECT i.nome, i.tipo, i.unidade,
               comp.quantidade_ideal, comp.gaveta,
               SUM(est.quantidade_atual) as quantidade_atual, 
               MIN(est.data_validade) as data_validade
        FROM car_itens_mestres i
        JOIN car_composicao_ideal comp ON i.id = comp.id_item
        LEFT JOIN car_estoque_atual est ON (i.id = est.id_item AND est.id_carrinho = comp.id_carrinho)
        WHERE comp.id_carrinho = ? AND i.ativo = 1
        GROUP BY i.id, comp.gaveta
        ORDER BY comp.gaveta, i.nome
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_carrinho]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($itens);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
