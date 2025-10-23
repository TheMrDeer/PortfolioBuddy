<?php
/**
 * Minimal, safe validators for the login form.
 * - Normalize POST values to strings (defends against arrays).
 * - Validate email + password.
 * - Do NOT html-escape here; escape only when rendering in HTML.
 */

function get_field(array $src, string $key): string {
    $v = $src[$key] ?? '';
    if (is_array($v)) {
        $first = reset($v);
        $v = ($first === false) ? '' : $first;
    }
    return trim((string)$v);
}

function validate_login_input(array $post): array {
    $errors = [];

    $email    = get_field($post, 'email');
    $password = get_field($post, 'password');

    // Email checks
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Password checks (keep it simple)
    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    return [
        'success' => empty($errors),
        'errors'  => $errors,          // flat list for easy rendering
        'data'    => ['email' => $email], // safe to refill email field
    ];
}
