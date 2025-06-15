<?php

class CsvHandler
{
    public static function createBackup(string $file): void
    {
        $backupFolder = __DIR__ . '/../backups';
        if (!is_dir($backupFolder)) {
            mkdir($backupFolder, 0777, true);
        }

        if (file_exists($file)) {
            $backupName = sprintf(
                '%s/%s.%s.bak',
                $backupFolder,
                basename($file),
                date('Ymd_His')
            );
            copy($file, $backupName);
        }
    }

    public static function saveTasksToCSV(array $tasks, string $userEmail): void
    {
        $safeEmail = self::sanitizeEmail($userEmail);
        $userDir   = __DIR__ . "/../data/{$safeEmail}";

        if (!is_dir($userDir)) {
            mkdir($userDir, 0777, true);
        }

        $filePath = $userDir . '/tasks.csv';
        // Zrób backup starego pliku
        self::createBackup($filePath);

        $fp = fopen($filePath, 'w');
        foreach ($tasks as $task) {
            // 1) Resources i tags – jak dotychczas
            $task['resources'] = is_array($task['resources'])
                ? implode('|', $task['resources'])
                : $task['resources'];

            $task['tags'] = is_array($task['tags'])
                ? implode('|', $task['tags'])
                : $task['tags'];

            // 2) Attachments – jeśli tablica asocjacyjna, mapujemy na URL-e, inaczej zostawiamy string
            if (is_array($task['attachments'])) {
                $urls = [];
                foreach ($task['attachments'] as $att) {
                    if (is_array($att) && isset($att['url'])) {
                        $urls[] = $att['url'];
                    } elseif (is_string($att)) {
                        $urls[] = $att;
                    }
                }
                $task['attachments'] = implode('|', $urls);
            }

            // 3) Na wszelki wypadek upewniamy się, że każdy element jest stringiem
            $flat = array_map(
                fn($v) => is_array($v) ? implode('|', $v) : (string)$v,
                $task
            );

            // 4) Zapis z explicit escape
            fputcsv($fp, $flat, ',', '"', '\\');
        }
        fclose($fp);
    }

    public static function loadTasksFromCSV(string $userEmail): array
    {
        $safeEmail = self::sanitizeEmail($userEmail);
        $filePath  = __DIR__ . "/../data/{$safeEmail}/tasks.csv";
        $tasks     = [];

        $headers = [
            'title', 'category', 'description',
            'priority', 'status', 'date', 'time',
            'location', 'assigned', 'resources',
            'tags', 'attachments'
        ];

        if (!file_exists($filePath)) {
            return $tasks;
        }

        if (($fp = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($fp, 0, ',', '"', '\\')) !== false) {
                $task = [];
                foreach ($headers as $i => $header) {
                    $val = $row[$i] ?? '';
                    if (in_array($header, ['resources', 'tags', 'attachments'], true)) {
                        $task[$header] = $val === '' ? [] : explode('|', $val);
                    } else {
                        $task[$header] = $val;
                    }
                }
                $tasks[] = $task;
            }
            fclose($fp);
        }

        return $tasks;
    }

    private static function sanitizeEmail(string $email): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9_@.]/', '', $email)));
    }
}
