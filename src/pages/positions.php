<?php
session_start();

// 1. Alle notwendigen Dateien einbinden
require_once __DIR__ . '/includes/dbaccess.php';          // Datenbank-Zugangsdaten
require_once __DIR__ . '/util/utils.php';                 // Hilfsfunktionen (get_field)
require_once __DIR__ . '/util/positions_functions.php';   

// Login-Check
if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
// Variablen initialisieren
$errors = [];
$successMessage = '';
 
$name = '';
$isin = '';
$qty  = '';
$price = '';
$date = '';

if ($isPost) {
    // 2. Validierung aufrufen 
    $result = validate_asset_input($_POST, $_FILES);

    // Daten für Prefill übernehmen (damit User nicht neu tippen muss) html specialchars denk ich unnötig hier wegen get_field innerhalb von input validierung specialchars ist dort enthalten
    $name  = htmlspecialchars($result['data']['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $isin  = htmlspecialchars($result['data']['isin'] ?? '', ENT_QUOTES, 'UTF-8');
    $qty   = htmlspecialchars($_POST['quantity'] ?? '', ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars($_POST['purchase_price'] ?? '', ENT_QUOTES, 'UTF-8');
    $date  = htmlspecialchars($result['data']['date'] ?? '', ENT_QUOTES, 'UTF-8');

    if ($result['success']) {
        // Daten sind gültig -> In Datenbank speichern
        
        $db_obj = new mysqli($host, $user, $pass, $db);
        if ($db_obj->connect_error) {
            $errors[] = "Verbindungsfehler: " . $db_obj->connect_error;
        } else {
            // SQL für Insert (PDF-Konform: Prepared Statements)
            $sql = "INSERT INTO `assets` (`user_id`, `name`, `isin`, `quantity`, `purchase_price`, `purchase_date`, `asset_type`) VALUES (?, ?, ?, ?, ?, ?, ?)";            
            $stmt = $db_obj->prepare($sql);
            
            // Parameter binden:
            // i = integer (user_id)
            // s = string (name, isin, date)
            // d = double (quantity, price)
            $userId = $_SESSION['user']['id'];
            $valQty = $result['data']['quantity'];
            $valPrice = $result['data']['price'];
            
            // Typen: i (int), s (string), s (string), d (double), d (double), s (string)
            $stmt->bind_param("issdds", $userId, $name, $isin, $valQty, $valPrice, $date);

            if ($stmt->execute()) {
                $newAssetId = $db_obj->insert_id;
                $successMessage = "Position erfolgreich gespeichert!";

                // --- Datei-Upload Handling (nur wenn erfolgreich gespeichert) ---
                if (isset($_FILES['asset_file']) && $_FILES['asset_file']['error'] === UPLOAD_ERR_OK) {
                    
                    // Sicherer Pfad: user_uploads/USER_ID/asset_attachment/ASSET_ID/
                    // Wir nutzen die Asset-ID statt der ISIN, damit sich Dateien bei gleicher Aktie nicht überschreiben.
                    $targetDir = __DIR__ . "/user_uploads/" . $userId . "/asset_attachment/" . $newAssetId;

                    // mkdir fix: Nur erstellen, wenn nicht existiert
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }

                    $fileName = basename($_FILES["asset_file"]["name"]);
                    $targetFilePath = $targetDir . "/" . $fileName;

                    if (move_uploaded_file($_FILES["asset_file"]["tmp_name"], $targetFilePath)) {
                        
                    } else {
                        $errors[] = "Position gespeichert, aber Datei konnte nicht hochgeladen werden.";
                    }
                }

                // Formular leeren nach Erfolg
                $name = $isin = $qty = $price = $date = ''; 

            } else {
                $errors[] = "Datenbankfehler: " . $stmt->error;
            }

            $stmt->close();
            $db_obj->close();
        }

    } else {
        $errors = $result['errors'];
    }
}
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