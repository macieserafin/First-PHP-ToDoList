<?php

require_once '../config.php';
require_once '../classes/Task.php';
require_once '../classes/TaskManager.php';
require_once '../classes/CsvHandler.php';
require_once '../classes/Filter.php';
require_once '../classes/Sorter.php';
require_once '../classes/TaskExportImport.php';

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
        $index = (int) $_POST['save_csv'];
        $exporter = new TaskExportImport($csvHandler, $userEmail);
        try {
            $exporter->exportTask($index);
        } catch (InvalidArgumentException $e) {
            $_SESSION['errors'][] = $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
        $csv       = file_get_contents($_FILES['csv_file']['tmp_name']);
        $importer  = new TaskExportImport($csvHandler, $userEmail);
        $errors    = $importer->importTaskCSV($csv);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['add_task'])) {
        $errors = $taskManager->addTask($_POST, $_FILES);
    }

    if (isset($_POST['mark_done'])) {
        $taskId = $_POST['mark_done'];

        $userEmail = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
        $csvHandler = new CsvHandler();
        $taskManager = new TaskManager($csvHandler, $userEmail);

        $tasks = $taskManager->getTasks();

        if (isset($tasks[$taskId])) {
            $tasks[$taskId]['status'] = 'Zakończone';
            $csvHandler->saveTasksToCSV($tasks, $userEmail);  // ✅ poprawna metoda
        }

        header("Location: ../pages/dashboard.php");
        exit;
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
