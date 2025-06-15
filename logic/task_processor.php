<?php

require_once '../config.php';
require_once '../classes/Task.php';
require_once '../classes/TaskManager.php';
require_once '../classes/CsvHandler.php';
require_once '../classes/Filter.php';
require_once '../classes/Sorter.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../pages/home.php');
    exit;
}

$userEmail = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$csvHandler = new CsvHandler();
$taskManager = new TaskManager($csvHandler, $userEmail);
$tasks = $taskManager->getTasks();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Wczytaj dane użytkownika i przygotuj taskManager
    $userEmail = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
    $csvHandler = new CsvHandler();
    $taskManager = new TaskManager($csvHandler, $userEmail);
    $tasks = $taskManager->getTasks();

    if (isset($_POST['save_csv'])) {
        if (!empty($tasks)) {
            $firstTaskTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tasks[0]['title']);
            $csvFileName = $firstTaskTitle . '.csv'; // np. test.csv

            $currentFilePath = '../downloads/' . $csvFileName;

            CsvHandler::createBackup($currentFilePath);    // backup z pełną ścieżką
            CsvHandler::saveTasksToCSV($tasks, $csvFileName); // zapis z samą nazwą
        }
    }

    if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $imported = CsvHandler::loadTasksFromCSV($file['tmp_name']);
            if (!empty($imported)) {
                // Zakładam, że po imporcie chcesz zapisać pod nazwą pierwszego zadania z połączonej tablicy
                $_SESSION['tasks'] = array_merge($tasks, $imported);

                $firstTaskTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_SESSION['tasks'][0]['title']);
                $csvFileName = $firstTaskTitle . '.csv';
                $currentFilePath = '../downloads/' . $csvFileName;

                CsvHandler::createBackup($currentFilePath);
                CsvHandler::saveTasksToCSV($_SESSION['tasks'], $csvFileName);
            }
        }
    }


    if (isset($_POST['add_task'])) {
        $errors = $taskManager->addTask($_POST, $_FILES);
    }

    if (isset($_POST['delete_task'])) {
        $taskManager->deleteTask($_POST['delete_task']);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$sortBy = $_GET['sort'] ?? '';
$tasks = Sorter::sortTasks($tasks, $sortBy);

$filterPriority = $_GET['filter_priority'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';
$filterTags = $_GET['filter_tags'] ?? '';
$tasks = Filter::applyFilters($tasks, $filterPriority, $filterStatus, $filterTags);

$regex = $_GET['regex'] ?? '';
$tasks = Filter::applyRegex($tasks, $regex);
