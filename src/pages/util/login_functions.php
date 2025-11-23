<?php
/**
 * Minimal, safe validators for the login form.
 * - Normalize POST values to strings (defends against arrays).
 * - Validate email + password.
 * - Do NOT html-escape here; escape only when rendering in HTML.
 */

// «« @MrDeer: Gegenseitige Code-Review wenn du willst mit Kommis (der eine schreibt Code der andere reviewt und kommentiert etc) (englisch oder deutsch)»»

function validate_login_input(array $post): array { // Validates login form input and returns result, wobei $array -> der POST array aus login.php ist(HTML form data)
    $errors = [];

    $email    = get_field($post, 'email'); // Holt den email wert aus dem POST array gibt ihn in $email variable,ruft get_field function auf und normalisiert den wert.
    $password = get_field($post, 'password');  // Holt den password wert aus dem POST array gibt ihn in $password variable,ruft get_field function auf und normalisiert den wert.

    // Email checks
    if($email === '' ){
             $errors[] = 'Email is required';
         } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL )){
             $errors[] = 'Invalid email format.';
         }

    // Password checks (keep it simple)
      if($password === '' ) {
            $errors[] = 'Password is required';
         }elseif(strlen($password)<8 ){
            $errors[] = 'Password does not meet Requirements';
         }elseif(preg_match('/[0-9]/', $password) !== 1 ){
            $errors[] = 'Password must include at least one number';
         }elseif (preg_match('/[\W_]/', $password) !== 1 ){
            $errors[] = 'Password must include at least one special character';}


    return [
        'success' => empty($errors), // Wenn der error array leer ist dann return success "true" sonst false
        'errors'  => $errors,          // flat list for easy rendering
        'data'    => ['email' => $email], // safe to refill email field
    ];
}
