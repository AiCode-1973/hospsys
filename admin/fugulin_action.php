<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            $_SESSION['mensagem_erro'] = "Falha na validação CSRF.";
            redirect('fugulin_novo.php');
        }

        $id_usuario = $_SESSION['user_id'];
        $paciente_nome = cleanInput($_POST['paciente_nome']);
        $paciente_prontuario = cleanInput($_POST['paciente_prontuario']);
        $id_setor = (int)$_POST['setor'];
        $id_leito = (int)$_POST['leito'];
        $respostas_ids = $_POST['pergunta'] ?? []; // [pergunta_id] => opcao_id

        if (empty($paciente_nome) || empty($id_setor) || empty($id_leito) || empty($respostas_ids)) {
            $_SESSION['mensagem_erro'] = "Preencha todos os campos e responda a todas as questões.";
            redirect('fugulin_novo.php');
        }

        try {
            $pdo->beginTransaction();

            // Calcula total e prepara respostas
            $total_pontos = 0;
            $respostas_data = [];

            foreach ($respostas_ids as $p_id => $o_id) {
                // Busca pontos da opção para validação server-side
                $stmt_opt = $pdo->prepare("SELECT pontuacao FROM fugulin_opcoes WHERE id = ?");
                $stmt_opt->execute([$o_id]);
                $pts = $stmt_opt->fetchColumn();
                
                $total_pontos += $pts;
                $respostas_data[] = [
                    'q' => $p_id,
                    'o' => $o_id,
                    'p' => $pts
                ];
            }

            // Define classificação
            $classificacao = "";
            if ($total_pontos >= 12 && $total_pontos <= 17) $classificacao = "Cuidados mínimos (CM)";
            else if ($total_pontos >= 18 && $total_pontos <= 23) $classificacao = "Cuidados intermediários (CI)";
            else if ($total_pontos >= 24 && $total_pontos <= 29) $classificacao = "Alta dependência (AD)";
            else if ($total_pontos >= 30 && $total_pontos <= 34) $classificacao = "Cuidados Semi-Intensivo (CSI)";
            else $classificacao = "Cuidados Intensivos (CI)";

            // Busca ou cria paciente
            $stmt_get_p = $pdo->prepare("SELECT id FROM fugulin_pacientes WHERE nome = ?");
            $stmt_get_p->execute([$paciente_nome]);
            $id_paciente = $stmt_get_p->fetchColumn();

            if (!$id_paciente) {
                if (empty($paciente_prontuario)) {
                    $paciente_prontuario = 'REG-' . strtoupper(substr(md5($paciente_nome), 0, 6));
                }
                $stmt_ins_p = $pdo->prepare("INSERT INTO fugulin_pacientes (nome, prontuario) VALUES (?, ?)");
                $stmt_ins_p->execute([$paciente_nome, $paciente_prontuario]);
                $id_paciente = $pdo->lastInsertId();
            }

            // Salva classificação principal
            $stmt_main = $pdo->prepare("
                INSERT INTO fugulin_classificacoes (id_usuario, id_paciente, id_setor, id_leito, paciente_nome, total_pontos, classificacao)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_main->execute([$id_usuario, $id_paciente, $id_setor, $id_leito, $paciente_nome, $total_pontos, $classificacao]);
            
            $id_classificacao = $pdo->lastInsertId();

            // Salva detalhes das respostas
            $stmt_resp = $pdo->prepare("
                INSERT INTO fugulin_respostas (id_classificacao, id_questao, id_opcao, pontos)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($respostas_data as $r) {
                $stmt_resp->execute([$id_classificacao, $r['q'], $r['o'], $r['p']]);
            }

            $pdo->commit();
            $_SESSION['mensagem_sucesso'] = "Classificação do paciente '$paciente_nome' realizada com sucesso!";
            redirect('fugulin_lista.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['mensagem_erro'] = "Erro ao salvar classificação: " . $e->getMessage();
            redirect('fugulin_novo.php');
        }
    } elseif ($acao === 'editar') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            $_SESSION['mensagem_erro'] = "Falha na validação CSRF.";
            redirect('fugulin_lista.php');
        }

        $id_classificacao = (int)$_POST['id_classificacao'];
        $paciente_nome = cleanInput($_POST['paciente_nome']);
        $paciente_prontuario = cleanInput($_POST['paciente_prontuario']);
        $id_setor = (int)$_POST['setor'];
        $id_leito = (int)$_POST['leito'];
        $respostas_ids = $_POST['pergunta'] ?? [];

        if (empty($id_classificacao) || empty($paciente_nome) || empty($id_setor) || empty($id_leito) || empty($respostas_ids)) {
            $_SESSION['mensagem_erro'] = "Preencha todos os campos e responda a todas as questões.";
            redirect("fugulin_novo.php?id=$id_classificacao");
        }

        try {
            $pdo->beginTransaction();

            // 1. Calcula total e prepara respostas
            $total_pontos = 0;
            $respostas_data = [];

            foreach ($respostas_ids as $p_id => $o_id) {
                $stmt_opt = $pdo->prepare("SELECT pontuacao FROM fugulin_opcoes WHERE id = ?");
                $stmt_opt->execute([$o_id]);
                $pts = $stmt_opt->fetchColumn();
                
                $total_pontos += $pts;
                $respostas_data[] = [
                    'q' => $p_id,
                    'o' => $o_id,
                    'p' => $pts
                ];
            }

            // 2. Define classificação
            $classificacao = "";
            if ($total_pontos >= 12 && $total_pontos <= 17) $classificacao = "Cuidados mínimos (CM)";
            else if ($total_pontos >= 18 && $total_pontos <= 23) $classificacao = "Cuidados intermediários (CI)";
            else if ($total_pontos >= 24 && $total_pontos <= 29) $classificacao = "Alta dependência (AD)";
            else if ($total_pontos >= 30 && $total_pontos <= 34) $classificacao = "Cuidados Semi-Intensivo (CSI)";
            else $classificacao = "Cuidados Intensivos (CI)";

            // Busca ou cria paciente
            $stmt_get_p = $pdo->prepare("SELECT id FROM fugulin_pacientes WHERE nome = ?");
            $stmt_get_p->execute([$paciente_nome]);
            $id_paciente = $stmt_get_p->fetchColumn();

            if (!$id_paciente) {
                if (empty($paciente_prontuario)) {
                    $paciente_prontuario = 'REG-' . strtoupper(substr(md5($paciente_nome), 0, 6));
                }
                $stmt_ins_p = $pdo->prepare("INSERT INTO fugulin_pacientes (nome, prontuario) VALUES (?, ?)");
                $stmt_ins_p->execute([$paciente_nome, $paciente_prontuario]);
                $id_paciente = $pdo->lastInsertId();
            }

            // 3. Salva como uma NOVA classificação (preservando o histórico conforme solicitado)
            $stmt_main = $pdo->prepare("
                INSERT INTO fugulin_classificacoes (id_usuario, id_paciente, id_setor, id_leito, paciente_nome, total_pontos, classificacao)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_main->execute([$_SESSION['user_id'], $id_paciente, $id_setor, $id_leito, $paciente_nome, $total_pontos, $classificacao]);
            
            $id_nova_classificacao = $pdo->lastInsertId();

            // 4. Insere as novas respostas vinculadas ao NOVO ID
            $stmt_resp = $pdo->prepare("
                INSERT INTO fugulin_respostas (id_classificacao, id_questao, id_opcao, pontos)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($respostas_data as $r) {
                $stmt_resp->execute([$id_nova_classificacao, $r['q'], $r['o'], $r['p']]);
            }

            $pdo->commit();
            $_SESSION['mensagem_sucesso'] = "Nova versão da classificação de '$paciente_nome' salva com sucesso (histórico preservado)!";
            redirect('fugulin_lista.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['mensagem_erro'] = "Erro ao atualizar classificação: " . $e->getMessage();
            redirect("fugulin_novo.php?id=$id_classificacao");
        }
    }
}

// Lógica de Alta (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'alta') {
    $id_paciente = (int)$_GET['id'];
    if ($id_paciente) {
        $stmt = $pdo->prepare("UPDATE fugulin_pacientes SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id_paciente]);
        $_SESSION['mensagem_sucesso'] = "Alta realizada com sucesso! O paciente não aparecerá mais na lista ativa.";
    }
    redirect('fugulin_lista.php');
}

// Lógica de Re-admissão (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['acao']) && $_GET['acao'] === 'readmitir') {
    $id_paciente = (int)$_GET['id'];
    if ($id_paciente) {
        $stmt = $pdo->prepare("UPDATE fugulin_pacientes SET ativo = 1 WHERE id = ?");
        $stmt->execute([$id_paciente]);
        $_SESSION['mensagem_sucesso'] = "Paciente re-admitido com sucesso na monitoria ativa!";
    }
    redirect('fugulin_lista.php');
}
?>
