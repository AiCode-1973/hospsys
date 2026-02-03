<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // Primeiro, atualizamos os registros existentes de 'Diário' para 'Cadastro Inicial'
    // Mas antes precisamos garantir que o ENUM permita o novo valor
    $pdo->query("ALTER TABLE car_checklists MODIFY COLUMN tipo ENUM('Mensal', 'Pós-Uso', 'Diário', 'Cadastro Inicial', 'Outro') DEFAULT 'Mensal'");
    
    // Atualiza os dados
    $pdo->query("UPDATE car_checklists SET tipo = 'Cadastro Inicial' WHERE tipo = 'Diário'");
    
    // Agora removemos o 'Diário' do ENUM
    $pdo->query("ALTER TABLE car_checklists MODIFY COLUMN tipo ENUM('Mensal', 'Pós-Uso', 'Cadastro Inicial', 'Outro') DEFAULT 'Mensal'");

    echo "Tipo de checklist atualizado no banco de dados com sucesso!";
} catch (PDOException $e) {
    echo "Erro na migração: " . $e->getMessage();
}
