<?php
require_once __DIR__ . '/../../config/database.php';

try {
    $sql = "
        CREATE TABLE IF NOT EXISTS car_gavetas_config (
            id_carrinho INT NOT NULL,
            num_gaveta TINYINT NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            PRIMARY KEY (id_carrinho, num_gaveta),
            FOREIGN KEY (id_carrinho) REFERENCES car_carrinhos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->query($sql);
    echo "Tabela 'car_gavetas_config' criada com sucesso!";
} catch (PDOException $e) {
    echo "Erro na criação da tabela: " . $e->getMessage();
}
