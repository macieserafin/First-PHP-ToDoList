<?php
require_once __DIR__ . '/../config.php';

$error = '';
$success = '';

// Sprawdzanie formularza rejestracji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $year = intval($_POST['year']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Walidacja danych

// Tworzenie folderu użytkownika

$storagePath = __DIR__ . '/../data/' . str_replace(['@', '.'], '_', strtolower($email));
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
    file_put_contents($storagePath . '/tasks.csv', '');
}

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nieprawidłowy format email.';
    } elseif (strlen($password) < 6) {
        $error = 'Hasło musi mieć co najmniej 6 znaków.';
    } elseif ($password !== $confirm) {
        $error = 'Hasła nie zgadzają się.';
    } else {
        // Zapis danych użytkownika
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Sprawdzanie, czy użytkownik istnieje
        $file = fopen(USERS_FILE, 'a+');
        $user_exists = false;

        while (($user = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {

            if ($user[3] === $email) {
                $user_exists = true;
                break;
            }
        }

        if ($user_exists) {
            $error = 'Użytkownik z tym adresem email już istnieje.';
        } else {
            // Dodanie użytkownika do pliku CSV
            $user_data = [$first_name, $last_name, $year, $email, $hashed_password];
            fputcsv($file, $user_data, ",", "\"", "\\");
            $success = 'Rejestracja zakończona sukcesem! Możesz się teraz zalogować.';
        }

        fclose($file);
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Header wczytany za pomocą include -->
<?php include '../includes/auth_header.php'; ?>

<!-- Wyśrodkowany box rejestracji -->
<main class="centered-container">
    <div class="box">
        <h2>Rejestracja</h2>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="name-row">
                <input type="text" name="first_name" placeholder="Imię" required>
                <input type="text" name="last_name" placeholder="Nazwisko" required>
            </div>

            <input type="number" name="year" placeholder="Rok urodzenia (np. 2000)" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Hasło (min. 6 znaków)" required>
            <input type="password" name="confirm" placeholder="Potwierdź hasło" required>
            <button type="submit">Zarejestruj się</button>
        </form>
        <a class="linka" href="login.php">Masz już konto? Zaloguj się</a>
    </div>
</main>

</body>
</html>

