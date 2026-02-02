<?php
require_once __DIR__ . '/../../config/database.php';

$user_id = $pdo->query("SELECT id FROM usuarios WHERE nivel_acesso = 'Administrador' LIMIT 1")->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM permissoes WHERE id_usuario = ? AND id_modulo = 11");
$stmt->execute([$user_id]);
$perms = $stmt->fetchAll();

echo "Permissões encontradas para o módulo 11:\n";
foreach ($perms as $p) {
    echo "ID: {$p['id']} | Modulo: {$p['id_modulo']} | User: {$p['id_usuario']}\n";
}

if (count($perms) > 1) {
    echo "Limpando duplicatas...\n";
    $ids = array_column($perms, 'id');
    array_shift($ids); // Mantém o primeiro
    $ids_str = implode(',', $ids);
    $pdo->exec("DELETE FROM permissoes WHERE id IN ($ids_str)");
    echo "Sucesso: Duplicatas removidas.";
} else {
    echo "Nenhuma duplicata de permissão encontrada.";
}
