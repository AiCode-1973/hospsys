<?php
error_reporting(0);
try {
    $pdo = new PDO('mysql:host=186.209.113.107;dbname=dema5738_hospsys', 'dema5738_hospsys', 'Dema@1973');
    
    // Add categoria column
    $pdo->exec("ALTER TABLE modulos ADD COLUMN categoria VARCHAR(50) DEFAULT NULL");
    
    // Update existing Fugulin modules
    $pdo->exec("UPDATE modulos SET categoria = 'Fugulin' WHERE nome_modulo LIKE '%Fugulin%'");
    
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
