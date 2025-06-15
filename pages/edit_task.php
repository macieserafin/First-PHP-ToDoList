<?php
require_once '../config.php';
require_once '../classes/CsvHandler.php';
require_once '../classes/TaskManager.php';

if (!isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$userEmail = is_array($_SESSION['user']) ? $_SESSION['user']['email'] : $_SESSION['user'];
$csvHandler = new CsvHandler();
$taskManager = new TaskManager($csvHandler, $userEmail);
$tasks = $taskManager->getTasks();

$index = isset($_GET['id']) ? (int)$_GET['id'] : -1;
if (!isset($tasks[$index])) {
    echo "<p>Nie znaleziono zadania.</p>";
    exit;
}

$task = $tasks[$index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tasks[$index]['title'] = $_POST['title'];
    $tasks[$index]['description'] = $_POST['description'];
    $tasks[$index]['category'] = $_POST['category'];
    $tasks[$index]['priority'] = $_POST['priority'];
    $tasks[$index]['status'] = $_POST['status'];
    $tasks[$index]['date'] = $_POST['date'];
    $tasks[$index]['time'] = $_POST['time'];
    $tasks[$index]['location'] = $_POST['location'];
    $tasks[$index]['assigned'] = $_POST['assigned'];
    $tasks[$index]['tags'] = explode(' ', $_POST['tags']);
    $tasks[$index]['resources'] = isset($_POST['resources']) ? $_POST['resources'] : [];

    $csvHandler->saveTasksToCSV($tasks, $userEmail);
    header('Location: dashboard.php');
    exit;
}
include '../includes/head.php';
?>

<div class="edit-container">

    <h2 style="margin-left: 20px;">Edytuj zadanie</h2>
    <form method="POST" style="margin: 20px;" enctype="multipart/form-data">
        <div class="line">
            <label>Tytuł zadania:
                <input name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
            </label>
            <label>Kategoria:
                <select name="category" required>
                    <option value="">Wybierz kategorię</option>
                    <?php $categories = ["Domowe", "Praca", "Nauka", "Hobby", "Inne"]; foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($task['category'] === $cat) ? "selected" : ""; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label>Opis zadania:
            <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
        </label>

        <div class="line">
            <label>Priorytet:
                <select name="priority" required>
                    <option value="">Wybierz priorytet</option>
                    <option value="Niski" <?php echo ($task["priority"] === "Niski") ? "selected" : ""; ?>>Niski</option>
                    <option value="Średni" <?php echo ($task["priority"] === "Średni") ? "selected" : ""; ?>>Średni</option>
                    <option value="Wysoki" <?php echo ($task["priority"] === "Wysoki") ? "selected" : ""; ?>>Wysoki</option>
                </select>
            </label>
            <label>Status:
                <select name="status">
                    <option value="Do zrobienia" <?php echo ($task["status"] === "Do zrobienia") ? "selected" : ""; ?>>Do zrobienia</option>
                    <option value="W trakcie" <?php echo ($task["status"] === "W trakcie") ? "selected" : ""; ?>>W trakcie</option>
                    <option value="Zakończone" <?php echo ($task["status"] === "Zakończone") ? "selected" : ""; ?>>Zakończone</option>
                </select>
            </label>
            <label>Data wykonania:
                <input type="date" name="date" value="<?php echo htmlspecialchars($task['date']); ?>" required>
            </label>
        </div>

        <div class="line">
            <label>Szacowany czas (minuty):
                <input type="number" name="time" value="<?php echo htmlspecialchars($task['time']); ?>">
            </label>
            <label>Lokalizacja:
                <input name="location" value="<?php echo htmlspecialchars($task['location']); ?>">
            </label>
            <label>Osoba przypisana:
                <input name="assigned" value="<?php echo htmlspecialchars($task['assigned']); ?>">
            </label>
            <label>Tagi (oddzielone spacją):
                <input name="tags" value="<?php echo htmlspecialchars(implode(' ', $task['tags'])); ?>">
            </label>
        </div>

        <div class="zasoby">
            <label>Potrzebne zasoby:</label>
            <?php
            $allResources = ["Komputer", "Internet", "Telefon", "Samochód", "Książka", "Narzędzia", "Dokumenty", "Inne"];
            foreach ($allResources as $res) {
                $checked = in_array($res, $task["resources"]) ? "checked" : "";
                echo "<label><input type='checkbox' name='resources[]' value='$res' $checked> $res</label>";
            }
            ?>
        </div>

        <button class="button-left" type="submit">Zapisz zmiany</button>
    </form>
</div>