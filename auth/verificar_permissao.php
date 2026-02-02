<?php
/**
 * Middleware para verificar permissões de acesso
 * Inclua este arquivo no topo de cada página protegida
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 1. Verifica se o usuário está logado
if (!isLoggedIn()) {
    redirect('/hospsys/auth/login.php');
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
$stmt = $pdo->prepare("
    SELECT p.*, m.nome_modulo 
    FROM permissoes p 
    JOIN modulos m ON p.id_modulo = m.id 
    WHERE p.id_usuario = ? AND m.rota = ?
");
$stmt->execute([$id_usuario, $rota_procurada]);
$permissao = $stmt->fetch();

// Se não houver registro de permissão e não for admin, ou se não puder visualizar
// Ignoramos a verificação para o dashboard principal por enquanto (ou adicionamos ele na tabela)
if ($rota_procurada !== 'admin/dashboard.php') {
    if (!$permissao || $permissao['pode_visualizar'] == 0) {
        if ($nivel_acesso !== 'Administrador') {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para acessar este módulo.";
            redirect('/hospsys/admin/dashboard.php');
        }
    }
}

// Define permissões globais para uso na página
$can_create = ($permissao['pode_criar'] ?? 0) || ($nivel_acesso === 'Administrador');
$can_edit   = ($permissao['pode_editar'] ?? 0) || ($nivel_acesso === 'Administrador');
$can_delete = ($permissao['pode_excluir'] ?? 0) || ($nivel_acesso === 'Administrador');
?>
