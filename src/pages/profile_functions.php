<?php

/**
 * Gets a field from an array source, normalizing it to a string.
 * This is a shared function, consider moving it to a common 'utils.php' file later.
 */
function get_profile_field(array $valueSource, string $key): string
{
    $temp = $valueSource[$key] ?? '';
    if (is_array($temp)) {
        $temp = reset($temp);
        $temp = ($temp === false) ? '' : $temp;
    }
    return trim((string)$temp);
}

/**
 * Validates the input from the profile update form.
 */
function validate_profile_input(array $postValue): array
{
    $errors = [];

    $email = get_profile_field($postValue, 'email');
    $fullname = get_profile_field($postValue, 'fullname');

    if ($email === '') {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if ($fullname === '') {
        $errors[] = 'Name is required';
    } elseif (strlen($fullname) < 3) {
        $errors[] = 'Name must be at least 3 characters long';
    }

    return [
        'errors' => $errors,
        'success' => empty($errors),
        'data' => ['email' => $email, 'fullname' => $fullname],
    ];
}
