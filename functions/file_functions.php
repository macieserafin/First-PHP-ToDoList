<?

// Zapisywane taska do pliku csv
function saveTasksToCSV($tasks, $taskTitle)
{
    // Ścieżka do folderu 'saved tasks'
    $folder = 'saved tasks';

    // Jeśli folder nie istnieje, utwórz go
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true); // 0777 - pełne prawa dostępu
    }

    // Tworzenie poprawnej nazwy pliku (bez znaków specjalnych)
    $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $taskTitle); // Zamienia niedozwolone znaki na "_"
    $filename = $folder . '/' . $safeTitle . '.csv';

    // Otwieranie pliku do zapisu
    if (($fp = fopen($filename, 'w')) !== false) {
        foreach ($tasks as $task) {
            // Przetwarzanie pól z listami
            $task['resources'] = implode('|', $task['resources']);
            $task['tags'] = implode('|', $task['tags']);

            // Zapis danych do pliku CSV
            fputcsv($fp, $task, ',', '"', '\\');
        }
        fclose($fp);
        echo "Zapisano plik: $filename"; // Informacja zwrotna
    } else {
        error_log("Nie można otworzyć pliku: $filename");
    }
}


// Wczytywanie taska do pliku csv
function loadTasksFromCSV($filename) {
    $tasks = [];
    if (!file_exists($filename)) return $tasks;

    if (($fp = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($fp, 0, ',', '"', '\\')) !== false) { // Zaktualizowane wywołanie fgetcsv
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
    $backupFolder = 'backups'; // Lokalizacja folderu kopii zapasowych

    // Sprawdzenie, czy folder istnieje - jeśli nie, utwórz go
    if (!is_dir($backupFolder)) {
        mkdir($backupFolder, 0777, true); // 0777 daje pełne uprawnienia do nowego folderu
    }

    if (file_exists($filename)) {
        $backupName = $backupFolder . '/backup_' . date('Y-m-d_H-i-s') . '.csv';
        copy($filename, $backupName);
    }
}
?>