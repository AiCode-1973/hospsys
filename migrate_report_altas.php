<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->prepare("SELECT id FROM modulos WHERE rota = 'admin/fugulin_relatorio_altas.php'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "Aviso: O módulo 'Relatório de Altas' já está registrado.";
    } else {
        $stmt_ins = $pdo->prepare("INSERT INTO modulos (nome_modulo, rota, icone, categoria) VALUES (?, ?, ?, ?)");
        $stmt_ins->execute(['Relatório de Altas', 'admin/fugulin_relatorio_altas.php', 'fas fa-file-contract', 'Fugulin']);
        $id_modulo = $pdo->lastInsertId();

        $stmt_admin = $pdo->prepare("SELECT id FROM usuarios WHERE nivel_acesso = 'Administrador'");
        $stmt_admin->execute();
        $admins = $stmt_admin->fetchAll();

        $stmt_perm = $pdo->prepare("INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar, pode_criar, pode_editar, pode_excluir) VALUES (?, ?, 1, 1, 1, 1)");
        foreach ($admins as $admin) {
            $stmt_perm->execute([$admin['id'], $id_modulo]);
        }
        echo "Sucesso: Módulo 'Relatório de Altas' registrado.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
