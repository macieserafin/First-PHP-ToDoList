<?php
require_once 'config.php';

require 'functions/file_functions.php';
require 'functions/helper_functions.php';
require 'php.php';


include 'includes/head.php';
include 'includes/header.php';
?>



<div class="main-wrapper">
    <div class="form-container">
        <h2>Dodaj nowe zadanie</h2>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" id="taskForm">
            <div class="line">
                <label>Tytuł zadania:
                    <input name="title" required>
                </label>
                <label>Kategoria:
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

            <label>Opis zadania:
                <textarea name="description"></textarea>
            </label>

            <div class="line">
                <label>Priorytet:
                    <select name="priority" required>
                        <option value="">Wybierz priorytet</option>
                        <option>Niski</option>
                        <option>Średni</option>
                        <option>Wysoki</option>
                    </select>
                </label>
                <label>Status:
                    <select name="status">
                        <option>Do zrobienia</option>
                        <option>W trakcie</option>
                        <option>Zakończone</option>
                    </select>
                </label>
                <label>Data wykonania:
                    <input type="date" name="date" required>
                </label>
            </div>

            <div class="line">
                <label>Szacowany czas (minuty):
                    <input type="number" name="time">
                </label>
                <label>Lokalizacja:
                    <input name="location">
                </label>
                <label>Osoba przypisana:
                    <input name="assigned">
                </label>
                <label>Tagi (oddzielone spacją):
                    <input name="tags">
                </label>
            </div>

            <div class="zasoby">
                <label>Potrzebne zasoby:</label>
                <label><input type="checkbox" name="resources[]" value="Komputer"> Komputer</label>
                <label><input type="checkbox" name="resources[]" value="Internet"> Internet</label>
                <label><input type="checkbox" name="resources[]" value="Telefon"> Telefon</label>
                <label><input type="checkbox" name="resources[]" value="Samochód"> Samochód</label>
                <label><input type="checkbox" name="resources[]" value="Książka"> Książka</label>
                <label><input type="checkbox" name="resources[]" value="Narzędzia"> Narzędzia</label>
                <label><input type="checkbox" name="resources[]" value="Dokumenty"> Dokumenty</label>
                <label><input type="checkbox" name="resources[]" value="Inne"> Inne</label>
            </div>

            <button class="button-left" type="submit">Dodaj zadanie</button>

        </form>

        <form method="post" enctype="multipart/form-data">
            <label>Importuj CSV:
                <div class="imnport-cl">
                    <input type="file" name="csv_file" accept=".csv" required>
                    <button type="submit" name="import_csv">Importuj zadania</button>
                </div>
            </label>

        </form>

    </div>



    <div class="tasks-container">


        <div class="filtry">



            <div class="sort">
                <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                    <label>
                        Priorytet:
                        <select name="filter_priority" class="filter-form-content">
                            <option value="">Wszystkie</option>
                            <option value="Niski" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Niski' ? 'selected' : '' ?>>Niski</option>
                            <option value="Średni" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Średni' ? 'selected' : '' ?>>Średni</option>
                            <option value="Wysoki" <?= isset($_GET['filter_priority']) && $_GET['filter_priority'] == 'Wysoki' ? 'selected' : '' ?>>Wysoki</option>
                        </select>
                    </label>

                    <label>
                        Status:
                        <select name="filter_status" class="filter-form-content">
                            <option value="">Wszystkie</option>
                            <option value="Do zrobienia" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'Do zrobienia' ? 'selected' : '' ?>>Do zrobienia</option>
                            <option value="W trakcie" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'W trakcie' ? 'selected' : '' ?>>W trakcie</option>
                            <option value="Zakończone" <?= isset($_GET['filter_status']) && $_GET['filter_status'] == 'Zakończone' ? 'selected' : '' ?>>Zakończone</option>
                        </select>
                    </label>

                    <label>
                        Tagi:
                        <input class="filter-form-content" type="text" name="filter_tags" value="<?= isset($_GET['filter_tags']) ? htmlspecialchars($_GET['filter_tags']) : '' ?>" >
                    </label>

                    <button type="submit">Filtruj</button>
                </form>
            </div>


            <form class="wyszukaj" method="GET" style="margin-bottom: 20px;">
                <input class="wyszukaj" placeholder="Wyszukaj" type="text" id="regex"  name="regex" value="<?php echo isset($_GET['regex']) ? htmlspecialchars($_GET['regex']) : ''; ?>" style="width: 60%; padding: 8px; margin-left: 10px;">
                <button type="submit">Szukaj</button>
            </form>


            <p class="sort">Sortuj według:
                <a href="?sort=title">Tytułu</a> |
                <a href="?sort=priority">Priorytetu</a> |
                <a href="?sort=date">Daty</a> |
                <a href="?sort=category">Kategorii</a>
            </p>

        </div>


        <div class="task-container">
            <?php foreach ($tasks as $key => $task): ?>
                <div class="task-card">
                    <form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                        <button type="submit" name="delete_task" value="<?php echo $key; ?>" class="delete-button">&times;</button>
                    </form>

                    <form>
                        <button type="button" class="edit-button">=</button>
                    </form>

                    <form form method="POST" onsubmit="return confirm('Czy na pewno chcesz to wykonac?');">
                        <button  type="submit" name="save_csv" class="save-button">+</button>
                    </form>

                    <h3><?php echo $task['title']; ?></h3>
                    <div class="badge"><?php echo $task['category']; ?></div>
                    <p><?php echo formatTaskDescription($task['description']); ?></p>

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

                    <?php if (!empty($task['tags'])): ?>
                        <div class="section">
                            <strong>Tagi:</strong>
                            <div class="resource-list">
                                <?php foreach ($task['tags'] as $tag): ?>
                                    <span><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="footer">
                        <div class="priority"><?php echo $task['priority']; ?></div>
                        <div class="status"><?php echo $task['status']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

