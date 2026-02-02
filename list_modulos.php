<?php
error_reporting(0);
try {
    $pdo = new PDO('mysql:host=186.209.113.107;dbname=dema5738_hospsys', 'dema5738_hospsys', 'Dema@1973');
    $stmt = $pdo->query("SELECT * FROM modulos");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
