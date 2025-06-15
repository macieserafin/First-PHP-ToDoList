<?php
// Ścieżki bazowe
define('BASE_DIR', __DIR__);                             // katalog projektu
define('CSS_URL', '/PHP-ToDoList/css/');                 // dostosuj do ścieżki projektu
define('USERS_FILE', BASE_DIR . '/data/users.csv');      // pełna ścieżka do CSV

// Sesja
session_start();

// Strony wymagające logowania
$protected_pages = [
    'dashboard.php',
    'delete_account.php',
    'admin_panel.php',
    'edit_task.php',
];
$current_file = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user']) && in_array($current_file, $protected_pages)) {
    header('Location: home.php');
    exit;
}

// Dodatkowa konfiguracja (ADMIN_EMAILS)
require_once __DIR__ . '/public_config.php';