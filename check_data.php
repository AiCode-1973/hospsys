<?php
require_once __DIR__ . '/config/database.php';

try {
    $modulos = $pdo->query("SELECT COUNT(*) FROM modulos")->fetchColumn();
    $permissoes = $pdo->query("SELECT COUNT(*) FROM permissoes WHERE id_usuario = 1")->fetchColumn();
    
    echo "Modulos: $modulos\n";
    echo "Permissoes Admin: $permissoes\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
