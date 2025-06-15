<?php
require_once __DIR__ . '/../config.php';  // config.php uruchamia sesję i ładuje stałe/public_config.php

// Zabezpieczenie: dostęp tylko dla administratora
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: home.php');
    exit;
}

// Wczytanie użytkowników z CSV
$users = [];
if (defined('USERS_FILE') && file_exists(USERS_FILE) && ($handle = fopen(USERS_FILE, 'r')) !== false) {
    while (($row = fgetcsv($handle, 0, ',', '"', "\\")) !== false) {
        // [0]=first_name, [1]=last_name, [2]=birth_year, [3]=email, [4]=pass_hash, [5]=role?
        $users[] = $row;
    }
    fclose($handle);
}

// Załaduj widoki
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 900px; margin: 40px auto;">
    <h1>Panel administratora</h1>
    <?php if (empty($users)): ?>
        <p>Brak użytkowników do wyświetlenia.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:20px;">
            <thead>
            <tr>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Rok</th>
                <th>Email</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user[0]) ?></td>
                    <td><?= htmlspecialchars($user[1]) ?></td>
                    <td><?= htmlspecialchars($user[2]) ?></td>
                    <td><?= htmlspecialchars($user[3]) ?></td>
                    <td>
                        <form action="../logic/admin_actions.php" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($user[3]) ?>">
                            <button type="submit" onclick="return confirm('Na pewno usunąć <?= htmlspecialchars($user[3]) ?>?');">Usuń</button>
                        </form>
                        <form action="../logic/admin_actions.php" method="post" style="display:inline; margin-left:10px;">
                            <input type="hidden" name="action" value="reset">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($user[3]) ?>">
                            <input type="password" name="new_password" placeholder="Nowe hasło" required>
                            <button type="submit">Resetuj hasło</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="button-container">
        <button class="btn-back" type="button" onclick="window.location.href='dashboard.php'">
            Wróć
        </button>
    </div>
</main>
