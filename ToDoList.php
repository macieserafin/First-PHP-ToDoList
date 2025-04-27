<?php
session_start();

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

$tasks = $_SESSION['tasks'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (isset($_POST['delete_task'])) {
        $taskIndex = $_POST['delete_task'];
        if (isset($tasks[$taskIndex])) {
            unset($tasks[$taskIndex]);
            $_SESSION['tasks'] = array_values($tasks);
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $priority = isset($_POST['priority']) ? trim($_POST['priority']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $assigned = isset($_POST['assigned']) ? trim($_POST['assigned']) : '';
    $resources = isset($_POST['resources']) ? array_map('htmlspecialchars', $_POST['resources']) : [];

    if ($title === '') {
        $errors[] = "Tytuł zadania jest wymagany.";
    }
    if ($category === '') {
        $errors[] = "Kategoria zadania jest wymagana.";
    }
    if ($priority === '') {
        $errors[] = "Priorytet zadania jest wymagany.";
    }
    if ($date === '') {
        $errors[] = "Data wykonania jest wymagana.";
    }

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
            'resources' => $resources
        ];

        $tasks[] = $newTask;
        $_SESSION['tasks'] = $tasks;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = implode('<br>', $errors);
    }
}

$sortBy = isset($_GET['sort']) ? $_GET['sort'] : '';
$allowedSortFields = ['title', 'priority', 'date', 'category'];

if (in_array($sortBy, $allowedSortFields)) {
    usort($tasks, function ($a, $b) use ($sortBy) {
        return strcmp($a[$sortBy], $b[$sortBy]);
    });
}
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>ToDoList</title>
    <style>
        * {
            font-family: Arial;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px;
        }

        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 800px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            padding-top: 10px;
        }

        .line {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 0;
        }

        .line label {
            width: 50%;
            text-align: left;
        }

        label {
            margin: 10px;
        }

        textarea {
            width: 720px;
            height: 90px;
            resize: none;
        }

        button {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2c3f50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #41607d;
        }

        h1, h2, h3 {
            color: #2c3f50;
        }

        h1 {
            text-align: center;
        }

        h1::after {
            content: "";
            display: block;
            width: 50px;
            height: 3px;
            background-color: #2c3f50;
            margin: 5px auto 0;
        }

        .task-card {
            position: relative;
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 15px;
            width: 300px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 14px;
        }

        .task-card h3 {
            margin: 0;
            font-size: 20px;
            color: #2c3f50;
        }

        .task-card .badge {
            background-color: #eef1f6;
            color: #2c3f50;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            width: fit-content;
        }

        .task-card p {
            margin: 0;
            color: #000000;
        }

        .task-card .section {
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .task-card .resource-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }

        .task-card .resource-list span {
            background-color: #eef1f6;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
        }

        .task-card .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .task-card .priority {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
        }

        .task-card .status {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
        }

        .task-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .sort a {
            text-decoration: none;
            color: #2c3f50;
        }

        .zasoby label{
            margin: 0;
        }

        .task-card form button {
            padding-left: 5px;
            padding-right: 5px;
            padding-bottom: 2.5px;
            padding-top: 2.5px;
            background: rgba(209, 20, 20, 0.81);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            margin: 0;
        }


    </style>
</head>
<body>

<h1>Menedżer Zadań</h1>

<div class="container">
    <div class="content">
        <h2>Dodaj nowe zadanie</h2>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" id="taskForm">
            <div class="line">
                <label><p>Tytuł zadania:</p><input name="title" required></label>
                <label><p>Kategoria:</p>
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

            <label><p>Opis zadania:</p><textarea name="description"></textarea></label>

            <div class="line">
                <label><p>Priorytet:</p>
                    <select name="priority" required>
                        <option value="">Wybierz priorytet</option>
                        <option>Niski</option>
                        <option>Średni</option>
                        <option>Wysoki</option>
                    </select>
                </label>

                <label><p>Status:</p>
                    <select name="status">
                        <option>Do zrobienia</option>
                        <option>W trakcie</option>
                        <option>Zakończone</option>
                    </select>
                </label>

                <label><p>Data wykonania:</p><input type="date" name="date" required></label>
            </div>

            <div class="line">
                <label><p>Szacowany czas (minuty):</p><input type="number" name="time"></label>
                <label><p>Lokalizacja:</p><input name="location"></label>
                <label><p>Osoba przypisana:</p><input name="assigned"></label>
            </div>

            <div class="zasoby">
                <label><p>Potrzebne zasoby:</p></label>
                <label><input type="checkbox" name="resources[]" value="Komputer"> Komputer</label>
                <label><input type="checkbox" name="resources[]" value="Internet"> Internet</label>
                <label><input type="checkbox" name="resources[]" value="Telefon"> Telefon</label>
                <label><input type="checkbox" name="resources[]" value="Samochód"> Samochód</label>
                <label><input type="checkbox" name="resources[]" value="Książka"> Książka</label>
                <label><input type="checkbox" name="resources[]" value="Narzędzia"> Narzędzia</label>
                <label><input type="checkbox" name="resources[]" value="Dokumenty"> Dokumenty</label>
                <label><input type="checkbox" name="resources[]" value="Inne"> Inne</label>
            </div>

            <button type="submit">Dodaj zadanie</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="content">
        <h2>Lista zadań</h2>
        <p class="sort">Sortuj według:
            <a href="?sort=title">Tytułu</a> |
            <a href="?sort=priority">Priorytetu</a> |
            <a href="?sort=date">Daty</a> |
            <a href="?sort=category">Kategorii</a>
        </p>

        <div class="task-container">
            <?php foreach ($tasks as $key => $task): ?>
                <div class="task-card">
                    <form method="POST">
                        <button type="submit" name="delete_task" value="<?php echo $key; ?>">&times;</button>
                    </form>
                    <h3><?php echo $task['title']; ?></h3>
                    <div class="badge"><?php echo $task['category']; ?></div>
                    <p><?php echo $task['description']; ?></p>

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
