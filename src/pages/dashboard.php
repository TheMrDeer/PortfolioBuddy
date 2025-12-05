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
   
    
    <!-- Bootstrap CSS and JS , da wir es ja verwenden müssen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script> 
   
 <title>Dashboard</title>
</head>
<body>
  <?php include __DIR__ .'/includes/_navbar.php'; ?>

  <div class="container py-5">
    <h1>Welcome to your Dashboard, <?= htmlspecialchars($_SESSION['user']['fullname'], ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p class="lead">This is where you'll see an overview of your portfolio.</p>
    <div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h4 mb-0">Meine Positionen</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Aktienname</th>
                                    <th scope="col" class="text-end">Anzahl</th>
                                    <th scope="col" class="text-end">Kaufpreis</th>
                                    <th scope="col" class="text-center">Kaufdatum</th>
                                    <th scope="col" class="text-end">Gesamtwert</th>
                                    <th scope="col" class="text-center">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Placeholder Row 1: Example of a position -->
                                
                                <!-- Placeholder Row 2: Another example -->
                                <tr>
                                    <td><strong>Microsoft Corp.</strong></td>
                                    <td class="text-end">5</td>
                                    <td class="text-end">€300,00</td>
                                    <td class="text-center">2023-09-15</td>
                                    <td class="text-end">€1.500,00</td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">Löschen</a>
                                    </td>
                                </tr>
                                <!-- Placeholder for empty state (to be used with PHP logic later) -->
                                <!-- <tr><td colspan="6" class="text-center text-muted">Noch keine Positionen hinzugefügt.</td></tr> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  </div>

</body>
</html>
