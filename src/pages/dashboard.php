<?php
session_start();
?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <!-- SEO Main Optimierung -->
    <meta name="description" content="Dashboard." />
    <meta name="author" content="Stipkovits,Hirsch" />
    <link rel="canonical" href="https://www.portfoliobuddy.com/welcome.php" />
      
    <meta property="og:title" content=" — PortfolioBuddy" />
    <meta property="og:description" content="Dashboard" />
    
    <!--Zeigt an welcher Typ von Page (SaaS,Product,Video,Website)-->
    <meta property="og:type" content="Website" />

    <!--Die URL, auf die der Preview verweist.Hilft, dass Social Bots korrekt die Seite referenzieren (besonders bei Redirects oder Query-Params).-->
    <meta property="og:url" content="https://www.portfoliobuddy.com/dashboard.php" />

    <!--Das Bild, das beim Teilen angezeigt wird — super wichtig fürs Branding und Klickrate.-->
    <!--Der Name der Website-->
    <meta property="og:site_name" content="PortfolioBuddy" />             
    
    <!-- Bootstrap CSS and JS , da wir es ja verwenden müssen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
   
   
  <link rel="stylesheet" href="styles.css">
 <title>Dashboard</title>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">PortfolioBuddy</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dashNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="dashNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="/dashboard.php">Overview</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/positions.php">Positions</a>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="" alt="Avatar" class="rounded-circle me-2" widt="28" height="28">
             <span><?= htmlspecialchars($_SESSION['user']['fullname'],ENT_QUOTES,'UTF-8') ?></span>
                </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
              <li><a class="dropdown-item" href="/profile.php">Profile</a></li>
              <li><a class="dropdown-item" href="/settings.php">Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout.php">Logout</a></li>
            </ul>
          </li>
        </ul>
    </div>
  </div>
</nav>


</body>
</html>


