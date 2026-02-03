<?php
/**
 * Funções utilitárias de segurança e sistema
 */

session_start();
ob_start();

/**
 * Retorna o caminho absoluto do sistema
 */
function url($path = '') {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_dir = str_replace(['/auth', '/admin', '/includes'], '', dirname($script_name));
    $base_dir = rtrim($base_dir, '/\\');
    return $base_dir . '/' . ltrim($path, '/');
}

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

/**
 * Recalcula e atualiza o status de um carrinho (OK, Atenção, Crítico)
 * Baseado na composição ideal vs estoque atual
 */
function atualizarStatusCarrinho($pdo, $id_carrinho) {
    if (!$id_carrinho) return;

    // 1. Busca composição vs estoque
    $sql = "
        SELECT comp.quantidade_ideal, comp.quantidade_minima,
               est.quantidade_atual, est.data_validade
        FROM car_composicao_ideal comp
        LEFT JOIN car_estoque_atual est ON (comp.id_item = est.id_item AND comp.id_carrinho = est.id_carrinho)
        WHERE comp.id_carrinho = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_carrinho]);
    $itens = $stmt->fetchAll();

    $novo_status = 'OK';

    // Se não houver itens padronizados, o status é OK (vazio)
    if (!empty($itens)) {
        foreach ($itens as $i) {
            $qtd = $i['quantidade_atual'] ?? 0;
            
            // Verificação de Validade
            if (!empty($i['data_validade'])) {
                $hoje = time();
                $validade = strtotime($i['data_validade']);
                $dias = ($validade - $hoje) / 86400;

                if ($validade < $hoje || $dias < 30) {
                    $novo_status = 'Crítico';
                } elseif ($dias < 90 && $novo_status !== 'Crítico') {
                    $novo_status = 'Atenção';
                }
            }

            // Verificação de Quantidade
            if ($qtd < $i['quantidade_minima']) {
                $novo_status = 'Crítico';
            } elseif ($qtd < $i['quantidade_ideal'] && $novo_status !== 'Crítico') {
                $novo_status = 'Atenção';
            }

            // Se atingiu o status Crítico, já podemos parar o loop
            if ($novo_status === 'Crítico') break;
        }
    }

    $stmt_up = $pdo->prepare("UPDATE car_carrinhos SET status = ? WHERE id = ?");
    $stmt_up->execute([$novo_status, (int)$id_carrinho]);
}
