<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user_nivel'] !== 'Administrador') {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar') {
        $nome = cleanInput($_POST['nome']);
        $usuario_login = cleanInput($_POST['usuario']);
        $email = cleanInput($_POST['email']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Limpa CPF
        $nivel_acesso = $_POST['nivel_acesso'];
        $senha = $_POST['senha'];
        
        // Validação básica
        if (strlen($senha) < 8) {
            $_SESSION['mensagem_erro'] = "A senha deve ter no mínimo 8 caracteres.";
            redirect('usuarios.php');
        }

        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, cpf, usuario, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $cpf, $usuario_login, $senha_hash, $nivel_acesso]);
            
            $id_novo_usuario = $pdo->lastInsertId();

            // Atribui permissões básicas (visualização) para todos os módulos existentes
            $modulos = $pdo->query("SELECT id FROM modulos")->fetchAll(PDO::FETCH_COLUMN);
            $stmt_perm = $pdo->prepare("INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar) VALUES (?, ?, ?)");
            foreach ($modulos as $id_modulo) {
                $stmt_perm->execute([$id_novo_usuario, $id_modulo, 1]);
            }

            $pdo->commit();
            $_SESSION['mensagem_sucesso'] = "Usuário '$nome' criado com sucesso!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $_SESSION['mensagem_erro'] = "Erro: Usuário, CPF ou E-mail já cadastrados.";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao criar usuário: " . $e->getMessage();
            }
        }
    }
    
    redirect('usuarios.php');
}
?>
