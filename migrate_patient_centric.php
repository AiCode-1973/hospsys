<?php
require_once __DIR__ . '/config/database.php';

try {
    // 1. Create fugulin_pacientes table
    echo "Criando tabela fugulin_pacientes...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fugulin_pacientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            prontuario VARCHAR(50) UNIQUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Add id_paciente to fugulin_classificacoes if it doesn't exist
    echo "Verificando colunas em fugulin_classificacoes...\n";
    $cols = $pdo->query("SHOW COLUMNS FROM fugulin_classificacoes LIKE 'id_paciente'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE fugulin_classificacoes ADD COLUMN id_paciente INT AFTER id_usuario");
        // Don't add constraint yet to avoid issues if data is dirty
    }

    $pdo->beginTransaction();

    // 3. Migrate unique patients
    echo "Migrando pacientes...\n";
    $pacientes_unicos = $pdo->query("SELECT DISTINCT paciente_nome FROM fugulin_classificacoes")->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt_ins = $pdo->prepare("INSERT IGNORE INTO fugulin_pacientes (nome, prontuario) VALUES (?, ?)");
    $stmt_upd = $pdo->prepare("UPDATE fugulin_classificacoes SET id_paciente = ? WHERE paciente_nome = ?");

    foreach ($pacientes_unicos as $nome) {
        if (empty($nome)) continue;
        
        $prontuario = 'REG-' . strtoupper(substr(md5($nome), 0, 6));
        $stmt_ins->execute([$nome, $prontuario]);
        
        $stmt_get = $pdo->prepare("SELECT id FROM fugulin_pacientes WHERE nome = ?");
        $stmt_get->execute([$nome]);
        $id_paciente = $stmt_get->fetchColumn();
        
        if ($id_paciente) {
            $stmt_upd->execute([$id_paciente, $nome]);
        }
    }

    $pdo->commit();
    
    // Now try to add the constraint
    try {
        $pdo->exec("ALTER TABLE fugulin_classificacoes ADD CONSTRAINT fk_fugulin_paciente FOREIGN KEY (id_paciente) REFERENCES fugulin_pacientes(id) ON DELETE CASCADE");
        echo "Constraint adicionada.\n";
    } catch (Exception $e) {
        echo "Aviso: Não foi possível adicionar constraint (talvez já exista ou dados órfãos): " . $e->getMessage() . "\n";
    }

    echo "Migração concluída com sucesso!";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "Erro: " . $e->getMessage();
}
