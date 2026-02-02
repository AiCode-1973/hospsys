<?php
require_once __DIR__ . '/../../config/database.php';

// Proteção CLI
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_nivel']) || $_SESSION['user_nivel'] !== 'Administrador') {
        die("Acesso negado.");
    }
}

try {
    // 1. Lista todos os módulos para conferência
    $modulos = $pdo->query("SELECT * FROM modulos ORDER BY nome_modulo ASC")->fetchAll();
    echo "Lista de Módulos:\n";
    foreach ($modulos as $m) {
        echo "ID: {$m['id']} | Nome: {$m['nome_modulo']} | Rota: {$m['rota']}\n";
    }
    echo "\n";

    // 2. Procura por duplicados do "Carrinho de Emergência"
    $duplicates = $pdo->query("SELECT id FROM modulos WHERE nome_modulo LIKE '%Carrinho de Emergência%' OR rota LIKE '%car_dashboard.php%'")->fetchAll(PDO::FETCH_COLUMN);

    if (count($duplicates) > 1) {
        echo "Foram encontrados " . count($duplicates) . " registros. Mantendo apenas o primeiro (ID: {$duplicates[0]})\n";
        
        // Remove os outros
        array_shift($duplicates); // Remove o primeiro da lista para manter
        $ids_para_remover = implode(',', $duplicates);
        
        // Antes de remover, precisamos remover as permissões associadas a esses IDs para evitar erro de constraint se houver, 
        // ou deixar o ON DELETE CASCADE (se existir) agir. No nosso setup_carrinhos não definimos FK para permissoes.
        $pdo->exec("DELETE FROM permissoes WHERE id_modulo IN ($ids_para_remover)");
        $pdo->exec("DELETE FROM modulos WHERE id IN ($ids_para_remover)");
        
        echo "Sucesso: Duplicados removidos.\n";
    } else {
        echo "Nenhuma duplicata encontrada.\n";
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
