<?php
require_once __DIR__ . '/../../config/database.php';

try {
    // Verificando se a tabela já existe para evitar erro
    $sql = "
        CREATE TABLE IF NOT EXISTS car_gavetas_config (
            id_carrinho INT NOT NULL,
            num_gaveta TINYINT NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            PRIMARY KEY (id_carrinho, num_gaveta),
            KEY idx_carrinho (id_carrinho)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
    ";
    
    $pdo->query($sql);
    echo "Tabela 'car_gavetas_config' criada com sucesso!";
} catch (PDOException $e) {
    echo "Erro na criação da tabela: " . $e->getMessage();
}
