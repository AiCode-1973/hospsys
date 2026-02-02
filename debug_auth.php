<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Depuração de Autenticação</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>Usuário 'admin' encontrado!</p>";
        echo "<p>Hash no banco: " . $user['senha_hash'] . "</p>";
        
        $senha_teste = 'admin123';
        if (password_verify($senha_teste, $user['senha_hash'])) {
            echo "<p style='color: green;'>Sucesso: A senha 'admin123' coincide com o hash!</p>";
        } else {
            echo "<p style='color: red;'>Erro: A senha 'admin123' NÃO coincide com o hash.</p>";
            
            // Gerar novo hash e atualizar
            $novo_hash = password_hash('admin123', PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE usuario = 'admin'");
            $update->execute([$novo_hash]);
            echo "<p style='color: blue;'>Ação: Hash atualizado para 'admin123'. Tente logar novamente agora.</p>";
        }
    } else {
        echo "<p style='color: red;'>Erro: Usuário 'admin' não encontrado no banco de dados.</p>";
        
        // Tentar inserir novamente
        $novo_hash = password_hash('admin123', PASSWORD_BCRYPT);
        $insert = $pdo->prepare("INSERT INTO usuarios (nome, email, cpf, usuario, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute(['Administrador', 'admin@hospsys.com', '000.000.000-00', 'admin', $novo_hash, 'Administrador']);
        echo "<p style='color: blue;'>Ação: Usuário 'admin' inserido com sucesso com a senha 'admin123'.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>Erro de Banco de Dados: " . $e->getMessage() . "</p>";
}
?>
