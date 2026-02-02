<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // 1. Insere o módulo de Setores
    $stmt = $pdo->prepare("INSERT INTO modulos (nome_modulo, rota, icone) VALUES (?, ?, ?)");
    $stmt->execute(['Setores', 'admin/setores.php', 'fas fa-map-marked-alt']);
    $mod_id = $pdo->lastInsertId();

    // 2. Concede permissão para o administrador (pega o primeiro administrador)
    $user_id = $pdo->query("SELECT id FROM usuarios WHERE nivel_acesso = 'Administrador' LIMIT 1")->fetchColumn();
    
    if ($user_id && $mod_id) {
        $stmt_perm = $pdo->prepare("INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES (?, ?, 1, 1, 1, 1)");
        $stmt_perm->execute([$user_id, $mod_id]);
        echo "Módulo Setores registrado e permissões concedidas.";
    } else {
        echo "Erro: Usuário ou Módulo não encontrado.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
