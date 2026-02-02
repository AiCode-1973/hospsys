<?php
/**
 * Middleware para verificar permissões de acesso
 * Inclua este arquivo no topo de cada página protegida
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 1. Verifica se o usuário está logado
if (!isLoggedIn()) {
    redirect(url('auth/login.php'));
}

// 2. Define o módulo atual baseado na URL
$url_atual = $_SERVER['PHP_SELF'];
// Tenta extrair o caminho relativo (ex: admin/usuarios.php)
$rota_procurada = '';
if (strpos($url_atual, 'admin/') !== false) {
    $parts = explode('admin/', $url_atual);
    $rota_procurada = 'admin/' . end($parts);
}

// 3. Verifica permissões no banco de dados
// Se for Administrador, tem acesso total (opcional, dependendo da regra de negócio)
$id_usuario = $_SESSION['user_id'];
$nivel_acesso = $_SESSION['user_nivel'];

if ($nivel_acesso === 'Administrador' && $rota_procurada !== 'admin/permissoes.php') {
    // Administrador pode ver quase tudo, mas vamos manter a verificação por segurança
    // Ou simplesmente retornar verdadeiro aqui.
}

// Busca permissão específica para o módulo
// Ajuste: Se for uma página do Fugulin, usamos a permissão do fugulin_lista.php
$rota_busca = $rota_procurada;
if (strpos($rota_procurada, 'admin/fugulin_') !== false) {
    $rota_busca = 'admin/fugulin_lista.php';
}

$stmt = $pdo->prepare("
    SELECT p.*, m.nome_modulo 
    FROM permissoes p 
    JOIN modulos m ON p.id_modulo = m.id 
    WHERE p.id_usuario = ? AND m.rota = ?
");
$stmt->execute([$id_usuario, $rota_busca]);
$permissao = $stmt->fetch();

// Ignoramos a verificação para o home e dashboard por enquanto para evitar loop de redirecionamento
if ($rota_procurada !== 'admin/home.php') {
    if (!$permissao || $permissao['pode_visualizar'] == 0) {
        // Se for admin e não tiver permissão específica, talvez devêssemos permitir? 
        // O usuário pediu "apenas o que tem permissão", então vamos ser rigorosos.
        $_SESSION['mensagem_erro'] = "Você não tem permissão para acessar este módulo.";
        redirect(url('admin/home.php'));
    }
}

// Define permissões globais para uso na página
$can_create = ($permissao['pode_criar'] ?? 0) || ($nivel_acesso === 'Administrador');
$can_edit   = ($permissao['pode_editar'] ?? 0) || ($nivel_acesso === 'Administrador');
$can_delete = ($permissao['pode_excluir'] ?? 0) || ($nivel_acesso === 'Administrador');
?>
