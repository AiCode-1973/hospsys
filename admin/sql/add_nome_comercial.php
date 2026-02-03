<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo->query("ALTER TABLE car_itens_mestres ADD COLUMN nome_comercial VARCHAR(255) AFTER nome");
    echo "Coluna 'nome_comercial' adicionada com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
