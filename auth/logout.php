<?php
require_once __DIR__ . '/../includes/functions.php';

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir o cookie da sessão também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
?>
