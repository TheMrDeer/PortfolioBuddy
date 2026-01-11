<?php

require_once __DIR__ . '/input_validation_function.php';



//Sanitizing -> Werte zu Strings zwingen trimmen, Arrays abfangen, ggf unerwünschte Zeichen entfernen
//Validierung -> Regeln prüfen wie Pflichtfeld,Format,Werteberech,Passwordgleichehit.

function validate_register_input (array $postValue): array{
    $email = get_field($postValue, 'email');
    $password = get_field($postValue, 'password');
    $passwordRepeat = get_field($postValue, 'passwordRepeat');
    $fullname = get_field($postValue, 'fullname');

    $errors = array_merge(
        validate_email_value($email),
        validate_password_value($password),
        validate_password_repeat_value($password, $passwordRepeat),
        validate_fullname_value($fullname)
    );

    return [
        'errors' => $errors,
        'success' => empty($errors),
        'data' => [ 'email' => $email, 'fullname' => $fullname],
    ];

}
