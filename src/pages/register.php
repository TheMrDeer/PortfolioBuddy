<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php'; // Deine PDF-Variablen ($host, $user, etc.)
require_once __DIR__ . '/util/register_functions.php';
require_once __DIR__ . '/util/utils.php';

// Wenn User schon eingeloggt, weg hier
if (isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/dashboard.php');
    exit;
}

$isPostRequest = $_SERVER['REQUEST_METHOD'] === 'POST'; 

// 1. Variablen IMMER initialisieren (damit keine "Undefined variable" Warnung kommt)
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
        
        // 2. Datenbankverbindung (PDF Folie 5)
        // Wir nutzen $host, $user, $pass, $db aus dbaccess.php
        $db_obj = new mysqli($host, $user, $pass, $db);

        // Verbindung prüfen
        if ($db_obj->connect_error) {
            echo "Connection Error: " . $db_obj->connect_error;
            exit();
        }

        // Passwort hashen (PDF Folie 23)
        $passwordHash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        
        // Variablen vorbereiten
        $uname = $result['data']['fullname'];
        $mail  = $result['data']['email'];
        $pass  = $passwordHash;

        // 3. SQL Statement vorbereiten (PDF Folie 13)
        $sql = "INSERT INTO `users` (`fullname`, `email`, `password_hash`) VALUES (?, ?, ?)";
        $stmt = $db_obj->prepare($sql);

        // 4. Parameter binden (PDF Folie 15)
        // "sss" -> String, String, String
        $stmt->bind_param("sss", $uname, $mail, $pass);

        // 5. Ausführen (PDF Folie 16/23)
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
            header('Location: /PortfolioBuddy/dashboard.php');
            exit;

        } else {
            // Fehler (z.B. E-Mail schon vergeben)
            // Error Code 1062 ist "Duplicate entry"
            if ($db_obj->errno === 1062) {
                $errors[] = "Diese E-Mail-Adresse wird bereits verwendet.";
            } else {
                $errors[] = "Datenbankfehler: " . $stmt->error;
            }
            // Auch im Fehlerfall Felder gefüllt lassen
            $prefillFullname = htmlspecialchars($uname, ENT_QUOTES, 'UTF-8');
            $prefillEmail    = htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
        }

        // Aufräumen (PDF Folie 23)
        $stmt->close();
        $db_obj->close();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
              
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> 
      
   
  
 <title>Register — PortfolioBuddy</title>
</head>

