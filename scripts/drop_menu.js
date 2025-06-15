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