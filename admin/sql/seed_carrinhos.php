<?php
require_once __DIR__ . '/../../config/database.php';

// Proteção básica
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'Administrador') {
        die("Acesso negado.");
    }
}

try {
    // 1. Limpa tabelas (apenas para desenvolvimento se necessário, mas vamos apenas inserir)
    
    // 2. Insere Itens Mestres (se não existirem)
    $itens = [
        ['Adrenalina 1mg/ml', 'Ampola de 1ml', 'Medicamento', 'amp'],
        ['Atropina 0,25mg/ml', 'Ampola de 1ml', 'Medicamento', 'amp'],
        ['Amiodarona 50mg/ml', 'Ampola de 3ml', 'Medicamento', 'amp'],
        ['Dopamina 5mg/ml', 'Ampola de 10ml', 'Medicamento', 'amp'],
        ['Lidocaína 2% s/ vaso', 'Frasco-ampola', 'Medicamento', 'fa'],
        ['Seringa 10ml', 'Seringa descartável luer lock', 'Material', 'un'],
        ['Agulha 25x7', 'Agulha descartável', 'Material', 'un'],
        ['Cateter Venoso 20G', 'Jelco / Abocath', 'Material', 'un'],
        ['Laringoscópio Adulto', 'Cabo e 3 lâminas', 'Equipamento', 'un'],
        ['Ambu com Máscara', 'Reanimador manual', 'Equipamento', 'un']
    ];

    $stmt_item = $pdo->prepare("INSERT INTO car_itens_mestres (nome, descricao, tipo, unidade) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nome=nome");
    foreach ($itens as $i) {
        $stmt_item->execute($i);
    }

    // 3. Insere Carrinhos Exemplo (associados a setores existentes)
    $setores = $pdo->query("SELECT id FROM fugulin_setores LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($setores) > 0) {
        $carrinhos = [
            ['Carrinho Emergência A', 'UTI Adulto - Posto 1', $setores[0], 'OK', 'TOKEN_CART_A'],
            ['Carrinho Emergência B', 'Pronto Socorro - Sala Vermelha', $setores[min(1, count($setores)-1)], 'Atenção', 'TOKEN_CART_B'],
            ['Carrinho Emergência C', 'Centro Cirúrgico - Bloco 2', $setores[min(2, count($setores)-1)], 'Crítico', 'TOKEN_CART_C']
        ];

        $stmt_cart = $pdo->prepare("INSERT INTO car_carrinhos (nome, localizacao, id_setor, status, qr_code_token) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nome=nome");
        foreach ($carrinhos as $c) {
            $stmt_cart->execute($c);
        }
    }

    echo "Sucesso: Dados de exemplo inseridos.";
} catch (PDOException $e) {
    echo "Erro ao inserir dados: " . $e->getMessage();
}
