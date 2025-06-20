<?php
require_once '../config.php';
require '../functions/helper_functions.php';
require_once '../classes/CsvHandler.php';
require_once '../classes/TaskManager.php';
require_once '../logic/task_processor.php';

if (!isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$email      = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$consentKey = 'allow_cookies_' . md5($email);
$visitKey   = 'visit_count_' . md5($email);

$cookieInfo = '';
if (isset($_COOKIE[$consentKey]) && $_COOKIE[$consentKey] === 'true') {
    // inkrementuj liczbę wejść przy każdym załadowaniu dashboardu
    $visitCount = isset($_COOKIE[$visitKey])
        ? (int)$_COOKIE[$visitKey] + 1
        : 1;
    setcookie($visitKey, $visitCount, time() + (365 * 24 * 60 * 60), "/");

    $cookieInfo = "🔢 Ilość wejść na stronę: <strong>$visitCount</strong>";
}
elseif (isset($_COOKIE[$consentKey]) && $_COOKIE[$consentKey] === 'false') {
    $cookieInfo = "❌ Nie zezwoliłeś na ciasteczka.";
}

$userEmail = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$csvHandler = new CsvHandler();
$taskManager = new TaskManager($csvHandler, $userEmail);
$tasks = $taskManager->getTasks();
$unfinishedTasks = array_filter($tasks, fn($t) => $t['status'] !== 'Zakończone');
$doneTasks       = array_filter($tasks, fn($t) => $t['status']   === 'Zakończone');
$_SESSION['tasks'] = $tasks;


$email = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$name = explode('@', $email)[0];


include '../includes/head.php';
include '../includes/header.php';
?>

<?php
$unfinished = array_filter($tasks, fn($t) => $t['status'] !== 'Zakończone');

$quotes = [
    "Każde zadanie to krok bliżej celu 🚀",
    "Nie odkładaj, zrób to teraz! 🔥",
    "Masz to pod kontrolą 💪",
    "Skup się na tym, co ważne 🧠",
    "Twój dzień to Twoje dzieło ✨",
    "Nawet najmniejszy krok to postęp ⏳",
    "Nie musisz być idealny – po prostu działaj 🧩",
];
$motivation = $quotes[array_rand($quotes)];

$total = count($tasks);
$done = count(array_filter($tasks, fn($t) => $t['status'] === 'Zakończone'));
$progress = $total > 0 ? round(($done / $total) * 100) : 0;


$today = new DateTime();
$tomorrow = (new DateTime())->modify('+1 day');

$tasksDueSoon = array_filter($unfinishedTasks, function ($task) use ($today, $tomorrow) {
    if (empty($task['date'])) return false;
    $taskDate = DateTime::createFromFormat('Y-m-d', $task['date']);
    return $taskDate >= $today && $taskDate <= $tomorrow;
});

$tasksDueSoonCount = count($tasksDueSoon);

$upcomingTasks = $taskManager->getUpcomingTasks(3); // pokaż zadania na dziś i 2 kolejne dni


?>



<body>

<div class="main-wrapper">

    <div class="left-panel">

        <div class="form-container">
            <h2>Dodaj nowe zadanie</h2>

            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>



            <form method="POST" id="taskForm" enctype="multipart/form-data">
                <div class="line">
                    <label>Tytuł zadania:
                        <input name="title" required>
                    </label>
                    <label>Kategoria:
                        <select name="category" required>
                            <option value="">Wybierz kategorię</option>
                            <option>Domowe</option>
                            <option>Praca</option>
                            <option>Nauka</option>
                            <option>Hobby</option>
                            <option>Inne</option>
                        </select>
                    </label>
                </div>

                <label>Opis zadania:
                    <textarea name="description"></textarea>
                </label>

                <div class="line">
                    <label>Priorytet:
                        <select name="priority" required>
                            <option value="">Wybierz priorytet</option>
                            <option>Niski</option>
                            <option>Średni</option>
                            <option>Wysoki</option>
                        </select>
                    </label>
                    <label>Status:
                        <select name="status">
                            <option>Do zrobienia</option>
                            <option>W trakcie</option>
                            <option>Zakończone</option>
                        </select>
                    </label>
                    <label>Data wykonania:
                        <input type="date" name="date" required>
                    </label>
                </div>

                <div class="line">
                    <label>Szacowany czas (minuty):
                        <input type="number" name="time">
                    </label>
                    <label>Lokalizacja:
                        <input name="location">
                    </label>
                    <label>Osoba przypisana:
                        <input name="assigned">
                    </label>
                    <label>Tagi (oddzielone spacją):
                        <input name="tags">
                    </label>
                </div>

                <div class="zasoby">
                    <label>Potrzebne zasoby:</label>
                    <label><input type="checkbox" name="resources[]" value="Komputer"> Komputer</label>
                    <label><input type="checkbox" name="resources[]" value="Internet"> Internet</label>
                    <label><input type="checkbox" name="resources[]" value="Telefon"> Telefon</label>
                    <label><input type="checkbox" name="resources[]" value="Samochód"> Samochód</label>
                    <label><input type="checkbox" name="resources[]" value="Książka"> Książka</label>
                    <label><input type="checkbox" name="resources[]" value="Narzędzia"> Narzędzia</label>
                    <label><input type="checkbox" name="resources[]" value="Dokumenty"> Dokumenty</label>
                    <label><input type="checkbox" name="resources[]" value="Inne"> Inne</label>
                </div>

                <div class="section attachment">
                    <div class="line">
                        <label>Dodaj załączniki:</label>
                        <input type="file" name="attachments[]" multiple>
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">Możesz dodać kilka plików jednocześnie.</p>
                    </div>

                </div>


                <button class="button-left" type="submit" name="add_task">Dodaj zadanie</button>

            </form>
        </div>

        <div class="form-container">
            <div class="import">
                <form method="post" enctype="multipart/form-data">
                    <label><strong>Importuj CSV:</strong>
                        <div class="imnport-cl">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <button type="submit" name="import_csv">Importuj zadania</button>
                        </div>
                    </label>
                </form>
            </div>
        </div>

        <div class="notifications-container">
            <h2>Powiadomienia:</h2>
            <?php if (empty($upcomingTasks)): ?>
                <p>Brak nadchodzących zadań 🎉</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($upcomingTasks as $task): ?>
                        <?php
                        $due = DateTime::createFromFormat('Y-m-d', $task['date']);
                        $today = new DateTime();
                        $diff = $due ? $today->diff($due)->days : '?';
                        ?>
                        <li>
                            🔔 <strong><?= htmlspecialchars($task['title']) ?></strong>
                            — za <?= $diff ?> dni (<em><?= htmlspecialchars($task['date']) ?></em>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>



    </div>


    <div class="right-panel">

        <div class="welcome-container">
            <h2>Witaj, <?php echo htmlspecialchars($name); ?> 👋</h2>
            <p style="margin: 8px 0;">
                🗓️ Dziś: <strong><?php echo date('d.m.Y'); ?></strong><br>
                📋 Wszystkich zadań: <strong><?php echo count($tasks); ?></strong><br>
                ⏳ Do zrobienia: <strong style="color: #e67e22;"><?php echo count($unfinished); ?></strong>
            </p>

            <p style="margin-top: 10px; color: #444; font-size: 15px;">
                <?= $cookieInfo ?>
            </p>

            <p style="margin-top: 10px; font-style: italic; color: #555;">
                💡 <span><?php echo $motivation; ?></span>
            </p>

            <?php if ($tasksDueSoonCount > 0): ?>
                <p style="margin-top: 10px; font-weight: bold;">
                    ⏱️ Masz <strong><?php echo $tasksDueSoonCount; ?></strong> zadań do zrobienia do jutra!
                </p>
            <?php else: ?>
                <p style="margin-top: 10px; color: green;">
                    ✅ Nie masz żadnych pilnych zadań do jutra!
                </p>
            <?php endif; ?>

            <div class="progress-bar">
                <div class="progress" style="width: <?= $progress ?>%;"><?= $progress ?>%</div>
            </div>
        </div>


        <div class="tasks-container">


            <div class="filtry">



                <div class="sort">
                    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                        <label>
                            Priorytet:
                            <select name="priority" class="filter-form-content">
                                <option value="">Wszystkie</option>
                                <option value="Niski" <?= isset($_GET['priority']) && $_GET['priority'] == 'Niski' ? 'selected' : '' ?>>Niski</option>
                                <option value="Średni" <?= isset($_GET['priority']) && $_GET['priority'] == 'Średni' ? 'selected' : '' ?>>Średni</option>
                                <option value="Wysoki" <?= isset($_GET['priority']) && $_GET['priority'] == 'Wysoki' ? 'selected' : '' ?>>Wysoki</option>
                            </select>
                        </label>

                        <label>
                            Status:
                            <select name="status" class="filter-form-content">
                                <option value="">Wszystkie</option>
                                <option value="Do zrobienia" <?= isset($_GET['status']) && $_GET['status'] == 'Do zrobienia' ? 'selected' : '' ?>>Do zrobienia</option>
                                <option value="W trakcie" <?= isset($_GET['status']) && $_GET['status'] == 'W trakcie' ? 'selected' : '' ?>>W trakcie</option>
                                <option value="Zakończone" <?= isset($_GET['status']) && $_GET['status'] == 'Zakończone' ? 'selected' : '' ?>>Zakończone</option>
                            </select>
                        </label>

                        <button type="submit">Filtruj</button>
                    </form>
                </div>


                <form class="wyszukaj" method="GET" style="margin-bottom: 20px;">
                    <input class="wyszukaj" placeholder="Wyszukaj" type="text" id="query" name="query"
                           value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                           style="width: 60%; padding: 8px; margin-left: 10px;">
                    <button type="submit">Szukaj</button>
                </form>



                <p class="sort">Sortuj według:
                    <a href="?sort=title">Tytułu</a> |
                    <a href="?sort=priority">Priorytetu</a> |
                    <a href="?sort=date">Daty</a> |
                    <a href="?sort=category">Kategorii</a>
                </p>

            </div>


            <div class="task-container">
                <?php $tasks = isset($tasks) ? $tasks : []; foreach ($unfinishedTasks as $key => $task): ?>
                    <div class="task-card">
                        <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                            <button type="submit" name="delete_task" value="<?php echo $key; ?>" class="delete-button">&times;</button>
                        </form>

                        <form method="GET" action="edit_task.php">
                            <input type="hidden" name="id" value="<?php echo $key; ?>">
                            <button type="submit" class="edit-button">=</button>
                        </form>

                        <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                            <button type="submit"
                                    name="save_csv"
                                    value="<?= $key; ?>"
                                    class="save-button">+</button>
                        </form>

                        <form method="POST" onsubmit="return confirm('Oznaczyć to zadanie jako zakończone?');">
                            <button type="submit" name="mark_done" value="<?= $key; ?>" class="complete-button">✓</button>
                        </form>

                        <?php
                        $taskDate = $task['date'] ?? null;
                        $taskTimeMinutes = isset($task['time']) ? (int)$task['time'] : 0;

                        $now = new DateTime();
                        $deadline = $taskDate ? new DateTime($taskDate) : null;

                        if ($deadline) {
                            // Dodaj czas w minutach, jeśli jest podany
                            if ($taskTimeMinutes > 0) {
                                $deadline->modify("+{$taskTimeMinutes} minutes");
                            }

                            $diff = $now->diff($deadline);
                            $remaining = '';

                            if ($now > $deadline) {
                                $remaining = '⏰ <span style="color:red;">Po terminie!</span>';
                            } else {
                                $days = $diff->days;
                                $hours = $diff->h;
                                $minutes = $diff->i;
                                $remaining = "⏳ Zostało: ";
                                if ($days > 0) $remaining .= "$days dni ";
                                if ($hours > 0) $remaining .= "$hours godz. ";
                                if ($minutes > 0 && $days === 0) $remaining .= "$minutes min.";
                            }

                            echo "<p style='font-size:13px; margin-bottom:8px; color:#444;'>$remaining</p>";
                        }
                        ?>

                        <h3><?php echo $task['title']; ?></h3>
                        <div class="badge"><?php echo $task['category']; ?></div>
                        <p><?php echo formatTaskDescription($task['description']); ?></p>

                        <div class="section">
                            <p><strong>Data:</strong> <?php echo $task['date']; ?></p>
                            <p><strong>Czas:</strong> <?php echo $task['time']; ?> minut</p>
                            <p><strong>Miejsce:</strong> <?php echo $task['location']; ?></p>
                            <p><strong>Przypisane do:</strong> <?php echo $task['assigned']; ?></p>
                        </div>

                        <div class="section">
                            <strong>Potrzebne zasoby:</strong>
                            <div class="resource-list">
                                <?php foreach ($task['resources'] as $resource): ?>
                                    <span><?php echo $resource; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if (!empty($task['tags'])): ?>
                            <div class="section">
                                <strong>Tagi:</strong>
                                <div class="resource-list">
                                    <?php foreach ($task['tags'] as $tag): ?>
                                        <span><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="footer">
                            <div class="priority"><?php echo $task['priority']; ?></div>
                            <div class="status"><?php echo $task['status']; ?></div>
                        </div>

                        <div class="section">
                            <?php if (!empty($task['attachments'])): ?>
                                <div class="section attachments-section">
                                    <strong>Załączniki:</strong>
                                    <ul class="attachments-list">
                                        <?php foreach ($task['attachments'] as $attachment): ?>
                                            <li>
                                                <a href="<?php echo htmlspecialchars($attachment); ?>" target="_blank">
                                                    <?php echo htmlspecialchars(basename($attachment)); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h1 style="margin:40px 0 20px; text-align:center; color:#2c3f50;">
                Historia:
            </h1>

            <div class="task-container">
                    <?php if (empty($doneTasks)): ?>
                        <p style="margin-top:100px; ">Historia jest pusta</p>
                    <?php else: ?>
                        <?php foreach ($doneTasks as $key => $task): ?>
                            <div class="task-card done">
                                <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                                    <button type="submit" name="delete_task" value="<?php echo $key; ?>" class="delete-button">&times;</button>
                                </form>

                                <form method="GET" action="edit_task.php">
                                    <input type="hidden" name="id" value="<?php echo $key; ?>">
                                    <button type="submit" class="edit-button">=</button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                                    <button type="submit"
                                            name="save_csv"
                                            value="<?= $key; ?>"
                                            class="save-button">+</button>
                                </form>


                                <h3><?php echo $task['title']; ?></h3>
                                <div class="badge"><?php echo $task['category']; ?></div>
                                <p><?php echo formatTaskDescription($task['description']); ?></p>

                                <div class="section">
                                    <p><strong>Data:</strong> <?php echo $task['date']; ?></p>
                                    <p><strong>Czas:</strong> <?php echo $task['time']; ?> minut</p>
                                    <p><strong>Miejsce:</strong> <?php echo $task['location']; ?></p>
                                    <p><strong>Przypisane do:</strong> <?php echo $task['assigned']; ?></p>
                                </div>

                                <div class="section">
                                    <strong>Potrzebne zasoby:</strong>
                                    <div class="resource-list">
                                        <?php foreach ($task['resources'] as $resource): ?>
                                            <span><?php echo $resource; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <?php if (!empty($task['tags'])): ?>
                                    <div class="section">
                                        <strong>Tagi:</strong>
                                        <div class="resource-list">
                                            <?php foreach ($task['tags'] as $tag): ?>
                                                <span><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="footer">
                                    <div class="priority"><?php echo $task['priority']; ?></div>
                                    <div class="status"><?php echo $task['status']; ?></div>
                                </div>

                                <div class="section">
                                    <?php if (!empty($task['attachments'])): ?>
                                        <div class="section attachments-section">
                                            <strong>Załączniki:</strong>
                                            <ul class="attachments-list">
                                                <?php foreach ($task['attachments'] as $attachment): ?>
                                                    <li>
                                                        <a href="<?php echo htmlspecialchars($attachment); ?>" target="_blank">
                                                            <?php echo htmlspecialchars(basename($attachment)); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>

<script src="../scripts/drop_menu.js"></script>

</body>