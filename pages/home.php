
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ToDoMaster</title>
  <link rel="stylesheet" href="../css/home.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php
include '../includes/auth_header.php';
?>

<section class="hero">
  <div class="hero-text">
    <h1>W końcu ogarniesz <span>wszystkie zadania</span></h1>
    <p>Zaawansowany menedżer zadań z trwałym przechowywaniem, załącznikami, filtrowaniem i obsługą CSV.</p>
    <div class="hero-buttons">
      <a href="register.php" class="register big">Rozpocznij za darmo</a>
      <a href="login.php" class="login-link">Mam już konto</a>
    </div>
  </div>
  <div class="hero-image">
    <img src="https://www.teamly.com/blog/wp-content/uploads/2021/12/Master-Task-List.png" alt="Zrzut ekranu">
  </div>
</section>

<section class="features" id="features">
  <h2>Dlaczego ToDoMaster?</h2>
  <div class="features-grid">
    <div class="feature">
      <h3>Sortowanie i tagi</h3>
      <p>Porządkuj zadania według tagów i priorytetów.</p>
    </div>
    <div class="feature">
      <h3>Wyszukiwarka i filtrowanie</h3>
      <p>Szybko odnajduj to, czego potrzebujesz – wśród setek zadań.</p>
    </div>
    <div class="feature">
      <h3>CSV i trwałość</h3>
      <p>Trwałe przechowywanie zadań z eksportem i importem do CSV.</p>
    </div>
    <div class="feature">
      <h3>Załączniki</h3>
      <p>Dodawaj pliki i notatki do każdego zadania.</p>
    </div>
  </div>
</section>
</body>
</html>
