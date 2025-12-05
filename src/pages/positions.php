<?php
session_start();

// Alle notwendigen Dateien einbinden
require_once __DIR__ . '/includes/dbaccess.php';          
require_once __DIR__ . '/util/utils.php';                 
require_once __DIR__ . '/util/positions_functions.php';   
require_once __DIR__ . '/util/stock_data_functions.php'; // NEU: API Funktionen

// Login-Check
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$db_obj = new mysqli($host, $user, $pass, $db);
if ($db_obj->connect_error) {
    die("Verbindungsfehler: " . $db_obj->connect_error);
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$userId = $_SESSION['user']['id'];

$errors = [];
$successMessage = '';

// Init Variablen
$currentAssetId = ''; 
$name = '';
$isin = '';
$ticker = ''; // NEU
$qty  = '';
$price = '';
$date = '';
$isEditMode = false;

// --- A. LÖSCHEN ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delSql = "DELETE FROM `assets` WHERE `id` = ? AND `user_id` = ?";
    $delStmt = $db_obj->prepare($delSql);
    $delId = (int)$_GET['id'];
    $delStmt->bind_param("ii", $delId, $userId);
    if ($delStmt->execute()) {
        $successMessage = "Position gelöscht.";
    } else {
        $errors[] = "Fehler: " . $delStmt->error;
    }
    $delStmt->close();
}

// --- B. SPEICHERN (INSERT/UPDATE) ---
if ($isPost) {
    $result = validate_asset_input($_POST, $_FILES);
    $currentAssetId = $_POST['asset_id'] ?? ''; 

    // Daten aus Validierung übernehmen
    $name  = $result['data']['name'] ?? '';
    $isin  = $result['data']['isin'] ?? '';
    $ticker = $result['data']['ticker'] ?? ''; // NEU
    $date  = $result['data']['date'] ?? '';
    $qty   = $result['data']['quantity'] ?? '';
    $price = $result['data']['price'] ?? '';
    
    if (!empty($currentAssetId)) {
        $isEditMode = true;
    }

    if ($result['success']) {
        
        // 1. API-CHECK: Wenn Ticker leer ist, versuchen wir ihn via ISIN zu holen
        if (empty($ticker) && !empty($isin)) {
            // Funktion aus stock_data_functions.php
            $apiData = get_data_from_isin($isin);
            if ($apiData && isset($apiData['ticker'])) {
                $ticker = $apiData['ticker'];
                // Optional: Auch den Namen updaten, falls der User faul war?
                // if (empty($name)) $name = $apiData['name'];
            }
        }

        $assetType = 'stock';

        if ($isEditMode) {
            // UPDATE mit Ticker
            $sql = "UPDATE `assets` SET `name`=?, `isin`=?, `ticker`=?, `quantity`=?, `purchase_price`=?, `purchase_date`=? WHERE `id`=? AND `user_id`=?";
            $stmt = $db_obj->prepare($sql);
            // Parameter: sssddsii (String, String, String, Double, Double, String, Int, Int)
            $stmt->bind_param("sssddsii", $name, $isin, $ticker, $qty, $price, $date, $currentAssetId, $userId);
            
            if ($stmt->execute()) {
                $successMessage = "Position aktualisiert! (Ticker: $ticker)";
                $targetAssetId = $currentAssetId; 
                // Reset Form
                $name = $isin = $ticker = $qty = $price = $date = $currentAssetId = ''; 
                $isEditMode = false;
            } else {
                $errors[] = "DB Fehler: " . $stmt->error;
            }
        } else {
            // INSERT mit Ticker
            $sql = "INSERT INTO `assets` (`user_id`, `name`, `isin`, `ticker`, `quantity`, `purchase_price`, `purchase_date`, `asset_type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db_obj->prepare($sql);
            // Parameter: isssddss
            $stmt->bind_param("isssddss", $userId, $name, $isin, $ticker, $qty, $price, $date, $assetType);
            
            if ($stmt->execute()) {
                $successMessage = "Position gespeichert! (Ticker: $ticker)";
                $targetAssetId = $db_obj->insert_id;
                // Reset Form
                $name = $isin = $ticker = $qty = $price = $date = ''; 
            } else {
                $errors[] = "DB Fehler: " . $stmt->error;
            }
        }

        // DATEI UPLOAD (Code bleibt gleich, nur asset_attachment Pfad nutzen)
        if (isset($stmt) && empty($errors) && isset($_FILES['asset_file']) && $_FILES['asset_file']['error'] === UPLOAD_ERR_OK) {
            $targetDir = __DIR__ . "/user_uploads/" . $userId . "/asset_attachment/" . $targetAssetId;
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $fileName = basename($_FILES["asset_file"]["name"]);
            move_uploaded_file($_FILES["asset_file"]["tmp_name"], $targetDir . "/" . $fileName);
        }
        if (isset($stmt)) $stmt->close();
    } else {
        $errors = $result['errors'];
    }
} 
// --- C. BEARBEITEN LADEN ---
elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $sql = "SELECT * FROM `assets` WHERE `id` = ? AND `user_id` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("ii", $editId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $asset = $res->fetch_assoc();
    
    if ($asset) {
        $isEditMode = true;
        $currentAssetId = $asset['id'];
        $name   = $asset['name'];
        $isin   = $asset['isin'];
        $ticker = $asset['ticker']; // NEU
        $qty    = $asset['quantity']; 
        $price  = $asset['purchase_price'];
        $date   = $asset['purchase_date'];
    }
    $stmt->close();
}

