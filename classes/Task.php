<?php

class Task
{
    public $title;
    public $category;
    public $description;
    public $priority;
    public $status;
    public $date;
    public $time;
    public $location;
    public $assigned;
    public $resources = [];
    public $tags = [];
    public $attachments = [];

    public function __construct($data)
    {
        $this->title = htmlspecialchars($data['title']);
        $this->category = htmlspecialchars($data['category']);
        $this->description = htmlspecialchars($data['description']);
        $this->priority = htmlspecialchars($data['priority']);
        $this->status = htmlspecialchars($data['status']);
        $this->date = htmlspecialchars($data['date']);
        $this->time = htmlspecialchars($data['time']);
        $this->location = htmlspecialchars($data['location']);
        $this->assigned = htmlspecialchars($data['assigned']);
        $this->resources = array_map('htmlspecialchars', $data['resources']);
        $this->tags = $data['tags'];
        $this->attachments = $data['attachments'];
    }

    public static function validate($postData, $filesData): array
    {
        $errors = [];

        $title = trim($postData['title'] ?? '');
        $category = trim($postData['category'] ?? '');
        $priority = trim($postData['priority'] ?? '');
        $date = trim($postData['date'] ?? '');
        $tags = trim($postData['tags'] ?? '');
        $tagsArray = preg_split('/\s+/', $tags, -1, PREG_SPLIT_NO_EMPTY);

        if ($title === '') $errors[] = "Tytuł zadania jest wymagany.";
        if ($category === '') $errors[] = "Kategoria zadania jest wymagana.";
        if ($priority === '') $errors[] = "Priorytet zadania jest wymagany.";

        if ($date === '') {
            $errors[] = "Data wykonania jest wymagana.";
        } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $errors[] = "Data wykonania musi być w formacie RRRR-MM-DD.";
        }

        if (!empty($tagsArray)) {
            foreach ($tagsArray as $tag) {
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $tag)) {
                    $errors[] = "Tagi mogą zawierać tylko litery, cyfry i podkreślniki.";
                    break;
                }
            }
        }

        // Walidacja plików
        if (isset($filesData['attachments']) && count($filesData['attachments']['name']) > 0) {
            foreach ($filesData['attachments']['name'] as $index => $fileName) {
                $fileSize = $filesData['attachments']['size'][$index];
                $error = $filesData['attachments']['error'][$index];

                if ($error !== UPLOAD_ERR_NO_FILE && $fileSize === 0) {
                    $errors[] = "Przesłany plik {$fileName} ma rozmiar 0 bajtów.";
                }
            }
        }

        return $errors;
    }

    public static function handleAttachments($filesData, $uploadDir): array
    {
        $attachments = [];

        // Upewnij się, że $uploadDir zawsze kończy się slashem
        $targetDir = rtrim($uploadDir, '/') . '/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (isset($filesData['attachments'])) {
            foreach ($filesData['attachments']['name'] as $index => $fileName) {
                $tmpFile = $filesData['attachments']['tmp_name'][$index];
                $error = $filesData['attachments']['error'][$index];
                $fileSize = $filesData['attachments']['size'][$index];

                if ($error === UPLOAD_ERR_OK && $fileSize > 0) {
                    $safeFileName = uniqid() . '-' . basename($fileName);
                    $fullPath = $targetDir . $safeFileName;

                    if (move_uploaded_file($tmpFile, $fullPath)) {
                        $attachments[] = [
                            'name' => htmlspecialchars($fileName),
                            // ZAPISUJEMY TYLKO ŚCIEŻKĘ PUBLICZNĄ DLA UŻYTKOWNIKA
                            'url'  => '/uploads/' . $safeFileName
                        ];
                    }
                }
            }
        }

        return $attachments;
    }
}