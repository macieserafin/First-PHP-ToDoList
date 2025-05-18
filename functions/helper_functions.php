<?php

// Formatowanie opisu zadania
function formatTaskDescription($description) {
    // Zamiana URLi na aktywne linki
    $description = preg_replace(
        '/\b(?:https?|ftp):\/\/[a-z0-9\-+&@#\/%?=~_|!:,.;]*[a-z0-9\-+&@#\/%=~_|]/i',
        '<a href="$0" target="_blank">$0</a>',
        $description
    );

    // Zamiana #tagów na span z klasą
    $description = preg_replace(
        '/#([a-zA-Z0-9_]+)/',
        '<span class="tag">#$1</span>',
        $description
    );

    // Zamiana znaków listy na <li>
    $description = preg_replace(
        '/^[\s]*[-*+][\s]+(.+)$/m',
        '<li>$1</li>',
        $description
    );

    // Dodanie znaczników <ul> wokół listy
    if (strpos($description, '<li>') !== false) {
        $description = '<ul>' . $description . '</ul>';
        $description = str_replace('</ul><ul>', '', $description); // Usunięcie podwójnych list
    }

    return $description;
}

?>