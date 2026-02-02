<?php
require_once __DIR__ . '/../auth/verificar_permissao.php';

// Apenas administradores podem gerenciar setores
if ($_SESSION['user_nivel'] !== 'Administrador') {
    $_SESSION['mensagem_erro'] = "Acesso restrito a administradores.";
    redirect('home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['mensagem_erro'] = "Falha na validação de segurança.";
        redirect('setores.php');
    }

    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar_setor') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nome = cleanInput($_POST['nome']);

        if (empty($nome)) {
            $_SESSION['mensagem_erro'] = "O nome do setor é obrigatório.";
            redirect('setores.php');
        }

        try {
            if ($id) {
                // Editar setor existente
                $stmt = $pdo->prepare("UPDATE fugulin_setores SET nome = ? WHERE id = ?");
                $stmt->execute([$nome, $id]);
                $_SESSION['mensagem_sucesso'] = "Setor atualizado com sucesso!";
            } else {
                // Criar novo setor
                $stmt = $pdo->prepare("INSERT INTO fugulin_setores (nome) VALUES (?)");
                $stmt->execute([$nome]);
                $_SESSION['mensagem_sucesso'] = "Novo setor cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro ao salvar setor: " . $e->getMessage();
        }
    }
    redirect('setores.php');
}

// Lógica para excluir setor (poderia ser via POST para mais segurança, mas seguindo padrão do projeto para itens)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'excluir') {
    $id = (int)$_GET['id'];
    
    try {
        // Verifica se o setor está em uso por carrinhos ou pacientes/classificações
        $stmt_check_car = $pdo->prepare("SELECT COUNT(*) FROM car_carrinhos WHERE id_setor = ?");
        $stmt_check_car->execute([$id]);
        $em_uso_car = $stmt_check_car->fetchColumn();

        $stmt_check_fug = $pdo->prepare("SELECT COUNT(*) FROM fugulin_classificacoes WHERE id_setor = ?");
        $stmt_check_fug->execute([$id]);
        $em_uso_fug = $stmt_check_fug->fetchColumn();

        if ($em_uso_car > 0 || $em_uso_fug > 0) {
            $_SESSION['mensagem_erro'] = "Não é possível excluir este setor pois ele possui registros vinculados.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM fugulin_setores WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['mensagem_sucesso'] = "Setor removido com sucesso!";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao excluir setor: " . $e->getMessage();
    }
    redirect('setores.php');
}

redirect('setores.php');
