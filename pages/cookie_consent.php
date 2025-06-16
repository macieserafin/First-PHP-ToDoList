<?php
session_start();

// 1) Jeżeli nie jesteś zalogowany – wracaj do home.php
if (!isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

// 2) Zbuduj unikalny klucz zgody dla tego konta
$userEmail  = $_SESSION['user']['email'];
$consentKey = 'allow_cookies_' . md5($userEmail);

// 3) Jeżeli już mamy decyzję (true/false) dla tego usera – od razu do dashboardu
if (isset($_COOKIE[$consentKey])) {
    header('Location: dashboard.php');
    exit;
}

// 4) Obsługa formularza POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consent'])) {
    // zapisujemy zgodę (true/false) w ciasteczku na rok
    setcookie($consentKey, $_POST['consent'], time() + (365 * 24 * 60 * 60), "/");

    // jeżeli zgoda to również zliczamy pierwszą wizytę
    if ($_POST['consent'] === 'true') {
        $visitKey = 'visit_count_' . md5($userEmail);

        if (isset($_COOKIE[$visitKey])) {
            $count = (int)$_COOKIE[$visitKey] + 1;
        } else {
            $count = 1;
        }
        setcookie($visitKey, $count, time() + (365 * 24 * 60 * 60), "/");
    }

    // po zapisaniu od razu wracamy do dashboard
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zgoda na ciasteczka</title>
    <link rel="stylesheet" href="../css/todolist.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<div class="main-wrapper">

    <div class="form-container">
        <h2>Czy zgadzasz się na używanie ciasteczek do zliczania liczby wejść?</h2>
        <form method="post" style="display: flex; gap: 10px; margin-top: 1em;">

            <button type="submit" name="consent" value="true" class="button">Tak</button>
            <button type="submit" name="consent" value="false" class="button">Nie</button>
        </form>
    </div>
</div>
</html>
