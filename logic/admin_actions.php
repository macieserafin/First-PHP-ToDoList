<?php
require_once __DIR__ . '/../config.php';  // uruchamia sesję i ładuje public_config.php

// Tylko admin
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../pages/home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin_panel.php');
    exit;
}

$action = $_POST['action'] ?? '';
$email  = $_POST['email']  ?? '';
$email  = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';

if (!$email) {
    $_SESSION['error'] = 'Nieprawidłowy adres e-mail.';
    header('Location: ../pages/admin_panel.php');
    exit;
}

$usersFile = USERS_FILE;
if (!file_exists($usersFile)) {
    $_SESSION['error'] = 'Plik użytkowników nie istnieje.';
    header('Location: ../pages/admin_panel.php');
    exit;
}

// Wczytaj wszystkie wiersze
$allUsers = [];
if (($h = fopen($usersFile, 'r')) !== false) {
    while (($row = fgetcsv($h, 0, ',', '"', "\\")) !== false) {
        $allUsers[] = $row;
    }
    fclose($h);
}

$updatedUsers = [];
$successMsg   = '';

switch ($action) {
    case 'delete':
        foreach ($allUsers as $row) {
            if (($row[3] ?? '') === $email) {
                continue; // pomijamy usuwany
            }
            $updatedUsers[] = $row;
        }
        $successMsg = 'Użytkownik ' . htmlspecialchars($email) . ' został usunięty.';
        break;

    case 'reset':
        $newPass = $_POST['new_password'] ?? '';
        if (strlen($newPass) < 6) {
            $_SESSION['error'] = 'Nowe hasło musi mieć co najmniej 6 znaków.';
            header('Location: ../pages/admin_panel.php');
            exit;
        }
        foreach ($allUsers as $row) {
            if (($row[3] ?? '') === $email) {
                $row[4] = password_hash($newPass, PASSWORD_BCRYPT);
            }
            $updatedUsers[] = $row;
        }
        $successMsg = 'Hasło użytkownika ' . htmlspecialchars($email) . ' zostało zresetowane.';
        break;

    default:
        $_SESSION['error'] = 'Nieznana akcja.';
        header('Location: ../pages/admin_panel.php');
        exit;
}

// Zapisz z powrotem z explicit escape parameter
if (($h = fopen($usersFile, 'w')) !== false) {
    foreach ($updatedUsers as $row) {
        // fputcsv($handle, $fields, $delimiter, $enclosure, $escape)
        fputcsv($h, $row, ',', '"', "\\");
    }
    fclose($h);
}

$_SESSION['success'] = $successMsg;
header('Location: ../pages/admin_panel.php');
exit;
