<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Debug de Menu</h2>";

// 1. Verificar Sessão
echo "<h3>1. Dados da Sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Verificar Usuário no Banco
if (isset($_SESSION['user_id'])) {
    echo "<h3>2. Dados do Usuário no Banco:</h3>";
    $stmt = $pdo->prepare("SELECT id, nome, usuario, nivel_acesso FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    echo "<pre>";
    print_r($user);
    echo "</pre>";
}

// 3. Verificar Módulos Registrados
echo "<h3>3. Módulos na Tabela 'modulos':</h3>";
try {
    $modulos = $pdo->query("SELECT * FROM modulos")->fetchAll();
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Rota</th><th>Ícone</th></tr>";
    foreach ($modulos as $m) {
        echo "<tr>";
        echo "<td>{$m['id']}</td>";
        echo "<td>{$m['nome_modulo']}</td>";
        echo "<td>{$m['rota']}</td>";
        echo "<td><i class='{$m['icone']}'></i> {$m['icone']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar módulos: " . $e->getMessage() . "</p>";
}

// 4. Testar a Query do Header
echo "<h3>4. Teste da Query do Header (Simulando Admin):</h3>";
$stmt_admin = $pdo->query("SELECT * FROM modulos ORDER BY nome_modulo ASC");
$test_menu = $stmt_admin->fetchAll();
echo "Total de módulos encontrados para Admin: " . count($test_menu);
?>
