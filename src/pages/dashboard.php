<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}
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
  <?php include '_navbar.php'; ?>

  <div class="container py-5">
    <h1>Welcome to your Dashboard, <?= htmlspecialchars($_SESSION['user']['fullname'], ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p class="lead">This is where you'll see an overview of your portfolio.</p>
  </div>

</body>
</html>
