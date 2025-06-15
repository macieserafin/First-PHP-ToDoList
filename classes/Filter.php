<?php

class Filter
{
    public static function applyFilters($tasks, $priority, $status, $tags)
    {
        if ($priority === '' && $status === '' && $tags === '') {
            return $tasks;
        }

        $filterTagArray = preg_split('/\s+/', $tags, -1, PREG_SPLIT_NO_EMPTY);

        return array_filter($tasks, function($task) use ($priority, $status, $filterTagArray) {
            if ($priority !== '' && $task['priority'] !== $priority) return false;
            if ($status !== '' && $task['status'] !== $status) return false;

            foreach ($filterTagArray as $tag) {
                if (!in_array($tag, $task['tags'])) return false;
            }
            return true;
        });
    }

    public static function applyRegex($tasks, $regex)
    {
        if (empty($regex)) {
            return $tasks;
        }

        return array_filter($tasks, function($task) use ($regex) {
            foreach ($task as $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (@preg_match('/' . $regex . '/ui', $item)) return true;
                    }
                } else {
                    if (@preg_match('/' . $regex . '/ui', $value)) return true;
                }
            }
            return false;
        });
    }
}