<!-- BODY MIT BOOTSTRAP-KLASSEN FÜR RESPONSIVENESS UND STYLING -->
 <!--bg-light macht einen Hellen Hintegrund-->
 <body class="bg-light">
    
  <!-- Container mit Padding oben und unten (padding top,bottom mit spacingschritt 5 (48px) ) -->
   <!-- Zusammen: Dein Block sitzt zentriert in einer maximal sinnvollen Breite und bekommt oben/unten großzügigen Abstand, damit das Formular auf allen Devices Luft zum Rand hat.-->
  <div class="container py-5">
    <!-- Zentriert den Inhalt horizontal // wobei .row Bootstrap flex Klasse ist und alle "childs" zu flex container macht-->
    <div class="row justify-content-center">
    <!-- Macht den Inhalt auf kleinen Screens 100% breit, auf mittleren Screens 8 von 12 Spalten (also ca. 66%), auf großen Screens 6 von 12 Spalten (also 50%) -->
     <!--Handy (unter 768px) Nimmt 12 von 12 Spalten → volle Breite - Tablet (ab 768px)	Nimmt 8 von 12 Spalten → etwas schmaler. Laptop (ab 992px)	Nimmt 6 von 12 Spalten → halbe Breite-->
     <div class="col-12 col-md-8 col-lg-6">
        <!-- Ab hier gehts eigentlich los alles darüber war ja nur mal containern und zentralisierung um sich auf bootstrap utils zu konzentrieren, flexbox lernen ist komplex-->
         <!--Hauptkarte mit Schatten-->
        <main>
            
         <div class="card bg-info-subtle shadow-sm">
          
            <div class="card-body p-4 p-md-5"> 
            
                <!-- Icon-->
            <div class="d-flex align-items-center gap-2 justify-content-center mb-2"> 
                <svg width="32" height="32" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                </svg>
            </div>

            <h1 class="h3 text-center mb-2">Register for PortfolioBuddy</h1>
            <p class="lead text-center mb-4">Sign up and track your investing performance</p>
        <?php if ($isPostRequest && !empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars(is_array($err) ? implode(', ', $err) : $err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($isPostRequest && $success): ?>
            <div class="alert alert-success" role="status">Registration successful (Debug)!</div>
        <?php endif; ?>
        

 <!--Formular mit ID und php Anbindung auf /register.php (backend post call)-->
  <form id="registerForm" action="/PortfolioBuddy/register.php" method="post">
  <!-- Full Name -->
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
      placeholder="Jane Doe"
    />
  </div>

  <!-- Email -->
  <div class="mb-3">
    <label for="email" class="form-label">E-mail</label>
    <input
      id="email"
      name="email"
      value="<?= $prefillEmail ?>"  
      type="email"
      required
      autocomplete="email"
      class="form-control"
      placeholder="example@com"
    />
  </div>

  <!-- Password -->
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <small class="form-text text-muted">must include at least one special character and one number.</small>
    <input
      id="password"
      name="password"
      type="password"
      required
      minlength="8"
      autocomplete="new-password"
      class="form-control"
      placeholder="At least 8 characters"
    />
  </div>

  <!-- Confirm Password -->
  <div class="mb-3">
    <label for="confirm" class="form-label">Confirm Password</label>
    <input
      id="confirm"
      name="passwordRepeat"
      type="password"
      required
      autocomplete="new-password"
      class="form-control"
      placeholder="Repeat your password"
    />
  </div>

  <!-- Submit Button -->
  <div class="d-grid mt-4">
    <button class="btn btn-primary" id="submitBtn" type="submit">
      Create account
    </button>
  </div>

  <!-- Divider -->
  <div>
    <div class="d-flex align-items-center my-2">
       <hr class="flex-grow-1">
         <span class="px-2 text-muted text-uppercase">or</span>
            <hr class="flex-grow-1">
    </div>
    <p class="text-center mb-3 auth-changer">Or use modern auth</p>
  </div>

  <!-- OAuth / Google Sign Up -->
  <div class="d-grid gap-2">
    <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center">
      <img
        src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg"
        alt="Google Logo"
        style="width: 20px; height: 20px; margin-right: 8px"
      />
      Sign up with Google
    </button>
  </div>
</form>
           

        </main>
        <!-- Alles was außerhalb von main ist, gehört rein Logischer Struktur nicht zur register.html seite, also eher dann zur login seite-->
        <p class="text-center mt-3"> Already have an account? <a class="small-link" href="/PortfolioBuddy/login.php">Sign in</a></p>
      </div>
    </div>
  </div>


<script>//@CreatineAbuser magst du für sowas echt JS benutzen? :D weil müssen das ja auch erklären 
       // @MrDeer naja, ist ja nur ne kleine Spielerei hier auf der register seite ;)  -> Kann dann bei goLive weg :)
  (function () {
    const words = [
      "Or use modern auth",
      "SSO",
      "OAuth2",
      "Magic links"
    ];
    const glitchChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#@$%";
    const target = document.querySelector(".auth-changer");
    if (!target) return;

    let index = 0;
    const glitchDuration = 400;
    const frameInterval = 40;
    const swapInterval = 2800;

    const runGlitch = (nextWord) => {
      const start = performance.now();
      target.classList.add("glitching");

      const update = (now) => {
        const elapsed = now - start;
        if (elapsed >= glitchDuration) {
          target.textContent = nextWord;
          target.classList.remove("glitching");
          return;
        }

        const scrambled = Array.from(nextWord)
          .map(() => glitchChars[Math.floor(Math.random() * glitchChars.length)])
          .join("");

        target.textContent = scrambled;
        setTimeout(() => requestAnimationFrame(update), frameInterval);
      };

      requestAnimationFrame(update);
    };

    setInterval(() => {
      index = (index + 1) % words.length;
      runGlitch(words[index]);
    }, swapInterval);
  })();
</script>



 </body>
</html> 