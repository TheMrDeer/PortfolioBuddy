<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php'; 
require_once __DIR__ . '/util/register_functions.php';
require_once __DIR__ . '/util/utils.php';

// Wenn User schon eingeloggt, weg hier
if (isset($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}

$isPostRequest = $_SERVER['REQUEST_METHOD'] === 'POST'; 

//  "Undefined variable" Warnung
$prefillFullname = '';
$prefillEmail    = '';
$errors          = [];
$success         = false;

if ($isPostRequest) {
    // Validierung
    $result = validate_register_input($_POST);
    $errors = $result['errors'];

    // Wenn Validierung fehlschlägt, die eingegebenen Daten zurück in die Felder schreiben
    // Damit der User nicht alles neu tippen muss.
    if (!$result['success']) {
        $prefillFullname = htmlspecialchars($_POST['fullname'] ?? '', ENT_QUOTES, 'UTF-8');
        $prefillEmail    = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    }

    if ($result['success']) {
        
        //dbaccess.php
        $db_obj = new mysqli($host, $user, $pass, $db);

        // Verbindung prüfen
        if ($db_obj->connect_error) {
            echo "Verbindungsfehler: " . $db_obj->connect_error;
            exit();
        }

        // Passwort hashen 
        $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        
        
        $uname = $result['data']['fullname'];
        $mail  = $result['data']['email'];
        $pass  = $passwordHash;

        
        $sql = "INSERT INTO `users` (`fullname`, `email`, `password_hash`) VALUES (?, ?, ?)";
        $stmt = $db_obj->prepare($sql);

        
        $stmt->bind_param("sss", $uname, $mail, $pass);

        
        if ($stmt->execute()) {
            
            // ID für Ordner holen
            $newUserId = $db_obj->insert_id;

            // Ordnerstruktur anlegen
            $uploadRoot = __DIR__ . '/user_uploads';
            $userFolder    = $uploadRoot . '/' . $newUserId;
            $profileFolder = $userFolder . '/profilepicture';
            $assetFolder   = $userFolder . '/asset_attachment';
            
            foreach ([$userFolder, $profileFolder, $assetFolder] as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }

            // Session starten (Login)
            $_SESSION['user'] = [
                'id'       => $newUserId,
                'fullname' => $uname,
                'email'    => $mail,
                'role'     => 'user'
            ];

            // Redirect
            header('Location: /dashboard.php');
            exit;

        } else {
            
            // Error Code 1062 ist "Duplicate entry"
            if ($db_obj->errno === 1062) {
                $errors[] = "Diese E-Mail-Adresse wird bereits verwendet.";
            } else {
                $errors[] = "Datenbankfehler: " . $stmt->error;
            }
           
        }

        
        $stmt->close();
        $db_obj->close();
    }
}
?>

<!doctype html>
<html lang="en">
<?php
$pageTitle = 'Registrieren - PortfolioBuddy';
include __DIR__ . '/includes/_head.php';
?>
<body class="min-vh-100 d-flex align-items-center bg-light">
  <div class="container">
    <div class="card shadow mx-auto" style="max-width:480px">
      <div class="card-body p-4 p-md-5"> 
        <div class="d-flex align-items-center gap-2 justify-content-center mb-2"> 
          <svg width="32" height="32" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
          </svg>
        </div>

        <h1 class="h4 text-center mb-2">Registrieren bei PortfolioBuddy</h1>
        <p class="text-secondary text-center mb-4">Registriere dich und verfolge deine Anlageperformance</p>
        <?php if ($isPostRequest && !empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars(is_array($err) ? implode(', ', $err) : $err, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="registerForm" action="/register.php" method="post">
          <div class="mb-3">
            <label for="fullname" class="form-label">Name</label>
            <input 
              id="fullname"
              name="fullname"
              value="<?= $prefillFullname ?>"
              type="text"
              inputmode="text"
              required
              autocomplete="name"
              class="form-control"
              placeholder="Max Mustermann"
            />
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input
              id="email"
              name="email"
              value="<?= $prefillEmail ?>"  
              type="email"
              required
              autocomplete="email"
              class="form-control"
              placeholder="beispiel@beispiel.de"
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Passwort</label>
            <small class="form-text text-muted">muss mindestens ein Sonderzeichen und eine Zahl enthalten.</small>
            <input
              id="password"
              name="password"
              type="password"
              required
              minlength="8"
              autocomplete="new-password"
              class="form-control"
              placeholder="Mindestens 8 Zeichen"
            />
          </div>

          <div class="mb-3">
            <label for="confirm" class="form-label">Passwort bestaetigen</label>
            <input
              id="confirm"
              name="passwordRepeat"
              type="password"
              required
              autocomplete="new-password"
              class="form-control"
              placeholder="Passwort wiederholen"
            />
          </div>

          <div class="d-grid mt-4">
            <button class="btn btn-primary" id="submitBtn" type="submit">
              Konto erstellen
            </button>
          </div>
          
        </form>

        <p class="text-center text-secondary mt-3 mb-0">
          Bereits ein Konto? <a class="small-link" href="/login.php">Anmelden</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
