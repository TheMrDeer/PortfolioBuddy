<?php

/**
 * Normalizes a field from a source array (like $_POST) to a trimmed string.
 *
 * If the field is an array, it takes the first element. This is a security
 * measure to prevent unexpected input types.
 */
function get_field(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    if (is_array($value)) {
        $value = reset($value); // Gets the first element, or false if empty.
    }
    return trim((string)$value);
}