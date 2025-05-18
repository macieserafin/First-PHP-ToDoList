<?php
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Walidacja danych
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nieprawidłowy format email.';
    } else {
        $file = fopen(USERS_FILE, 'r');
        $user_found = false;

        while (($user = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {

            if ($user[3] === $email && password_verify($password, $user[4])) {
                // Zalogowano
                $user_found = true;
                $_SESSION['user'] = [
                    'first_name' => $user[0],
                    'last_name' => $user[1],
                    'email' => $user[3],
                ];
                fclose($file);
                header('Location: dashboard.php');
                exit;
            }
        }

        fclose($file);

        if (!$user_found) {
            $error = 'Nieprawidłowy email lub hasło.';
        }
    }
}
?>


<!DOCTYPE HTML>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <title>Logowanie</title>
        <link rel="stylesheet" href="css/auth.css">
    </head>
    <body>
        <div class="box">
            <h2>Zaloguj się</h2>

                <?php if ($success): ?>
                    <div class="success"><?= $success ?></div>
                <?php elseif ($error): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

            <form method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Hasło" required>
                <button type="submit">Zaloguj</button>
            </form>
            <a href="register.php">Nie masz konta? Zarejestruj się</a>
        </div>

    </body>
</html>