<?php
session_start();

// Zapisywanie zadania
$csvFile = 'tasks.csv';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_csv'])) {
    createBackup($csvFile);
    saveTasksToCSV($_SESSION['tasks'], $csvFile);
    echo "<p class='save-true'>Zadania zostały zapisane do pliku CSV.</p>";
}

// Importowanie zadania
if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $file['tmp_name'];
        $importedTasks = loadTasksFromCSV($tmpPath);

        if (!empty($importedTasks)) {
            createBackup($csvFile);
            $_SESSION['tasks'] = $importedTasks;
            saveTasksToCSV($importedTasks, $csvFile);
            echo "<p class='save-true'>Import zakończony sukcesem.</p>";
        } else {
            echo "<p class='save-false''>Importowany plik jest pusty lub nieprawidłowy.</p>";
        }
    } else {
        echo "<p class='save-false'>Błąd podczas przesyłania pliku.</p>";
    }
}


// Inicjalizacja tablicy zadań, jeśli jeszcze nie istnieje w sesji
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = array();
}

$tasks = $_SESSION['tasks'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = array();

    // Obsługa usuwania zadania
    if (isset($_POST['delete_task'])) {
        $taskIndex = $_POST['delete_task'];
        if (isset($tasks[$taskIndex])) {
            unset($tasks[$taskIndex]);
            $_SESSION['tasks'] = array_values($tasks); // Przebudowanie indeksów
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Pobieranie i czyszczenie danych z formularza
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $priority = trim(isset($_POST['priority']) ? $_POST['priority'] : '');
    $status = trim(isset($_POST['status']) ? $_POST['status'] : '');
    $date = trim(isset($_POST['date']) ? $_POST['date'] : '');
    $time = trim(isset($_POST['time']) ? $_POST['time'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : '');
    $assigned = trim(isset($_POST['assigned']) ? $_POST['assigned'] : '');
    $tags = trim(isset($_POST['tags']) ? $_POST['tags'] : '');

    // Rozdzielenie tagów po białych znakach
    $tagsArray = preg_split('/\s+/', $tags, -1, PREG_SPLIT_NO_EMPTY);

    // Filtrowanie tablicy resources, z zabezpieczeniem przed HTML-injection
    $resources = isset($_POST['resources']) ? array_map('htmlspecialchars', $_POST['resources']) : array();

    // Walidacja wymaganych pól
    if ($title === '') $errors[] = "Tytuł zadania jest wymagany.";
    if ($category === '') $errors[] = "Kategoria zadania jest wymagana.";
    if ($priority === '') $errors[] = "Priorytet zadania jest wymagany.";

    // Walidacja daty za pomocą wyrażenia regularnego
    if ($date === '') {
        $errors[] = "Data wykonania jest wymagana.";
    } else {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            // ^\d{4}-\d{2}-\d{2}$ - data w formacie RRRR-MM-DD
            $errors[] = "Data wykonania musi być w formacie RRRR-MM-DD.";
        }
    }

    // Walidacja tagów
    if (!empty($tags)) {
        foreach ($tagsArray as $tag) {
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $tag)) {
                // ^[a-zA-Z0-9_]+$ - tylko litery, cyfry i podkreślniki
                $errors[] = "Tagi mogą zawierać tylko litery, cyfry i podkreślniki.";
                break;
            }
        }
    }

    // Jeśli brak błędów, dodaj zadanie
    if (empty($errors)) {
        $newTask = array(
            'title' => htmlspecialchars($title),
            'category' => htmlspecialchars($category),
            'description' => htmlspecialchars($description),
            'priority' => htmlspecialchars($priority),
            'status' => htmlspecialchars($status),
            'date' => htmlspecialchars($date),
            'time' => htmlspecialchars($time),
            'location' => htmlspecialchars($location),
            'assigned' => htmlspecialchars($assigned),
            'resources' => $resources,
            'tags' => $tagsArray
        );
        $tasks[] = $newTask;
        $_SESSION['tasks'] = $tasks;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = implode('<br>', $errors);
    }
}

// Sortowanie
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : '';
$allowedSortFields = array('title', 'priority', 'date', 'category');
if (in_array($sortBy, $allowedSortFields)) {
    usort($tasks, function($a, $b) use ($sortBy) {
        return strcmp($a[$sortBy], $b[$sortBy]);
    });
}

// Filtrowanie według priorytetu, statusu i tagów
$filterPriority = isset($_GET['filter_priority']) ? $_GET['filter_priority'] : '';
$filterStatus = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filterTags = isset($_GET['filter_tags']) ? $_GET['filter_tags'] : '';

if ($filterPriority !== '' || $filterStatus !== '' || $filterTags !== '') {
    $filterTagArray = preg_split('/\s+/', $filterTags, -1, PREG_SPLIT_NO_EMPTY);
    $tasks = array_filter($tasks, function($task) use ($filterPriority, $filterStatus, $filterTagArray) {
        $pass = true;

        if ($filterPriority !== '' && $task['priority'] !== $filterPriority) {
            $pass = false;
        }

        if ($filterStatus !== '' && $task['status'] !== $filterStatus) {
            $pass = false;
        }

        // Sprawdzenie, czy wszystkie tagi pasują do zadania
        if (!empty($filterTagArray)) {
            $taskTags = $task['tags'];
            foreach ($filterTagArray as $tag) {
                if (!in_array($tag, $taskTags)) {
                    $pass = false;
                    break;
                }
            }
        }

        return $pass;
    });
}

