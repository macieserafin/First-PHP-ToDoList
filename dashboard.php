<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
</head>
<body>
<h1>Witaj, <?= htmlspecialchars($_SESSION['user']['first_name']); ?>!</h1>
<p>Jesteś zalogowany jako: <?= htmlspecialchars($_SESSION['user']['email']); ?></p>
<a href="index.php">ToDoList</a>
<a href="logout.php">Wyloguj</a>
<a href="delete_account.php">Usuń konto</a>
</body>
</html>
