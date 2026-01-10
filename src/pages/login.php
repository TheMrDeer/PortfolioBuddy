<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php'; // $host, $user, $pass, $db
require_once __DIR__ . '/util/login_functions.php';
require_once __DIR__ . '/util/utils.php';

// Wenn User schon eingeloggt, weg hier
if (isset($_SESSION['user'])) {
        header('Location: /dashboard.php');
        exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$result = ['success' => false, 'errors' => [], 'data' => []];
 
if ($isPost) {
    // 1. Basis-Validierung (Format E-Mail etc.)
    $result = validate_login_input($_POST);

    if ($result['success']) {
        
        // Datenbankverbindung aufbauen (wie in register.php)
        $db_obj = new mysqli($host, $user, $pass, $db);

        if ($db_obj->connect_error) {
            $result['errors'][] = "Verbindungsfehler: " . $db_obj->connect_error;
            $result['success'] = false;
        } else {
            
            
            $emailRaw = $result['data']['email'];
            $passwordInput = get_field($_POST, 'password');

            
            $sql = "SELECT id, fullname, email, password_hash, role FROM users WHERE email = ?";
            $stmt = $db_obj->prepare($sql);

            
            $stmt->bind_param("s", $emailRaw);
            $stmt->execute();

            
            $stmt->bind_result($uid, $uname, $uemail, $upassHash, $urole);

            
            if ($stmt->fetch()) {
               
                if (password_verify($passwordInput, $upassHash)) {
                    // Login erfolgreich!
                    
                    // Session setzen
                    $_SESSION['user'] = [
                        'id'       => $uid,
                        'fullname' => $uname,
                        'email'    => $uemail,
                        'role'     => $urole
                    ];

                    // Redirect
                    header('Location: /dashboard.php');
                    exit;

                } else {
                    // Passwort falsch
                    $result['success'] = false;
                    $result['errors'][] = "Ungültige E-Mail-Adresse oder Passwort.";
                }
            } else {
                // Keine E-Mail gefunden
                $result['success'] = false;
                $result['errors'][] = "Ungültige E-Mail-Adresse oder Passwort.";
            }

            
            $stmt->close();
            $db_obj->close();
        }
    }
}

// Variablen für die View
$prefillEmail = htmlspecialchars($result['data']['email'] ?? '', ENT_QUOTES, 'UTF-8');
$errors = $result['errors'];

?>

<!doctype html>
<html lang="en">
<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
        
    
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
            
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0 ps-3">
                <?php
                foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars(is_array($err) ? implode(', ', $err) : $err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
           <div class="mb-3">
             <label for="email" class="form-label">E-Mail</label>
             <input type="email" class="form-control" id="email" name="email" value="<?= $prefillEmail ?>" required> 
           </div>

          <div class="mb-2">
            <label for="password" class="form-label">Passwort</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required> 
          </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">Login</button>
         </form>

         <p class="text-center text-secondary mt-3 mb-0">
          Noch kein Konto? <a href="/register.php">Registrieren</a>
         </p>
       </div>
     </div>
   </div>
 </body>
</html>
