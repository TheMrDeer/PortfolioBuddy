<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php'; // $host, $user, $pass, $db

// Login Check
if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}

// 1. Datenbank verbinden
$db_obj = new mysqli($host, $user, $pass, $db);
if ($db_obj->connect_error) {
    die("Verbindungsfehler: " . $db_obj->connect_error);
}

// 2. Assets des Users laden
$userId = $_SESSION['user']['id'];
$assets = [];

// SQL: Wähle alle Assets für diesen User, sortiert nach Kaufdatum (neueste zuerst)
$sql = "SELECT * FROM `assets` WHERE `user_id` = ? ORDER BY `purchase_date` DESC";
$stmt = $db_obj->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Alle Zeilen als assoziatives Array holen
while ($row = $result->fetch_assoc()) {
    $assets[] = $row;
}

$stmt->close();
$db_obj->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script> 
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
                                    <?php if (count($assets) > 0): ?>
                                        <?php foreach ($assets as $asset): ?>
                                            <?php 
                                                // Berechnungen für die Anzeige
                                                $totalValue = $asset['quantity'] * $asset['purchase_price'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($asset['name']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($asset['isin']) ?></small>
                                                </td>
                                                <td class="text-end"><?= number_format($asset['quantity'], 2, ',', '.') ?></td>
                                                <td class="text-end">€<?= number_format($asset['purchase_price'], 2, ',', '.') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($asset['purchase_date']) ?></td>
                                                <td class="text-end">€<?= number_format($totalValue, 2, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <a href="/PortfolioBuddy/positions.php" class="btn btn-sm btn-outline-primary">Verwalten</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Noch keine Positionen vorhanden. <br>
                                                <a href="/PortfolioBuddy/positions.php">Erste Aktie hinzufügen</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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