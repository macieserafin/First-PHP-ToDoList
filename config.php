<?php
// Ścieżki bazowe
define('BASE_DIR', __DIR__); // Ścieżka katalogu na serwerze
define('CSS_URL', '/css/'); // Ścieżka URL widoczna dla przeglądarki
define('USERS_FILE', BASE_DIR . '/data/users.csv'); // Absolutna ścieżka do pliku CSV

// Ustawienia sesji
session_start();

// Sprawdzanie czy obecny plik jest chroniony
$protected_pages = ['dashboard.php', 'dashboard.php', 'delete_account.php']; // Lista plików chronionych dostępem
$current_file = basename($_SERVER['PHP_SELF']); // Pobieramy nazwę bieżącego pliku

if (!isset($_SESSION['user']) && in_array($current_file, $protected_pages)) {
    // Użytkownik nie jest zalogowany i próbuje wejść na chroniony plik
    header('Location: home.php');
    exit;
}
