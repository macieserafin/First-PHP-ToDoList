<?php
require_once __DIR__ . '/../config.php';

$error = '';
$success = '';


// Sprawdzanie formularza logowania
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Walidacja emaila
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nieprawidłowy format email.';
    } else {
        // Otwarcie pliku z użytkownikami
        $file = fopen(USERS_FILE, 'r');
        $user_found = false;

        while (($user = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {
            if ($user[3] === $email && password_verify($password, $user[4])) {
                $user_found = true;

                // Na podstawie stałej ADMIN_EMAILS nadajemy rolę admin/user
                $isAdmin = in_array($user[3], ADMIN_EMAILS, true);

                $_SESSION['user'] = [
                    'first_name' => $user[0],
                    'last_name'  => $user[1],
                    'email'      => $user[3],
                    'role'       => $isAdmin ? 'admin' : 'user'
                ];

                fclose($file);
                header('Location: cookie_consent.php');
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

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<!-- HEADER -->
<?php include '../includes/auth_header.php'; ?>

<main class="centered-container">
    <div class="box">
        <h2>Zaloguj się</h2>

        <!-- Komunikaty błędów -->
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Zaloguj</button>
        </form>

        <a class="linka" href="register.php">Nie masz konta? Zarejestruj się</a>
    </div>
</main>
</body>
</html>
