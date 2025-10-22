<?php
/**
 * Simple input sanitization + validation helpers for login
 */

function sanitize_input(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function validate_email(string $email): array {
    $clean = sanitize_input($email);
    $errors = [];

    if ($clean === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($clean, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    return ['clean' => $clean, 'errors' => $errors];
}

function validate_password(string $password, int $minLength = 6): array {
    $clean = sanitize_input($password);
    $errors = [];

    if ($clean === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($clean) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters.";
    }

    return ['clean' => $clean, 'errors' => $errors];
}

/**
 * Validate login form POST data.
 * Expects keys: 'email', 'password'
 * Returns: ['valid' => bool, 'errors' => array, 'clean' => array]
 */
function validate_login_input(array $data): array {
    $emailRes = validate_email($data['email'] ?? '');
    $pwdRes   = validate_password($data['password'] ?? '');

    $errors = [
        'email' => $emailRes['errors'],
        'password' => $pwdRes['errors']
    ];

    $flatErrors = array_merge($emailRes['errors'], $pwdRes['errors']);

    return [
        'valid' => empty($flatErrors),
        'errors' => $errors,
        'clean' => [
            'email' => $emailRes['clean'],
            'password' => $pwdRes['clean']  // note: still sanitized, do not log/store plain
        ]
    ];
}