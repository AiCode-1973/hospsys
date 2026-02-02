-- Fugulin Module Tables

-- 1. Sectors table
CREATE TABLE IF NOT EXISTS fugulin_setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Beds table
CREATE TABLE IF NOT EXISTS fugulin_leitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_setor INT NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_setor) REFERENCES fugulin_setores(id) ON DELETE CASCADE
);

-- 3. Questions table
CREATE TABLE IF NOT EXISTS fugulin_questoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordem INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- 4. Options table
CREATE TABLE IF NOT EXISTS fugulin_opcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_questao INT NOT NULL,
    pontuacao INT NOT NULL,
    descricao TEXT NOT NULL,
    FOREIGN KEY (id_questao) REFERENCES fugulin_questoes(id) ON DELETE CASCADE
);

-- 5. Submissions (Classifications) table
CREATE TABLE IF NOT EXISTS fugulin_classificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_setor INT NOT NULL,
    id_leito INT NOT NULL,
    paciente_nome VARCHAR(255) NOT NULL,
    total_pontos INT NOT NULL,
    classificacao VARCHAR(100) NOT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_setor) REFERENCES fugulin_setores(id),
    FOREIGN KEY (id_leito) REFERENCES fugulin_leitos(id)
);

-- 6. Answers details (optional but good for history)
CREATE TABLE IF NOT EXISTS fugulin_respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_classificacao INT NOT NULL,
    id_questao INT NOT NULL,
    id_opcao INT NOT NULL,
    pontos INT NOT NULL,
    FOREIGN KEY (id_classificacao) REFERENCES fugulin_classificacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_questao) REFERENCES fugulin_questoes(id),
    FOREIGN KEY (id_opcao) REFERENCES fugulin_opcoes(id)
);

-- Initial Data
INSERT IGNORE INTO fugulin_setores (id, nome) VALUES (1, 'UTI'), (2, 'ENFERMARIA'), (3, 'PA');

-- Beds for UTI (Box 01 to 10)
INSERT IGNORE INTO fugulin_leitos (id_setor, descricao) VALUES 
(1, 'Box 01'), (1, 'Box 02'), (1, 'Box 03'), (1, 'Box 04'), (1, 'Box 05'),
(1, 'Box 06'), (1, 'Box 07'), (1, 'Box 08'), (1, 'Box 09'), (1, 'Box 10');

-- Beds for PA (Leito 01, 02, 03)
INSERT IGNORE INTO fugulin_leitos (id_setor, descricao) VALUES 
(3, 'LEITO 01'), (3, 'LEITO 02'), (3, 'LEITO 03');

-- Beds for ENFERMARIA (Quarto 01-17)
INSERT IGNORE INTO fugulin_leitos (id_setor, descricao) VALUES 
(2, 'QUARTO 01 - LEITO 01'), (2, 'QUARTO 01 - LEITO 02'),
(2, 'QUARTO 02 - LEITO 03'), (2, 'QUARTO 02 - LEITO 04'),
(2, 'QUARTO 03 - LEITO 05'), (2, 'QUARTO 03 - LEITO 06'),
(2, 'QUARTO 04 - LEITO 07'),
(2, 'QUARTO 05 - LEITO 08'), (2, 'QUARTO 05 - LEITO 09'), (2, 'QUARTO 05 - LEITO 10'),
(2, 'QUARTO 06 - LEITO 11'), (2, 'QUARTO 06 - LEITO 12'),
(2, 'QUARTO 07 - LEITO 13'), (2, 'QUARTO 07 - LEITO 14'),
(2, 'QUARTO 08 - LEITO 15'), (2, 'QUARTO 08 - LEITO 16'),
(2, 'QUARTO 09 - LEITO 17'), (2, 'QUARTO 09 - LEITO 18'),
(2, 'QUARTO 10 - LEITO 19'), (2, 'QUARTO 10 - LEITO 20'),
(2, 'QUARTO 11 - LEITO 21'), (2, 'QUARTO 11 - LEITO 22'),
(2, 'QUARTO 12 - LEITO 23'), (2, 'QUARTO 12 - LEITO 24'),
(2, 'QUARTO 13 - LEITO 25'), (2, 'QUARTO 13 - LEITO 26'),
(2, 'QUARTO 14 - LEITO 27'), (2, 'QUARTO 14 - LEITO 28'),
(2, 'QUARTO 15 - LEITO 29'), (2, 'QUARTO 15 - LEITO 30'),
(2, 'QUARTO 16 - LEITO 31'), (2, 'QUARTO 16 - LEITO 32'), (2, 'QUARTO 16 - LEITO 33'),
(2, 'QUARTO 17 - LEITO 34'), (2, 'QUARTO 17 - LEITO 35'), (2, 'QUARTO 17 - LEITO 36');

