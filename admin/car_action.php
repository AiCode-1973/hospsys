<?php
require_once __DIR__ . '/../auth/verificar_permissao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['mensagem_erro'] = "Falha na validação de segurança.";
        redirect('car_dashboard.php');
    }

    $acao = $_POST['acao'] ?? '';

    // 1. Criar Novo Carrinho
    if ($acao === 'criar_carrinho') {
        $nome = cleanInput($_POST['nome']);
        $id_setor = (int)$_POST['id_setor'];
        $localizacao = cleanInput($_POST['localizacao']);
        $token = bin2hex(random_bytes(16)); // Token para QR Code

        if (empty($nome) || empty($id_setor)) {
            $_SESSION['mensagem_erro'] = "Nome e Setor são obrigatórios.";
            redirect('car_dashboard.php');
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO car_carrinhos (nome, id_setor, localizacao, qr_code_token) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $id_setor, $localizacao, $token])) {
                $_SESSION['mensagem_sucesso'] = "Carrinho cadastrado com sucesso!";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao cadastrar carrinho.";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro no banco de dados: " . $e->getMessage();
        }
        redirect('car_dashboard.php');
    }

    // 1.1 Editar Carrinho
    if ($acao === 'editar_carrinho') {
        $id = (int)$_POST['id'];
        $nome = cleanInput($_POST['nome']);
        $id_setor = (int)$_POST['id_setor'];
        $localizacao = cleanInput($_POST['localizacao']);

        if (empty($nome) || empty($id_setor)) {
            $_SESSION['mensagem_erro'] = "Nome e Setor são obrigatórios.";
            redirect('car_dashboard.php');
        }

        try {
            $stmt = $pdo->prepare("UPDATE car_carrinhos SET nome = ?, id_setor = ?, localizacao = ? WHERE id = ?");
            $stmt->execute([$nome, $id_setor, $localizacao, $id]);
            $_SESSION['mensagem_sucesso'] = "Carrinho atualizado com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro: " . $e->getMessage();
        }
        redirect('car_dashboard.php');
    }

    // 2. Salvar Item (Criar ou Editar)
    if ($acao === 'salvar_item') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $nome = cleanInput($_POST['nome']);
        $nome_comercial = cleanInput($_POST['nome_comercial'] ?? '');
        $tipo = cleanInput($_POST['tipo']);
        $unidade = cleanInput($_POST['unidade']);
        $descricao = cleanInput($_POST['descricao']);

        if (empty($nome) || empty($tipo)) {
            $_SESSION['mensagem_erro'] = "Nome e Tipo são obrigatórios.";
            redirect('car_itens.php');
        }

        try {
            if ($id) {
                // Editar
                $stmt = $pdo->prepare("UPDATE car_itens_mestres SET nome = ?, nome_comercial = ?, tipo = ?, unidade = ?, descricao = ? WHERE id = ?");
                $stmt->execute([$nome, $nome_comercial, $tipo, $unidade, $descricao, $id]);
                $_SESSION['mensagem_sucesso'] = "Item atualizado com sucesso!";
            } else {
                // Criar
                $stmt = $pdo->prepare("INSERT INTO car_itens_mestres (nome, nome_comercial, tipo, unidade, descricao) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $nome_comercial, $tipo, $unidade, $descricao]);
                $_SESSION['mensagem_sucesso'] = "Novo item adicionado ao catálogo!";
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem_erro'] = "Erro: " . $e->getMessage();
        }
        redirect('car_itens.php');
    }

    // 3. Salvar Composição Ideal (Padronização)
    if ($acao === 'salvar_composicao') {
        $id_carrinho = (int)$_POST['id_carrinho'];
        $item_ids = $_POST['item_id'] ?? [];
        $qtd_ideais = $_POST['qtd_ideal'] ?? [];
        $qtd_minimas = $_POST['qtd_minima'] ?? [];

        try {
            $pdo->beginTransaction();

            // Reseta composicao atual para reconstruir
            $stmt_del = $pdo->prepare("DELETE FROM car_composicao_ideal WHERE id_carrinho = ?");
            $stmt_del->execute([$id_carrinho]);

            $stmt_ins = $pdo->prepare("INSERT INTO car_composicao_ideal (id_carrinho, id_item, quantidade_ideal, quantidade_minima) VALUES (?, ?, ?, ?)");
            
            foreach ($item_ids as $index => $item_id) {
                if (!empty($item_id)) {
                    $stmt_ins->execute([
                        $id_carrinho, 
                        (int)$item_id, 
                        (int)$qtd_ideais[$index], 
                        (int)$qtd_minimas[$index]
                    ]);
                }
            }

            $pdo->commit();
            atualizarStatusCarrinho($pdo, $id_carrinho);
            $_SESSION['mensagem_sucesso'] = "Padrão do carrinho atualizado com sucesso!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensagem_erro'] = "Erro ao salvar padronização: " . $e->getMessage();
        }
        redirect("car_estoque.php?id=$id_carrinho");
    }

    // 4. Salvar Checklist e Atualizar Estoque + Status
    if ($acao === 'salvar_checklist') {
        $id_carrinho = (int)$_POST['id_carrinho'];
        $tipo_checklist = cleanInput($_POST['tipo_checklist']);
        $observacoes = cleanInput($_POST['observacoes']);
        
        $item_qtds = $_POST['item_qtd'] ?? [];
        $item_lotes = $_POST['item_lote'] ?? [];
        $item_validades = $_POST['item_validade'] ?? [];

        try {
            $pdo->beginTransaction();

            // 1. Salva Cabeçalho do Checklist
            $stmt_check = $pdo->prepare("INSERT INTO car_checklists (id_carrinho, id_usuario, tipo, observacoes) VALUES (?, ?, ?, ?)");
            $stmt_check->execute([$id_carrinho, $_SESSION['user_id'], $tipo_checklist, $observacoes]);
            $id_checklist = $pdo->lastInsertId();

            // 2. Processa cada item
            foreach ($item_qtds as $item_id => $qtd) {
                $lote = $item_lotes[$item_id] ?? '';
                $validade = !empty($item_validades[$item_id]) ? $item_validades[$item_id] : null;
                
                // Salva detalhe do checklist
                $stmt_det = $pdo->prepare("INSERT INTO car_checklist_itens (id_checklist, id_item, conferido, quantidade_encontrada, validade_conferida) VALUES (?, ?, 1, ?, ?)");
                $stmt_det->execute([$id_checklist, $item_id, $qtd, $validade]);

                // Atualiza/Insere Estoque Atual
                $stmt_est = $pdo->prepare("
                    INSERT INTO car_estoque_atual (id_carrinho, id_item, lote, data_validade, quantidade_atual) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE lote = VALUES(lote), data_validade = VALUES(data_validade), quantidade_atual = VALUES(quantidade_atual)
                ");
                $stmt_est->execute([$id_carrinho, $item_id, $lote, $validade, (int)$qtd]);

                // Log de Movimentação (simplificado: ajuste via checklist)
                $stmt_mov = $pdo->prepare("INSERT INTO car_movimentacoes (id_carrinho, id_item, id_usuario, tipo_movimentacao, quantidade, observacao) VALUES (?, ?, ?, 'Ajuste', ?, 'Ajuste via Checklist')");
                $stmt_mov->execute([$id_carrinho, $item_id, $_SESSION['user_id'], (int)$qtd]);
            }

            // 3. Atualiza status final do carrinho usando a função centralizada
            atualizarStatusCarrinho($pdo, $id_carrinho);

            $pdo->commit();
            $_SESSION['mensagem_sucesso'] = "Checklist finalizado com sucesso!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensagem_erro'] = "Erro ao processar checklist: " . $e->getMessage();
        }
        redirect('car_dashboard.php');
    }

    // Outras ações virão aqui...
} else {
    // Ações via GET
    $acao = $_GET['acao'] ?? '';
    
    if ($acao === 'excluir_item') {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE car_itens_mestres SET ativo = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['mensagem_sucesso'] = "Item removido do catálogo.";
        }
        redirect('car_itens.php');
    }

    // Excluir Carrinho
    if ($acao === 'excluir_carrinho') {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE car_carrinhos SET ativo = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['mensagem_sucesso'] = "Carrinho removido com sucesso!";
        }
        redirect('car_dashboard.php');
    }

    // Excluir Item do Padrão de um Carrinho
    if ($acao === 'excluir_padrao') {
        $id_carrinho = (int)$_GET['id_carrinho'];
        $id_item = (int)$_GET['id_item'];
        
        $stmt = $pdo->prepare("DELETE FROM car_composicao_ideal WHERE id_carrinho = ? AND id_item = ?");
        if ($stmt->execute([$id_carrinho, $id_item])) {
            atualizarStatusCarrinho($pdo, $id_carrinho);
            $_SESSION['mensagem_sucesso'] = "Item removido do padrão deste carrinho.";
        }
        redirect("car_estoque.php?id=$id_carrinho");
    }

    // Excluir Checklist (Auditoria)
    if ($acao === 'excluir_checklist') {
        $id = (int)$_GET['id'];
        
        // Busca o ID do carrinho antes de excluir para poder recalcular o status
        $stmt_get = $pdo->prepare("SELECT id_carrinho FROM car_checklists WHERE id = ?");
        $stmt_get->execute([$id]);
        $id_carrinho = $stmt_get->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM car_checklists WHERE id = ?");
        if ($stmt->execute([$id])) {
            atualizarStatusCarrinho($pdo, $id_carrinho);
            $_SESSION['mensagem_sucesso'] = "Registro de auditoria removido com sucesso.";
        }
        redirect('car_relatorios.php');
    }

    redirect('car_dashboard.php');
}
