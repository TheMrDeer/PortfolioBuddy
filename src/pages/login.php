<?php
session_start();
require_once __DIR__ . '/util/login_functions.php';
require_once __DIR__ . '/util/utils.php';

// If the user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/dashboard.php');
    exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';  //verify ob es ein POST request ist

// Start with a predictable shape so template logic can assume these keys exist.
$result = ['success' => false, 'errors' => [], 'data' => []]; //
 
if ($isPost) {
    $result = validate_login_input($_POST);

    if ($result['success']) {
        // In a real application, you would fetch the user from the database here.
        // For now, we'll create a placeholder user session.
        $_SESSION['user'] = [
            'id'       => 1, // Placeholder ID
            'fullname' => 'Logged-in User', // Placeholder name
            'email'    => $result['data']['email'],
        ];

        header('Location: /PortfolioBuddy/dashboard.php');
        exit;
    }
}


// Escape once at the point of output to centralize XSS protection and avoid double-escaping elsewhere.
$prefillEmail = htmlspecialchars($result['data']['email'] ?? '', ENT_QUOTES, 'UTF-8');
$errors = $result['errors'];
$success = !empty($result['success']);

?>

<!doctype html>
<html lang="en">
<head>
 <!--Hier drinnen stehen META Tags, CSS, CDNS--> 
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
        
    <!-- Bootstrap CSS , da wir es ja verwenden müssen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> 
     
  <title>Login— PortfolioBuddy</title>

</head>

<body class="min-vh-100 d-flex align-items-center">
  <div class="container">
    <div class="card shadow mx-auto" style="max-width:420px">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Einloggen</h1>
         <p class="text-secondary mb-4">Weiter zum Depot-Tracker</p>

        <!-- Show errors or success message conditionally based on form submission and validation result. -->

        <?php if ($isPost && !empty($errors)): ?>
            <!-- Show validation feedback when the form was submitted; this keeps GETs clean and avoids confusing users. -->
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0 ps-3">
                <?php
                // Errors are expected to be a flat list; joining if an element is an array is defensive — prevents rendering raw arrays.
                foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars(is_array($err) ? implode(', ', $err) : $err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
        <?php endif; ?>

        <!-- The login form itself. We prefill only the email field for UX; passwords are never prefilled for security reasons. -->
        <form method="post" action="">
           <div class="mb-3">
            <!-- Require attribute helps client-side UX // but server-side validation is authoritative; keep both. -->
             <label for="email" class="form-label">E-Mail</label>
             <input type="email" class="form-control" id="email" name="email" value="<?= $prefillEmail ?>" required> 
           </div>

          <div class="mb-2">
            <!-- We don't prefill passwords for security; keep the field blank on every render. -->
            <label for="password" class="form-label">Passwort</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required> 
          </div>

            <!-- A POST submit keeps credentials out of the URL; button is placed after inputs for accessibility. -->
            <button type="submit" class="btn btn-primary w-100 mt-3">Login</button>
         </form>

         <p class="text-center text-secondary mt-3 mb-0">
          Noch kein Konto? <a href="/PortfolioBuddy/register.php">Registrieren</a>
         </p>
       </div>
     </div>
   </div>
 </body>
</html>