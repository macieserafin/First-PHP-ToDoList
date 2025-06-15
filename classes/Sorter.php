<?php

class Sorter
{
    public static function sortTasks($tasks, $sortBy)
    {
        $allowedSortFields = ['title', 'priority', 'date', 'category'];

        if (!in_array($sortBy, $allowedSortFields)) {
            return $tasks;
        }

        usort($tasks, function($a, $b) use ($sortBy) {
            return strcmp($a[$sortBy], $b[$sortBy]);
        });

        return $tasks;
    }
}
