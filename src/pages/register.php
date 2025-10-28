
<?php
require_once __DIR__ . '/register_functions.php';

 $isPostRequest = $_SERVER['REQUEST_METHOD'] === 'POST'; 
 $result = ['success' => false, 'errors' => [], 'data' => []];


 if($isPostRequest) {
  $result = validate_register_input($_POST);
   if($result['success']){ 
    session_start();
    // Here you would typically handle successful registration, e.g., save to database
      /*
     $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=portfoliobuddy;charset=utf8mb4',
        'db_user',
        'db_pass',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        'INSERT INTO users (email, fullname, password_hash) VALUES (:email, :fullname, :hash)'
    );

    $stmt->execute([
        ':email'    => $result['data']['email'],
        ':fullname' => $result['data']['fullname'],
        ':hash'     => password_hash($postPassword, PASSWORD_DEFAULT),
    ]);

    $userId = (int)$pdo->lastInsertId();

    $_SESSION['user'] = [
        'id'       => $userId,
        'fullname' => $result['data']['fullname'],
        'email'    => $result['data']['email'],
    ];

      */

      $tempFullname =  $result['data']['fullname'] ?? '';
      $tempEmail = $result['data']['email'] ?? '';

      // die Variablen sind testzwecke
      $_SESSION['user'] = [
        'id' => 1,
        'fullname' => $tempFullname,
        'email' => $tempEmail,

      ];


    header('Location: /dashboard.php'); // Redirect to a welcome page after successful registration
    exit;
   }  

 }

    $prefillEmail = htmlspecialchars($result['data']['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $prefillFullname = htmlspecialchars($result['data']['fullname'] ?? '', ENT_QUOTES, 'UTF-8');
    $errors = $result['errors'];
    $success = !empty($result['success']);  

 
 ?>  


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <!-- SEO Main Optimierung -->
     <!--    Wobei description und canonical das wichtigste sind anscheinend         -->

     <!-- Das, was in Google unter dem Seitentitel steht. Wichtig für Klickrate (CTR), nicht direkt fürs Ranking, aber super relevant für SEO-Performance.-->
    <meta name="description" content="Register for PortfolioBuddy to manage and track your investment portfolio with ease." />
    <!--Zeigt, wer die Seite erstellt hat — manchmal von Suchmaschinen oder Browsern angezeigt, aber hauptsächlich für Doku oder Transparenz nice-to-have.-->
    <meta name="author" content="Stipkovits,Hirsch" />
    <!-- SEHR WICHTIG Hilft gegen Duplicate-Content-Probleme (z. B. wenn dieselbe Seite über /register?ref=xyz erreichbar ist). Canonical sorgt dafür, dass SEO-Power auf eine URL gebündelt wird. -->
    <link rel="canonical" href="https://www.portfoliobuddy.com/register.html" />
   
    <!-- Open Graph (Metadaten wie diese können später mit PHP per Template zentralisiert werden, aber zum lernen gut)-->
   
    <!--Das sind Meta-Tags für Facebook, LinkedIn, X (Twitter) & Co., damit beim Teilen ein schöner Preview-Kasten erscheint.-->
    <meta property="og:title" content="Register — PortfolioBuddy" />
    
    <!---->
    <meta property="og:description" content="Register for PortfolioBuddy to manage and track your investment portfolio with ease." />
    
    <!--Zeigt an welcher Typ von Page (SaaS,Product,Video,Website)-->
    <meta property="og:type" content="Website" />

    <!--Die URL, auf die der Preview verweist.Hilft, dass Social Bots korrekt die Seite referenzieren (besonders bei Redirects oder Query-Params).-->
    <meta property="og:url" content="https://www.portfoliobuddy.com/register" />

    <!--Das Bild, das beim Teilen angezeigt wird — super wichtig fürs Branding und Klickrate.-->
    <meta property="og:image" content="https://www.portfoliobuddy.com/assets/og-image-register.png" />
    <!--Der Name der Website-->
    <meta property="og:site_name" content="PortfolioBuddy" />
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Register — PortfolioBuddy" />               
    
    <!-- Bootstrap CSS and JS , da wir es ja verwenden müssen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
   
   
  <link rel="stylesheet" href="styles.css">
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
  <form id="registerForm" action="/register.php" method="post">
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
        <p class="text-center mt-3"> Already have an account? <a class="small-link" href="/login.php">Sign in</a></p>
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