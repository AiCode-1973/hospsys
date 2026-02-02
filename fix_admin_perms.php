<?php
error_reporting(0);
try {
    $pdo = new PDO('mysql:host=186.209.113.107;dbname=dema5738_hospsys', 'dema5738_hospsys', 'Dema@1973');
    
    // Ensure User ID 1 (Admin) has all permissions for all modules
    $modulos = $pdo->query("SELECT id FROM modulos")->fetchAll(PDO::FETCH_COLUMN);
    $stmt_check = $pdo->prepare("SELECT id FROM permissoes WHERE id_usuario = 1 AND id_modulo = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES (1, ?, 1, 1, 1, 1)");
    $stmt_update = $pdo->prepare("UPDATE permissoes SET pode_visualizar = 1, pode_criar = 1, pode_editar = 1, pode_excluir = 1 WHERE id_usuario = 1 AND id_modulo = ?");
    
    foreach ($modulos as $mid) {
        $stmt_check->execute([$mid]);
        if ($stmt_check->fetch()) {
            $stmt_update->execute([$mid]);
        } else {
            $stmt_insert->execute([$mid]);
        }
    }
    
    echo "ADMIN_READY";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
