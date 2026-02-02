<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // 1. Identifica o ID duplicado (aquele que tem a mesma rota mas ID maior)
    $stmt = $pdo->query("
        SELECT id FROM modulos 
        WHERE rota = 'admin/car_dashboard.php' 
        ORDER BY id DESC LIMIT 1
    ");
    $id_duplicado = $stmt->fetchColumn();

    // 2. Verifica se existe mais de um
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM modulos WHERE rota = 'admin/car_dashboard.php'");
    $count = $stmt_count->fetchColumn();

    if ($count > 1 && $id_duplicado) {
        // Remove permissões associadas a esse ID específico
        $pdo->prepare("DELETE FROM permissoes WHERE id_modulo = ?")->execute([$id_duplicado]);
        // Remove o módulo duplicado
        $pdo->prepare("DELETE FROM modulos WHERE id = ?")->execute([$id_duplicado]);
        echo "Sucesso: Módulo duplicado (ID: $id_duplicado) removido.";
    } else {
        echo "Nenhuma duplicata encontrada para 'admin/car_dashboard.php'.";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
