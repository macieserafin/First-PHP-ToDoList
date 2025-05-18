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

    <div class="user-info">
        <span class="user-name" onclick="toggleMenu()"><?= $user_name ?></span>
        <div class="avatar"><?= $initials ?></div>

        <!-- Rozwijane menu -->
        <div class="dropdown-menu" id="userDropdown">
            <a href="../logic/logout.php">Wyloguj się</a>
            <form action="../logic/delete_account.php" method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć konto?');">
                <button type="submit">Usuń konto</button>
            </form>
        </div>
    </div>
</header>

<script>
    // Funkcja do przełączania widoczności menu
    function toggleMenu() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
        }
    }

    // Ukryj menu, jeśli użytkownik kliknie poza elementem
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('userDropdown');
        const userInfo = document.querySelector('.user-info');

        // Jeśli kliknięcie jest poza user-info lub dropdown-menu, ukryj menu
        if (!userInfo.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>
