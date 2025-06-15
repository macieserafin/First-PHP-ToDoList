<?php
require '../config.php';

// Musisz być zalogowany, aby usunąć konto

if (!isset($_SESSION['user'])) {
    header('Location: ../pages/login.php');
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

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

header('Location: ../pages/home.php');
exit;
?>
