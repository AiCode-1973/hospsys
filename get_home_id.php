<?php
error_reporting(0);
try {
    $pdo = new PDO('mysql:host=186.209.113.107;dbname=dema5738_hospsys', 'dema5738_hospsys', 'Dema@1973');
    $stmt = $pdo->query("SELECT id FROM modulos WHERE rota = 'admin/home.php'");
    echo "ID:" . $stmt->fetchColumn();
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
