-- Tabelas do Módulo de Carrinho de Parada/Emergência

-- 1. Cadastro de Carrinhos
CREATE TABLE IF NOT EXISTS car_carrinhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    localizacao VARCHAR(255),
    id_setor INT,
    status ENUM('OK', 'Atenção', 'Crítico') DEFAULT 'OK',
    qr_code_token VARCHAR(100) UNIQUE,
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_setor) REFERENCES fugulin_setores(id) ON DELETE SET NULL
);

-- 2. Catálogo Global de Itens (Medicamentos, Materiais, Equipamentos)
CREATE TABLE IF NOT EXISTS car_itens_mestres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo ENUM('Medicamento', 'Material', 'Equipamento', 'Outro') DEFAULT 'Material',
    unidade VARCHAR(50) DEFAULT 'un',
    ativo TINYINT(1) DEFAULT 1
);

-- 3. Composição Ideal (O que deve ter em cada carrinho)
CREATE TABLE IF NOT EXISTS car_composicao_ideal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carrinho INT NOT NULL,
    id_item INT NOT NULL,
    quantidade_ideal INT NOT NULL,
    quantidade_minima INT NOT NULL,
    FOREIGN KEY (id_carrinho) REFERENCES car_carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES car_itens_mestres(id) ON DELETE CASCADE
);

-- 4. Estoque Atual (O que realmente está no carrinho)
CREATE TABLE IF NOT EXISTS car_estoque_atual (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carrinho INT NOT NULL,
    id_item INT NOT NULL,
    lote VARCHAR(100),
    data_validade DATE,
    quantidade_atual INT DEFAULT 0,
    FOREIGN KEY (id_carrinho) REFERENCES car_carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES car_itens_mestres(id) ON DELETE CASCADE
);

-- 5. Registro de Checklists (Conferências)
CREATE TABLE IF NOT EXISTS car_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carrinho INT NOT NULL,
    id_usuario INT NOT NULL,
    data_conferencia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('Mensal', 'Pós-Uso', 'Diário', 'Outro') DEFAULT 'Mensal',
    observacoes TEXT,
    status_final ENUM('Conforme', 'Não Conforme') DEFAULT 'Conforme',
    FOREIGN KEY (id_carrinho) REFERENCES car_carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- 6. Itens do Checklist
CREATE TABLE IF NOT EXISTS car_checklist_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_checklist INT NOT NULL,
    id_item INT NOT NULL,
    conferido TINYINT(1) DEFAULT 0,
    quantidade_encontrada INT,
    validade_conferida DATE,
    FOREIGN KEY (id_checklist) REFERENCES car_checklists(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES car_itens_mestres(id) ON DELETE CASCADE
);

-- 7. Histórico de Movimentações
CREATE TABLE IF NOT EXISTS car_movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carrinho INT NOT NULL,
    id_item INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_movimentacao ENUM('Reposição', 'Consumo', 'Ajuste', 'Vencimento') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacao VARCHAR(255),
    FOREIGN KEY (id_carrinho) REFERENCES car_carrinhos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES car_itens_mestres(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Registro do Módulo na tabela central de módulos
INSERT INTO modulos (nome_modulo, rota, icone, descricao, categoria) 
VALUES ('Carrinho de Emergência', 'admin/car_dashboard.php', 'fas fa-ambulance', 'Gestão e checklist de carrinhos de parada', 'Suporte Assistencial')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao), categoria = VALUES(categoria);