// LISTE LADEN
$myAssets = [];
$listSql = "SELECT * FROM `assets` WHERE `user_id` = ? ORDER BY `purchase_date` DESC";
$listStmt = $db_obj->prepare($listSql);
$listStmt->bind_param("i", $userId);
$listStmt->execute();
$listResult = $listStmt->get_result();
while ($row = $listResult->fetch_assoc()) {
    $myAssets[] = $row;
}
$listStmt->close();
$db_obj->close(); 
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
                    <h1 class="h3 mb-4"><?= $isEditMode ? 'Position bearbeiten' : 'Aktien verwalten' ?></h1>
                    
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/positions.php" enctype="multipart/form-data">
                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($currentAssetId) ?>">
                        
                        <div class="mb-3">
                            <label for="assetName" class="form-label">Aktienname</label>
                            <input type="text" class="form-control" id="assetName" name="asset_name" value="<?= htmlspecialchars($name) ?>" placeholder="z.B. Apple Inc." required>
                        </div>
                        <div class="mb-3">
                            <label for="assetISIN" class="form-label">ISIN</label>
                            <input type="text" class="form-control" id="assetISIN" name="asset_ISIN" value="<?= htmlspecialchars($isin) ?>" placeholder="US0378331005" required>
                        </div>
                        <div class="mb-3">
                            <label for="assetTicker" class="form-label">Ticker Symbol (Optional)</label>
                            <input type="text" class="form-control" id="assetTicker" name="asset_ticker" value="<?= htmlspecialchars($ticker) ?>" placeholder="AAPL (wird automatisch gesucht, wenn leer)">
                            <div class="form-text">Lassen Sie dieses Feld leer, um den Ticker automatisch anhand der ISIN zu ermitteln.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Anzahl</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars($qty) ?>" step="any" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="purchasePrice" class="form-label">Kaufpreis (€)</label>
                                <input type="number" class="form-control" id="purchasePrice" name="purchase_price" value="<?= htmlspecialchars($price) ?>" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="purchaseDate" class="form-label">Kaufdatum</label>
                            <input type="date" class="form-control" id="purchaseDate" name="purchase_date" value="<?= htmlspecialchars($date) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="assetFile" class="form-label">Dokument (optional)</label>
                            <input class="form-control" type="file" id="assetFile" name="asset_file">
                            <?php if($isEditMode): ?>
                                <small class="text-muted">Lassen Sie dieses Feld leer, um die Datei nicht zu ändern.</small>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between">
                            <?php if ($isEditMode): ?>
                                <a href="/positions.php" class="btn btn-secondary">Abbrechen</a>
                            <?php else: ?>
                                <div></div> <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <?= $isEditMode ? 'Änderungen speichern' : 'Position speichern' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                    <th scope="col">Aktie / Symbol</th>
                                    <th scope="col" class="text-end">Anzahl</th>
                                    <th scope="col" class="text-end">Kaufpreis</th>
                                    <th scope="col" class="text-center">Kaufdatum</th>
                                    <th scope="col" class="text-end">Gesamtwert</th>
                                    <th scope="col" class="text-center">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($myAssets) > 0): ?>
                                    <?php foreach ($myAssets as $asset): ?>
                                        <?php 
                                            $total = $asset['quantity'] * $asset['purchase_price']; 
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($asset['name']) ?></strong><br>
                                                
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($asset['isin']) ?>
                                                    
                                                    <?php if (!empty($asset['ticker'])): ?>
                                                        &bull; <span class="badge bg-secondary"><?= htmlspecialchars($asset['ticker']) ?></span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td class="text-end"><?= number_format($asset['quantity'], 2, ',', '.') ?></td>
                                            <td class="text-end">€<?= number_format($asset['purchase_price'], 2, ',', '.') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($asset['purchase_date']) ?></td>
                                            <td class="text-end">€<?= number_format($total, 2, ',', '.') ?></td>
                                            <td class="text-center">
                                                <a href="/positions.php?action=edit&id=<?= $asset['id'] ?>" 
                                                class="btn btn-sm btn-outline-primary">
                                                Bearbeiten
                                                </a>
                                                
                                                <a href="/positions.php?action=delete&id=<?= $asset['id'] ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Möchten Sie diese Position wirklich löschen?');">
                                                Löschen
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Keine Positionen gefunden.</td>
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

</body>
</html>
