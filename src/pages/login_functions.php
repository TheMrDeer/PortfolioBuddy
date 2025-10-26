<?php
/**
 * Minimal, safe validators for the login form.
 * - Normalize POST values to strings (defends against arrays).
 * - Validate email + password.
 * - Do NOT html-escape here; escape only when rendering in HTML.
 */

// «« @MrDeer: Gegenseitige Code-Review wenn du willst mit Kommis (der eine schreibt Code der andere reviewt und kommentiert etc) (englisch oder deutsch)»»

        // Bitte variablen so bennenen, dass sie klar machen was sie tun/enthalten (vorallem bei funktionen ->$v, besser $value oder $tempValue etc)
function get_field(array $src, string $key): string { // Normalizes a field from the source array to a trimmed string; if the field is an array, takes the first element.
    $tempValue = $src[$key] ?? ''; // If the key is missing, return empty string.
    if (is_array($tempValue)) { // If the value is an array, take the first element. // Defends against malicious users sending arrays.
        $first = reset($tempValue); // reset() returns false if the "array" is empty or not an array. // & -> Gets the first element of the array.
        $tempValue = ($first === false) ? '' : $first; // if false (weil empty (es muss array sein siehe if-Bedingung 14)) returned empty string
    }
    return trim((string)$tempValue); // Cast to string and trim "whitespace".
}

function validate_login_input(array $post): array { // Validates login form input and returns result, wobei $array -> der POST array aus login.php ist(HTML form data)
    $errors = [];

    $email    = get_field($post, 'email'); // Holt den email wert aus dem POST array gibt ihn in $email variable,ruft get_field function auf und normalisiert den wert.
    $password = get_field($post, 'password');  // Holt den password wert aus dem POST array gibt ihn in $password variable,ruft get_field function auf und normalisiert den wert.

    // Email checks
    if ($email === '') {  // Wenn email empty ist dann fehler
        $errors[] = 'Email is required.'; // Dann füge String in errors array hinzu
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Wenn email nicht valid ist dann fehler und string in errors array hinzufügen.
        $errors[] = 'Invalid email format.';
    }

    // Password checks (keep it simple)
    if ($password === '') { // Wenn password empty ist dann fehler
        $errors[] = 'Password is required.';    // Dann füge String in errors array hinzu
    } elseif (strlen($password) < 6) { // Wenn Passwort kürzer als 6 zeichen ist dann fehler // strlen gibt die länge des strings zurück 
        $errors[] = 'Password must be at least 6 characters.'; // Obwohl das beim Login "Questionable" ist  bzw. kein "Clean Code", für übung wichtig 
    } 

    return [
        'success' => empty($errors), // Wenn der error array leer ist dann return success "true" sonst false
        'errors'  => $errors,          // flat list for easy rendering
        'data'    => ['email' => $email], // safe to refill email field
    ];
}
