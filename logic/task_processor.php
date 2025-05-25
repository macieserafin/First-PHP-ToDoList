<?php


$feedback = '';
$attachmentDirectory = '../uploads/';
$attachments = [];


// Zapisywanie zadania
$csvFile = 'tasks.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_csv'])) {
    createBackup($csvFile); // Tworzenie kopii zapasowej starego pliku (jeśli potrzebne)

    // Pobranie tytułu pierwszego zadania jako przykład
    $taskTitle = isset($_SESSION['tasks'][0]['title']) ? $_SESSION['tasks'][0]['title'] : 'zadanie';

    saveTasksToCSV($_SESSION['tasks'], $taskTitle);
}


// Importowanie zadania
if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $file['tmp_name'];
        $importedTasks = loadTasksFromCSV($tmpPath); // Funkcja ładowania

        if (!empty($importedTasks)) {
            createBackup($csvFile); // Tworzenie kopii zapasowej
            // Import zadań do sesji
            if (!isset($_SESSION['tasks'])) {
                $_SESSION['tasks'] = [];
            }
            $_SESSION['tasks'] = array_merge($_SESSION['tasks'], $importedTasks);
            saveTasksToCSV($_SESSION['tasks'], $csvFile); // Funkcja zapisu
            $feedback = "<p class='feedback'>Import zakończony sukcesem.</p>";
        } else {
            $feedback = "<p class='feedback'>Importowany plik jest pusty lub nieprawidłowy.</p>";
        }
    } else {
        $feedback = "<p class='feedback'>Błąd podczas przesyłania pliku.</p>";
    }
}



// Inicjalizacja tablicy zadań, jeśli jeszcze nie istnieje w sesji
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = array();
}

$tasks = $_SESSION['tasks'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obsługa usuwania zadania
    if (isset($_POST['delete_task'])) {
        $taskIndex = $_POST['delete_task'];
        if (isset($_SESSION['tasks'][$taskIndex])) {
            unset($_SESSION['tasks'][$taskIndex]); // Usuń zadanie
            $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Przebuduj indeksy
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Obsługa dodawania zadania
    if (isset($_POST['add_task']))
    {
        $errors = [];

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

        if (isset($_FILES['attachments']) && count($_FILES['attachments']['name']) > 0) {
            foreach ($_FILES['attachments']['name'] as $index => $fileName) {
                $tmpFile = $_FILES['attachments']['tmp_name'][$index];
                $error = $_FILES['attachments']['error'][$index];
                $fileSize = $_FILES['attachments']['size'][$index];

                if ($error === UPLOAD_ERR_OK && $fileSize > 0) {
                    $safeFileName = uniqid() . '-' . basename($fileName); // Tworzenie unikalnej nazwy dla pliku
                    if (move_uploaded_file($tmpFile, $attachmentDirectory . $safeFileName)) {
                        $attachments[] = [
                            'name' => htmlspecialchars($fileName), // Oryginalna nazwa pliku
                            'url' => $attachmentDirectory . $safeFileName // Ścieżka do załącznika
                        ];
                    } else {
                        $errors[] = "Nie udało się zapisać pliku: " . htmlspecialchars($fileName);
                    }
                } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = "Błąd podczas przesyłania pliku: " . htmlspecialchars($fileName);
                }
            }
        }

        // Jeśli brak błędów, dodaj zadanie
        if (empty($errors)) {
            $newTask = [
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
                'tags' => $tagsArray,
                'attachments' => $attachments // Dodanie obsługi załączników do nowego zadania
            ];
            $tasks[] = $newTask;
            $_SESSION['tasks'] = $tasks;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = implode('<br>', $errors);
        }
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


?>