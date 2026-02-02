<?php
/**
 * Redirecionamento inicial do sistema
 */
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
} else {
    header("Location: auth/login.php");
}
exit;
