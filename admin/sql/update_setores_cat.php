<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $stmt = $pdo->prepare("UPDATE modulos SET categoria = 'Administração' WHERE rota = 'admin/setores.php'");
    $stmt->execute();
    echo "Categoria do módulo Setores atualizada para 'Administração'.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
