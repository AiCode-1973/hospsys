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

            // Atribui apenas a permissão do módulo Início (ID 7) por padrão
            $stmt_perm = $pdo->prepare("INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar) VALUES (?, 7, 1)");
            $stmt_perm->execute([$id_novo_usuario]);

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
    } elseif ($acao === 'editar') {
        $id = (int)$_POST['id'];
        $nome = cleanInput($_POST['nome']);
        $usuario_login = cleanInput($_POST['usuario']);
        $email = cleanInput($_POST['email']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $nivel_acesso = $_POST['nivel_acesso'];
        $senha = $_POST['senha'] ?? '';
        
        try {
            if (!empty($senha)) {
                if (strlen($senha) < 8) {
                    $_SESSION['mensagem_erro'] = "A nova senha deve ter no mínimo 8 caracteres.";
                    redirect('usuarios.php');
                }
                $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, cpf = ?, usuario = ?, senha_hash = ?, nivel_acesso = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $cpf, $usuario_login, $senha_hash, $nivel_acesso, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, cpf = ?, usuario = ?, nivel_acesso = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $cpf, $usuario_login, $nivel_acesso, $id]);
            }
            $_SESSION['mensagem_sucesso'] = "Usuário '$nome' atualizado com sucesso!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['mensagem_erro'] = "Erro: Usuário, CPF ou E-mail já cadastrados.";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao atualizar usuário: " . $e->getMessage();
            }
        }
    }
    
    redirect('usuarios.php');
}
?>
