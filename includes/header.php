<?php

// Sprawdź, czy użytkownik jest zalogowany
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $user_name = $user['first_name'] . ' ' . $user['last_name'];
    $initials = strtoupper($user['first_name'][0] . $user['last_name'][0]); // Pobierz inicjały
} else {
    $user_name = 'Gość';
    $initials = '?'; // Domyślne inicjały dla niezalogowanego użytkownika
}
?>



<header>

    <div class="custom-logo">
        <a href="home.php" class="custom-logo-link">ToDoMaster</a>
    </div>

    <div class="title">Dashboard</div>

    <div onclick="toggleMenu()" class="user-info">
        <span class="user-name"><?= $user_name ?></span>
        <div class="avatar"><?= $initials ?></div>

        <!-- Rozwijane menu -->
        <div class="dropdown-menu" id="userDropdown">
            <a href="../logic/logout.php">Wyloguj się</a>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="../pages/admin_panel.php">Panel administratora</a>
            <?php endif; ?>

            <form action="../logic/delete_account.php" method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć konto?');">
                <button type="submit">Usuń konto</button>
            </form>


        </div>
    </div>
</header>


