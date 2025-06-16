<?php
// Plik: classes/TaskExportImport.php

require_once __DIR__ . '/CsvHandler.php';

class TaskExportImport
{
    private $csvHandler;
    private $userEmail;
    // Pola w kolejności, jaka ma być w CSV
    private $fields = [
        'title','category','description',
        'priority','status','date','time',
        'location','assigned','resources',
        'tags','attachments'
    ];

    public function __construct($csvHandler, $userEmail)
    {
        $this->csvHandler = $csvHandler;
        $this->userEmail  = $userEmail;
    }

    /**
     * Eksportuje zadanie o danym indeksie jako plik CSV do przeglądarki (jeden wiersz, bez nagłówka).
     *
     * @param int $index
     * @throws InvalidArgumentException
     */
    public function exportTask(int $index): void
    {
        $tasks = $this->csvHandler->loadTasksFromCSV($this->userEmail);

        if (!isset($tasks[$index])) {
            throw new InvalidArgumentException("Nie znaleziono zadania o indeksie $index.");
        }

        $task = $tasks[$index];

        // Przygotuj wiersz danych: tablice łączymy '|'
        $row = [];
        foreach ($this->fields as $field) {
            $value = $task[$field] ?? '';
            if (is_array($value)) {
                $row[] = implode('|', $value);
            } else {
                $row[] = $value;
            }
        }

        // Ustaw nagłówki HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="task_' . $index . '.csv"');

        $output = fopen('php://output', 'w');
        // Tylko wiersz danych, bez nagłówka
        fputcsv($output, $row, ',', '"', '\\');
        fclose($output);
        exit;
    }
    
    public function importTaskCSV(string $csvContent): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($csvContent));
        if (count($lines) < 1) {
            return ['Plik CSV musi zawierać co najmniej jeden wiersz z danymi.'];
        }

        // Pobierz pierwszy wiersz i sparsuj z pełnymi parametrami
        $rawLine = array_shift($lines);
        $row = str_getcsv($rawLine, ',', '"', '\\');

        if (count($row) !== count($this->fields)) {
            return ['Liczba pól w wierszu różni się od oczekiwanej (' . count($this->fields) . ').'];
        }

        // Zmapuj wartości na pola
        $taskData = [];
        foreach ($this->fields as $i => $column) {
            $value = $row[$i];
            if (in_array($column, ['resources', 'tags', 'attachments'], true)) {
                $taskData[$column] = $value === '' ? [] : explode('|', $value);
            } else {
                $taskData[$column] = $value;
            }
        }

        // Dodaj do pliku CSV zadań
        $tasks = $this->csvHandler->loadTasksFromCSV($this->userEmail);
        $tasks[] = $taskData;
        $this->csvHandler->saveTasksToCSV($tasks, $this->userEmail);

        return [];
    }
}