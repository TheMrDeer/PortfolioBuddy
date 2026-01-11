<?php

require_once __DIR__ . '/input_validation_function.php';

function validate_login_input(array $post): array {
    $email    = get_field($post, 'email'); // Holt den email wert aus dem POST array gibt ihn in $email variable,ruft get_field function auf und normalisiert den wert.
    $password = get_field($post, 'password');  

    $errors = array_merge(
        validate_email_value($email),
        validate_password_value($password)
    );

    return [
        'success' => empty($errors), // Wenn der error array leer ist dann return success "true" sonst false
        'errors'  => $errors,          // flat list for easy rendering
        'data'    => ['email' => $email], // safe to refill email field
    ];
}
