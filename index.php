<?php
/**
 * Redirecionamento inicial do sistema
 */
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(url('admin/home.php'));
} else {
    redirect(url('auth/login.php'));
}
exit;