-- Initial Questions
INSERT IGNORE INTO fugulin_questoes (id, ordem, titulo) VALUES 
(1, 1, 'ESTADO MENTAL'),
(2, 2, 'OXIGENAÇÃO'),
(3, 3, 'SINAIS VITAIS'),
(4, 4, 'MOTILIDADE'),
(5, 5, 'DEAMBULAÇÃO'),
(6, 6, 'ALIMENTAÇÃO'),
(7, 7, 'CUIDADO CORPORAL'),
(8, 8, 'ELIMINAÇÃO'),
(9, 9, 'TERAPÊUTICA'),
(10, 10, 'INTEGRIDADE CUTÂNEA-MUCOSA'),
(11, 11, 'CURATIVO'),
(12, 12, 'TEMPO NA TROCA DO CURATIVO');

-- Options for Question 1 (ESTADO MENTAL)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(1, 1, '1 - Orientação no tempo e no espaço'),
(1, 2, '2 - Períodos de desorientação'),
(1, 3, '3 - Periodos de inconsciencia'),
(1, 4, '4 - Inconsciente');

-- Options for Question 2 (OXIGENAÇÃO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(2, 1, '1- Não depende de Oxigenio'),
(2, 2, '2- Uso intermitente de Mascara ou Cat. O²'),
(2, 3, '3- Uso continuo de Mascara ou Cat. O²'),
(2, 4, '4- Ventilação Mecanica');

-- Options for Question 3 (SINAIS VITAIS)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(3, 1, '1- Controle de rotina (8/8h)'),
(3, 2, '2- Controle em intervalos (6/6h)'),
(3, 3, '3- Controle em intervalos de (4/4h)'),
(3, 4, '4- Controle de rotina (2/2h)');

-- Options for Question 4 (MOTILIDADE)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(4, 1, '1- Movimenta todos os segmentos corporais'),
(4, 2, '2- Limitações de movimentos'),
(4, 3, '3- Dificuldade para movimentar segmentos corporais. Mudança de decubito e movimentação passiva auxiliada pela enfermagem'),
(4, 4, '4- Incapaz de movimentar qualquer segmento corporal. Necessita da Equipe de Enf. para mudar de decubito');

-- Options for Question 5 (DEAMBULAÇÃO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(5, 1, '1- Deambula sozinho'),
(5, 2, '2- Necessita de auxilio para deambular'),
(5, 3, '3- Locomoção atraves de cadeiras de rodas'),
(5, 4, '4- Restrito ao leito');

-- Options for Question 6 (ALIMENTAÇÃO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(6, 1, '1- Autosuficiente'),
(6, 2, '2- Oral com auxilio'),
(6, 3, '3- Atraves de Sonda Enteral'),
(6, 4, '4- Através de Cateter Central');

-- Options for Question 7 (CUIDADO CORPORAL)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(7, 1, '1- Autosuficiente'),
(7, 2, '2- Banho no chuveiro e higiene oral auxiliados pela Equipe de Enfermagem'),
(7, 3, '3- Banho no chuveiro e higiene oral pela Equipe de Enfermagem'),
(7, 4, '4- Banho no leito pela Equipe de Enfermagem');

-- Options for Question 8 (ELIMINAÇÃO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(8, 1, '1- Autosuficiente'),
(8, 2, '2- Uso de vasosanitário com auxilio'),
(8, 3, '3- Uso de comadre ou eliminação no leito'),
(8, 4, '4- Evacuação no leito e uso da cateter vesical de demora');

-- Options for Question 9 (TERAPÊUTICA)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(9, 1, '1- I.M ou V.O'),
(9, 2, '2- E.V intermitente'),
(9, 3, '3- E.V continua ou atraves de cateter'),
(9, 4, '4- Uso de drogas vasoativas (B.Infusão)');

-- Options for Question 10 (INTEGRIDADE CUTÂNEA-MUCOSA)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(10, 1, '1- Pele Integra'),
(10, 2, '2- Alteração da cor da pele e /ou Comp. Derme'),
(10, 3, '3- Solução de continuidade envolve Subcutâneo e Músculo. Ostomias'),
(10, 4, '4- Detruição atinge Musculo, Tendões');

-- Options for Question 11 (CURATIVO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(11, 1, '1- Sem curativo'),
(11, 2, '2- Troca de Curativo 1 x Dia'),
(11, 3, '3- Troca de Curativo 2 x Dia'),
(11, 4, '4- Troca de Curativo 3 x Dia');

-- Options for Question 12 (TEMPO NA TROCA DO CURATIVO)
INSERT IGNORE INTO fugulin_opcoes (id_questao, pontuacao, descricao) VALUES 
(12, 1, '1- Sem curativo'),
(12, 2, '2- Tempo de Troca entre 5 a 15 min.'),
(12, 3, '3- Tempo de Troca entre 15 a 30 min.'),
(12, 4, '4- Tempo de Troca > 30 min.');

-- Register Module
INSERT IGNORE INTO modulos (nome_modulo, descricao, rota, icone) VALUES 
('Classificação Fugulin', 'Sistema de classificação de dependência de enfermagem', 'admin/fugulin_novo.php', 'fas fa-clipboard-list');
