<?php
session_start();
require_once __DIR__ . '/includes/dbaccess.php'; // $host, $user, $pass, $db
// If user is not logged in, redirect to login page.
if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}

// Placeholder for form submission handling in the future
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($isPost) {
    // Asset submission logic will go here.
    // For now, we can just acknowledge the submission for debugging.
  mkdir("user_uploads/".$_SESSION['user']['id']."/"."asset_attachment/".$_POST['asset_ISIN'], 0755, true);
  // echo "<pre>Form submitted:\n"; print_r($_POST); print_r($_FILES); echo "</pre>";
  $targetFileName= "user_uploads/".$_SESSION['user']['id']."/"."asset_attachment/".$_POST['asset_ISIN']."/".basename($_FILES["asset_file"]["name"]);
  move_uploaded_file($_FILES["asset_file"]["tmp_name"], $targetFileName);

$result = validate_assets_input($_POST);
$errors = $result['errors'];

   

    // Wenn Validierung fehlschlägt, die eingegebenen Daten zurück in die Felder schreiben
    // Damit der User nicht alles neu tippen muss.
    
    if ($result['success']) {
    
        $db_obj = new mysqli($host, $user, $pass, $db);

        // Verbindung prüfen
        if ($db_obj->connect_error) {
            echo "Connection Error: " . $db_obj->connect_error;
            exit();
        }

        
        // Variablen vorbereiten
        $assetname = $result['data']['asset_name'];
        $assetISIN  = $result['data']['asset_ISIN'];
        $quantity = $result['data']['quantity'];
        $purchaseprice = $result['data']['purchase_price'];
        $purchasedate = $result['data']['purchase_date'];
        $assetType = 'stock'; // Hardcoded for now
        $userId = $_SESSION['user']['id'];

        // 3. SQL Statement vorbereiten (PDF Folie 13)
        $sql = "INSERT INTO `assets` (`user_id`, `name`, `isin`,'quantity','purchase_price','purchase_date','asset_type') VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db_obj->prepare($sql);

        // 4. Parameter binden (PDF Folie 15)
        // "sss" -> String, String, String
        $stmt->bind_param("issddds", $userID, $assetname, $assetISIN, $quantity, $purchaseprice, $purchasedate, $assetType);

        // 5. Ausführen (PDF Folie 16/23)
        if ($stmt->execute()) {
        


        // Aufräumen (PDF Folie 23)
        $stmt->close();
        $db_obj->close();
    }
}}
?>


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My Positions — PortfolioBuddy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>

<?php include __DIR__ .'/includes/_navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-4">Aktien verwalten</h1>
                    <p class="text-muted">Fügen Sie hier eine neue Aktienposition hinzu oder bearbeiten Sie eine bestehende.</p>

                    <!-- Form for adding/editing a stock asset -->
                    <form method="post" action="/PortfolioBuddy/positions.php" enctype="multipart/form-data">
                        <!-- Hidden input for asset ID (for editing existing assets later) -->
                        <input type="hidden" name="asset_id" value="">

                        <!-- Asset Name -->
                        <div class="mb-3">
                            <label for="assetName" class="form-label">Aktienname</label>
                            <input type="text" class="form-control" id="assetName" name="asset_name" placeholder="z.B. Apple Inc." required>
                        </div>

                        <div class="mb-3">
                            <label for="assetISIN" class="form-label">ISIN</label>
                            <input type="text" class="form-control" id="assetISIN" name="asset_ISIN" placeholder="z.B. Apple Inc." required>
                        </div>

                        <div class="row">
                            <!-- Quantity -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Anzahl</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="z.B. 10" step="any" required>
                            </div>

                            <!-- Purchase Price -->
                            <div class="col-md-6 mb-3">
                                <label for="purchasePrice" class="form-label">Kaufpreis (pro Stück)</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="purchasePrice" name="purchase_price" placeholder="z.B. 150.50" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Date -->
                        <div class="mb-3">
                            <label for="purchaseDate" class="form-label">Kaufdatum</label>
                            <input type="date" class="form-control" id="purchaseDate" name="purchase_date" required>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="assetFile" class="form-label">Dokument hochladen (optional)</label>
                            <input class="form-control" type="file" id="assetFile" name="asset_file">
                            <small class="form-text text-muted">Laden Sie eine relevante Datei hoch, z.B. den Kaufbeleg.</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Position speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Positions Table Section -->
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
                                <tr>
                                    <td><strong>Apple Inc.</strong></td>
                                    <td class="text-end">10</td>
                                    <td class="text-end">€150,50</td>
                                    <td class="text-center">2023-10-26</td>
                                    <td class="text-end">€1.505,00</td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm btn-outline-primary">Bearbeiten</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">Löschen</a>
                                    </td>
                                </tr>
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

</body>
</html>