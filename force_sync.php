<?php
require_once __DIR__ . '/config/database.php';

echo "Sincronizando Módulos...\n";

$mods = [
    ['Dashboard', 'Painel principal com estatísticas', 'admin/dashboard.php', 'fas fa-tachometer-alt'],
    ['Usuários', 'Gerenciamento de usuários do sistema', 'admin/usuarios.php', 'fas fa-users'],
    ['Permissões', 'Gerenciamento de permissões de acesso', 'admin/permissoes.php', 'fas fa-user-shield'],
    ['Classificação Fugulin', 'Sistema de classificação de dependência de enfermagem', 'admin/fugulin_novo.php', 'fas fa-clipboard-list'],
    ['Histórico Fugulin', 'Histórico e relatórios de classificações', 'admin/fugulin_lista.php', 'fas fa-history'],
    ['Config. Fugulin', 'Configuração de questões e opções Fugulin', 'admin/fugulin_config.php', 'fas fa-cog']
];

foreach ($mods as $m) {
    $stmt = $pdo->prepare("SELECT id FROM modulos WHERE rota = ?");
    $stmt->execute([$m[2]]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO modulos (nome_modulo, descricao, rota, icone) VALUES (?, ?, ?, ?)");
        $ins->execute($m);
        echo "Adicionado: {$m[0]}\n";
    } else {
        echo "Já existe: {$m[0]}\n";
    }
}

echo "\nVerificando Usuário Admin:\n";
$stmt = $pdo->query("SELECT id, usuario, nivel_acesso FROM usuarios WHERE usuario = 'admin'");
$admin = $stmt->fetch();
if ($admin) {
    echo "ID: {$admin['id']} | Usuário: {$admin['usuario']} | Nível: {$admin['nivel_acesso']}\n";
    if ($admin['nivel_acesso'] !== 'Administrador') {
        $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'Administrador' WHERE id = ?")->execute([$admin['id']]);
        echo "Nível de acesso corrigido para 'Administrador'.\n";
    }
} else {
    echo "Usuário 'admin' não encontrado!\n";
}
echo "\nEstado Final dos Módulos:\n";
$stmt = $pdo->query("SELECT nome_modulo FROM modulos");
while($r = $stmt->fetch()) echo "MOD: [{$r['nome_modulo']}]\n";
?>