// Filtrowanie po wyrażeniu regularnym z GET
$regex = isset($_GET['regex']) ? $_GET['regex'] : '';
if (!empty($regex)) {
    $tasks = array_filter($tasks, function($task) use ($regex) {
        foreach ($task as $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (@preg_match('/' . $regex . '/ui', $item)) return true;
                }
            } else {
                if (@preg_match('/' . $regex . '/ui', $value)) return true;
            }
        }
        return false;
    });
}

// Formatowanie opisu zadania
function formatTaskDescription($description) {
    // Zamiana URLi na aktywne linki
    $description = preg_replace(
        '/\b(?:https?|ftp):\/\/[a-z0-9\-+&@#\/%?=~_|!:,.;]*[a-z0-9\-+&@#\/%=~_|]/i',
        '<a href="$0" target="_blank">$0</a>',
        $description
    );

    // Zamiana #tagów na span z klasą
    $description = preg_replace(
        '/#([a-zA-Z0-9_]+)/',
        '<span class="tag">#$1</span>',
        $description
    );

    // Zamiana znaków listy na <li>
    $description = preg_replace(
        '/^[\s]*[-*+][\s]+(.+)$/m',
        '<li>$1</li>',
        $description
    );

    // Dodanie znaczników <ul> wokół listy
    if (strpos($description, '<li>') !== false) {
        $description = '<ul>' . $description . '</ul>';
        $description = str_replace('</ul><ul>', '', $description); // Usunięcie podwójnych list
    }

    return $description;
}

// Zapisywane taska do pliku csv
function saveTasksToCSV($tasks, $filename)
{
    if(($fp = fopen($filename, 'w')) !== false) {
        foreach ($tasks as $task) {

            $task['resources'] = implode('|', $task['resources']);
            $task['tags'] = implode('|', $task['tags']);

            fputcsv($fp, $task);
        }
        fclose($fp);
    }else{
        error_log('Unable to open file ' . $filename);
    }

}

// Wczytywanie taska do pliku csv
function loadTasksFromCSV($filename) {
    $tasks = [];
    if (!file_exists($filename)) return $tasks;

    if (($fp = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($fp)) !== false) {
            // Walidacja liczby kolumn
            if (count($data) >= 10) {
                $tasks[] = [
                    'title' => htmlspecialchars($data[0]),
                    'category' => htmlspecialchars($data[1]),
                    'description' => htmlspecialchars($data[2]),
                    'priority' => htmlspecialchars($data[3]),
                    'status' => htmlspecialchars($data[4]),
                    'date' => htmlspecialchars($data[5]),
                    'time' => htmlspecialchars($data[6]),
                    'location' => htmlspecialchars($data[7]),
                    'assigned' => htmlspecialchars($data[8]),
                    'resources' => explode('|', $data[9]),
                    'tags' => isset($data[10]) ? explode('|', $data[10]) : []
                ];
            }
        }
        fclose($fp);
    } else {
        error_log("Nie można odczytać pliku: $filename");
    }

    return $tasks;
}

function createBackup($filename) {
    if (file_exists($filename)) {
        $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.csv';
        copy($filename, $backupName);
    }
}
?>



<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>ToDoList</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Menedżer Zadań</h1>

<div class="main-wrapper">
    <div class="form-container">
        <h2>Dodaj nowe zadanie</h2>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" id="taskForm">
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

            <button class="button-left" type="submit">Dodaj zadanie</button>

        </form>

        <form method="post" enctype="multipart/form-data">
            <label>Importuj CSV:
                <div class="imnport-cl">
                    <input type="file" name="csv_file" accept=".csv" required>
                    <button type="submit" name="import_csv">Importuj zadania</button>
                </div>
            </label>

        </form>

    </div>



    <div class="tasks-container">


        <div class="filtry">



            <div class="sort">
                <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                    <label>
                        Priorytet:
                        <select name="filter_priority" class="filter-form-content">
                            <option value="">Wszystkie</option>
                            <option value="Niski" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Niski' ? 'selected' : '' ?>>Niski</option>
                            <option value="Średni" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Średni' ? 'selected' : '' ?>>Średni</option>
                            <option value="Wysoki" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Wysoki' ? 'selected' : '' ?>>Wysoki</option>
                        </select>
                    </label>

                    <label>
                        Status:
                        <select name="filter_status" class="filter-form-content">
                            <option value="">Wszystkie</option>
                            <option value="Do zrobienia" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'Do zrobienia' ? 'selected' : '' ?>>Do zrobienia</option>
                            <option value="W trakcie" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'W trakcie' ? 'selected' : '' ?>>W trakcie</option>
                            <option value="Zakończone" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'Zakończone' ? 'selected' : '' ?>>Zakończone</option>
                        </select>
                    </label>

                    <label>
                        Tagi:
                        <input class="filter-form-content" type="text" name="filter_tags" value="<?= isset($_GET['filter_tags']) ? htmlspecialchars($_GET['filter_tags']) : '' ?>" >
                    </label>

                    <button type="submit">Filtruj</button>
                </form>
            </div>


            <form class="wyszukaj" method="GET" style="margin-bottom: 20px;">
                <input class="wyszukaj" placeholder="Wyszukaj" type="text" id="regex"  name="regex" value="<?php echo isset($_GET['regex']) ? htmlspecialchars($_GET['regex']) : ''; ?>" style="width: 60%; padding: 8px; margin-left: 10px;">
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
            <?php foreach ($tasks as $key => $task): ?>
                <div class="task-card">
                    <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                        <button type="submit" name="delete_task" value="<?php echo $key; ?>" class="delete-button">&times;</button>
                    </form>

                    <form>
                        <button type="button" class="edit-button">=</button>
                    </form>

                    <form form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                        <button  type="submit" name="save_csv" class="save-button">+</button>
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
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>

