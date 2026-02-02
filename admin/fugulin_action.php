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
        $paciente_nome = cleanInput($_POST['paciente']);
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

            // Salva classificação principal
            $stmt_main = $pdo->prepare("
                INSERT INTO fugulin_classificacoes (id_usuario, id_setor, id_leito, paciente_nome, total_pontos, classificacao)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt_main->execute([$id_usuario, $id_setor, $id_leito, $paciente_nome, $total_pontos, $classificacao]);
            
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
    }
}
?>
