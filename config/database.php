<?php
/**
 * Configuração de conexão com o banco de dados via PDO
 */

$host = '186.209.113.107';
$dbname = 'dema5738_hospsys'; // Corrigido de hosys para hospsys
$username = 'dema5738_hospsys';
$password = 'Dema@1973';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configura o fetch mode padrão para objetos, facilitando o acesso às propriedades
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Desabilita emulação de prepared statements por segurança (SQL Injection)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Em produção, não exiba a mensagem de erro detalhada
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>
