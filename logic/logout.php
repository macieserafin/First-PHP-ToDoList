<?php
// Uruchom sesję
session_start();

// Usuń wszystkie dane sesji
$_SESSION = [];

// Zniszcz sesję
session_destroy();

// Usuń plik cookie sesji
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Przekieruj użytkownika na stronę główną
header('Location: ../pages/home.php');
exit;
?>
