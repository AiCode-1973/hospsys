<?php
include 'config/database.php';

$res = [];

// Check Sector
$stmt = $pdo->prepare("SELECT id FROM fugulin_setores WHERE nome LIKE ?");
$stmt->execute(['%TOMOGRAFIA%']);
$res['setor_tomografia'] = $stmt->fetch();

// Check Cart
$stmt = $pdo->prepare("SELECT id FROM car_carrinhos WHERE nome LIKE ? AND ativo = 1");
$stmt->execute(['%TOMOGRAFIA%']);
$res['carrinho_tomografia'] = $stmt->fetch();

echo json_encode($res);
