<?php

require_once 'Task.php';

class TaskManager
{
    private $csvHandler;
    private $userEmail;
    private $attachmentDirectory;

    public function __construct($csvHandler, $userEmail, $attachmentDirectory = '../uploads')
    {
        $this->csvHandler = $csvHandler;
        $this->userEmail = $userEmail;
        $this->attachmentDirectory = rtrim($attachmentDirectory, '/') . '/';

        $_SESSION['tasks'] = $this->csvHandler->loadTasksFromCSV($this->userEmail);
    }

    public function getTasks()
    {
        $tasks = $_SESSION['tasks'];

        // Globalne wyszukiwanie po wielu polach
        if (!empty($_GET['query'])) {
            $query = strtolower(trim($_GET['query']));
            $tasks = array_filter($tasks, function ($task) use ($query) {
                return strpos(strtolower($task['title']), $query) !== false
                    || strpos(strtolower($task['description']), $query) !== false
                    || strpos(strtolower($task['location']), $query) !== false
                    || strpos(strtolower($task['assigned']), $query) !== false
                    || strpos(strtolower($task['category']), $query) !== false
                    || strpos(strtolower($task['priority']), $query) !== false
                    || strpos(strtolower($task['status']), $query) !== false
                    || (isset($task['tags']) && is_array($task['tags']) && strpos(strtolower(implode(' ', $task['tags'])), $query) !== false);
            });
        }

        if (!empty($_GET['priority'])) {
            $priority = strtolower(trim($_GET['priority']));
            $tasks = array_filter($tasks, function ($task) use ($priority) {
                return strtolower(trim($task['priority'])) === $priority;
            });
        }

        if (!empty($_GET['status'])) {
            $status = strtolower(trim($_GET['status']));
            $tasks = array_filter($tasks, function ($task) use ($status) {
                return strtolower(trim($task['status'])) === $status;
            });
        }

        if (!empty($_GET['sort_by'])) {
            $sortBy = $_GET['sort_by'];
            $order = $_GET['order'] ?? 'asc';

            usort($tasks, function ($a, $b) use ($sortBy, $order) {
                $valA = $a[$sortBy] ?? '';
                $valB = $b[$sortBy] ?? '';
                return ($order === 'asc' ? 1 : -1) * strnatcasecmp($valA, $valB);
            });
        }

        return $tasks;
    }

    public function addTask($postData, $filesData)
    {
        $errors = Task::validate($postData, $filesData);
        if (!empty($errors)) {
            return $errors;
        }

        $tagsArray = preg_split('/\s+/', $postData['tags'], -1, PREG_SPLIT_NO_EMPTY);
        $resources = isset($postData['resources']) ? array_map('htmlspecialchars', $postData['resources']) : array();
        $attachments = Task::handleAttachments($filesData, $this->attachmentDirectory);

        $taskData = [
            'title' => $postData['title'],
            'category' => $postData['category'],
            'description' => $postData['description'],
            'priority' => $postData['priority'],
            'status' => $postData['status'],
            'date' => $postData['date'],
            'time' => $postData['time'],
            'location' => $postData['location'],
            'assigned' => $postData['assigned'],
            'resources' => $resources,
            'tags' => $tagsArray,
            'attachments' => $attachments
        ];

        if (!isset($_SESSION['tasks']) || !is_array($_SESSION['tasks'])) {
            $_SESSION['tasks'] = [];
        }

        $_SESSION['tasks'][] = $taskData;

        // Zapis do pliku
        CsvHandler::saveTasksToCSV($_SESSION['tasks'], $this->userEmail);

        return [];
    }


    public function deleteTask($index)
    {
        if (isset($_SESSION['tasks'][$index])) {
            unset($_SESSION['tasks'][$index]);
            $_SESSION['tasks'] = array_values($_SESSION['tasks']);
            $this->csvHandler->saveTasksToCSV($_SESSION['tasks'], $this->userEmail);
        }
    }
}
