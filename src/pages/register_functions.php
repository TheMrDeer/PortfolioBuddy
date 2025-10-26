<?php



//Sanitizing -> Werte zu Strings zwingen trimmen, Arrays abfangen, ggf unerwünschte Zeichen entfernen
//Validierung -> Regeln prüfen wie Pflichtfeld,Format,Werteberech,Passwordgleichehit.


function validate_register_input (array $postValue): array{
    $errors = [];

    $email = get_field($postValue, 'email');
    $password = get_field($postValue, 'password');
    $passwordRepeat = get_field($postValue, 'passwordRepeat');
    $fullname = get_field($postValue, 'fullname');

        if($email === '' ){
             $errors[] = 'Email is required';
         } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL )){
             $errors[] = 'Invalid email format.';
         }

         if($password === '' ) {
            $errors[] = 'Password is required';
         }elseif(strlen($password)<8 ){
            $errors[] = 'Password does not meet Requirements';
         }elseif(preg_match('/[0-9]/', $password) !== 1 ){
            $errors[] = 'Password must include at least one number';
         }elseif (preg_match('/[\W_]/', $password) !== 1 ){
            $errors[] = 'Password must include at least one special character';}

        if($passwordRepeat === '') {
            $errors[] = 'Please confirm your password';
        } elseif($password !== $passwordRepeat){
            $errors[] = 'Passwords do not match';
        }

        if($fullname === ''){
            $errors[] = 'Name is required';
        }elseif(strlen($fullname)<3){
            $errors[] = 'Name must be at least 3 characters long';
        }
    return [
        'errors' => $errors,
        'success' => !empty($errors),
        'data' => [ 'email' => $email, 'fullname' => $fullname],
    ];

}


// Kurzer Input von mir: Ist coding style, aber ich persönlich würds bei einer TempVariable belassen -> da man sieht die ändert sich explizit.
function get_field(array $valueSource , string $key): string{
$temp = $valueSource[$key] ?? '';  
if(is_array($temp)){
    $temp = reset($temp);
    $temp = ($temp === false) ?'' : $temp;
}
return trim((string)$temp);

}
