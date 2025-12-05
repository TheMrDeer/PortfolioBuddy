<?php

/**
 * Validates the input from the profile update form.
 */
function validate_positions_input(array $postValue): array
{
    $errors = [];

    // Use the consolidated get_field function
    $ = get_field($postValue, 'email');
    $fullname = get_field($postValue, 'fullname');

    if($email === '' ){
             $errors[] = 'Email is required';
         } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL )){
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
