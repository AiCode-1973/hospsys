<?php
require_once __DIR__ . '/../auth/verificar_permissao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['mensagem_erro'] = "Falha na validação de segurança (CSRF).";
        redirect('perfil.php');
    }

    $id_usuario = $_SESSION['user_id'];
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validações básicas
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $_SESSION['mensagem_erro'] = "Todos os campos de senha são obrigatórios.";
        redirect('perfil.php');
    }

    if ($nova_senha !== $confirmar_senha) {
        $_SESSION['mensagem_erro'] = "A nova senha e a confirmação não coincidem.";
        redirect('perfil.php');
    }

    if (strlen($nova_senha) < 6) {
        $_SESSION['mensagem_erro'] = "A nova senha deve ter pelo menos 6 caracteres.";
        redirect('perfil.php');
    }

    // Busca senha atual no banco
    $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($senha_atual, $user['senha'])) {
        $_SESSION['mensagem_erro'] = "A senha atual informada está incorreta.";
        redirect('perfil.php');
    }

    // Atualiza a senha
    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    
    if ($stmt_update->execute([$novo_hash, $id_usuario])) {
        $_SESSION['mensagem_sucesso'] = "Sua senha foi alterada com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao tentar atualizar a senha no banco de dados.";
    }

    redirect('perfil.php');
} else {
    redirect('perfil.php');
}
