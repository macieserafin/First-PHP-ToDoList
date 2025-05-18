<?php
require 'config.php';

// Musisz być zalogowany, aby usunąć konto

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$email_to_delete = $_SESSION['user']['email'];
$users = [];
$file = fopen(USERS_FILE, 'r');

// Odczytanie użytkowników z pliku bez usuwanego konta
while (($user = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {

    if ($user[3] !== $email_to_delete) {
        $users[] = $user;
    }
}
fclose($file);

// Nadpisanie pliku bez usuniętego użytkownika
$file = fopen(USERS_FILE, 'w');
foreach ($users as $user) {
    fputcsv($file, $user, ",", "\"", "\\");

}
fclose($file);

session_destroy();
header('Location: register.php');
exit;
?>
