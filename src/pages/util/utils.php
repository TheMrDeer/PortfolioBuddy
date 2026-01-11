<?php


function get_field(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    if (is_array($value)) {
        $value = reset($value); // Gets the first element, or false if empty.
    }
    return htmlspecialchars(trim((string)$value));
}