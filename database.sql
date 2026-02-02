-- Database schema for Authentication and Authorization System

-- Users table
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('Administrador', 'Gestor', 'Usuário Padrão', 'Visualizador') NOT NULL DEFAULT 'Usuário Padrão',
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Modules table
CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_modulo VARCHAR(50) NOT NULL,
    descricao TEXT,
    rota VARCHAR(255) NOT NULL,
    icone VARCHAR(50) DEFAULT 'fas fa-box'
);

-- Permissions table
CREATE TABLE IF NOT EXISTS permissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_modulo INT NOT NULL,
    pode_visualizar TINYINT(1) DEFAULT 0,
    pode_criar TINYINT(1) DEFAULT 0,
    pode_editar TINYINT(1) DEFAULT 0,
    pode_excluir TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_modulo) REFERENCES modulos(id) ON DELETE CASCADE
);

-- Access logs table
CREATE TABLE IF NOT EXISTS logs_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    usuario_tentativa VARCHAR(50),
    ip_address VARCHAR(45),
    sucesso TINYINT(1),
    mensagem TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default modules
INSERT INTO modulos (nome_modulo, descricao, rota, icone) VALUES 
('Dashboard', 'Painel principal com estatísticas', 'admin/dashboard.php', 'fas fa-tachometer-alt'),
('Usuários', 'Gerenciamento de usuários do sistema', 'admin/usuarios.php', 'fas fa-users'),
('Permissões', 'Gerenciamento de permissões de acesso', 'admin/permissoes.php', 'fas fa-user-shield');

-- Insert initial admin user (password: admin123)
-- Hash generated via bcrypt
INSERT INTO usuarios (nome, email, cpf, usuario, senha_hash, nivel_acesso) VALUES 
('Administrador', 'admin@hospsys.com', '000.000.000-00', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

-- Assign all permissions to admin for the default modules
INSERT INTO permissoes (id_usuario, id_modulo, pode_visualizar, pode_criar, pode_editar, pode_excluir)
SELECT 1, id, 1, 1, 1, 1 FROM modulos;
