<?php

function validate_email_value(string $email): array
{
    $errors = [];

    if ($email === '') {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    return $errors;
}

function validate_password_value(string $password): array
{
    $errors = [];

    if ($password === '') {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password does not meet Requirements';
    } elseif (preg_match('/[0-9]/', $password) !== 1) {
        $errors[] = 'Password must include at least one number';
    } elseif (preg_match('/[\\W_]/', $password) !== 1) {
        $errors[] = 'Password must include at least one special character';
    }

    return $errors;
}

function validate_password_repeat_value(string $password, string $passwordRepeat): array
{
    $errors = [];

    if ($passwordRepeat === '') {
        $errors[] = 'Please confirm your password';
    } elseif ($password !== $passwordRepeat) {
        $errors[] = 'Passwords do not match';
    }

    return $errors;
}

function validate_fullname_value(string $fullname): array
{
    $errors = [];

    if ($fullname === '') {
        $errors[] = 'Name is required';
    } elseif (strlen($fullname) < 3) {
        $errors[] = 'Name must be at least 3 characters long';
    }

    return $errors;
}
