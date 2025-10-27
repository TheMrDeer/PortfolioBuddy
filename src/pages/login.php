<?php
require_once __DIR__ . '/login_functions.php';

// Only treat POST as a form submission so validation doesn't run on normal GET requests.

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';  //verify ob es ein POST request ist

// Start with a predictable shape so template logic can assume these keys exist.
$result = ['success' => false, 'errors' => [], 'data' => []]; //
 
if ($isPost) {
    // Keep validation logic out of the template so we can test it separately and keep the view simple.
    $result = validate_login_input($_POST);    
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
    <!-- SEO Main Optimierung -->
     <!--    Wobei description und canonical das wichtigste sind anscheinend         -->

     <!-- Das, was in Google unter dem Seitentitel steht. Wichtig für Klickrate (CTR), nicht direkt fürs Ranking, aber super relevant für SEO-Performance.-->
    <meta name="description" content="login to PortfolioBuddy to manage and track your investment portfolio with ease." />
    <!--Zeigt, wer die Seite erstellt hat — manchmal von Suchmaschinen oder Browsern angezeigt, aber hauptsächlich für Doku oder Transparenz nice-to-have.-->
    <meta name="author" content="Stipkovits,Hirsch" />
    <!-- SEHR WICHTIG Hilft gegen Duplicate-Content-Probleme (z. B. wenn dieselbe Seite über /register?ref=xyz erreichbar ist). Canonical sorgt dafür, dass SEO-Power auf eine URL gebündelt wird. -->
    <link rel="canonical" href="https://www.portfoliobuddy.com/login.html" />
   
    <!-- Open Graph (Metadaten wie diese können später mit PHP per Template zentralisiert werden, aber zum lernen gut)-->
   
    <!--Das sind Meta-Tags für Facebook, LinkedIn, X (Twitter) & Co., damit beim Teilen ein schöner Preview-Kasten erscheint.-->
    <meta property="og:title" content="Login — PortfolioBuddy" />
    
    <!---->
    <meta property="og:description" content="Login into PortfolioBuddy to manage and track your investment portfolio with ease." />
    
    <!--Zeigt an welcher Typ von Page (SaaS,Product,Video,Website)-->
    <meta property="og:type" content="Website" />

    <!--Die URL, auf die der Preview verweist.Hilft, dass Social Bots korrekt die Seite referenzieren (besonders bei Redirects oder Query-Params).-->
    <meta property="og:url" content="https://www.portfoliobuddy.com/login.html" />

    <!--Das Bild, das beim Teilen angezeigt wird — super wichtig fürs Branding und Klickrate.-->
    <meta property="og:image" content="https://www.portfoliobuddy.com/assets/og-image-login.png" />
    <!--Der Name der Website-->
    <meta property="og:site_name" content="PortfolioBuddy" />
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Login — PortfolioBuddy" />               
    
    <!-- Bootstrap CSS and JS , da wir es ja verwenden müssen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> 
     <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
   -->
   
  <link rel="stylesheet" href="styleslogin.css">
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
        <?php elseif ($isPost && $success): ?>
            <!-- A simple success message is useful during development; in production you'd redirect to a protected area instead. -->
            <div class="alert alert-success" role="status">Login erfolgreich (Debug)!</div>
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
          Noch kein Konto? <a href="register.php">Registrieren</a>
         </p>
       </div>
     </div>
   </div>
 </body>
</html>