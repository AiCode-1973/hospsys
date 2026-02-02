<?php
/**
 * Funções utilitárias de segurança e sistema
 */

session_start();
ob_start();

/**
 * Sanitização de entrada para evitar XSS
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Gera um token CSRF para formulários
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirecionamento simples
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Formata CPF para o padrão 000.000.000-00
 */
function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return $cpf;
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Log de auditoria simples
 */
function logAccess($pdo, $user_id, $usuario_tentativa, $sucesso, $mensagem) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO logs_acesso (id_usuario, usuario_tentativa, ip_address, sucesso, mensagem) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $usuario_tentativa, $ip, $sucesso, $mensagem]);
}
