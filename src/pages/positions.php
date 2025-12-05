<?php
session_start();

// 1. Alle notwendigen Dateien einbinden
require_once __DIR__ . '/includes/dbaccess.php';          
require_once __DIR__ . '/util/utils.php';                 
require_once __DIR__ . '/util/positions_functions.php';   

// Login-Check
if (!isset($_SESSION['user'])) {
    header('Location: /PortfolioBuddy/login.php');
    exit;
}

// Verbindung für die gesamte Seite öffnen
$db_obj = new mysqli($host, $user, $pass, $db);
if ($db_obj->connect_error) {
    die("Verbindungsfehler: " . $db_obj->connect_error);
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$userId = $_SESSION['user']['id'];

// Initialisierung der Variablen (leer für "Neu erstellen")
$errors = [];
$successMessage = '';

$currentAssetId = ''; // ID der Aktie, die gerade bearbeitet wird (leer = neu)
$name = '';
$isin = '';
$qty  = '';
$price = '';
$date = '';
$isEditMode = false; // Steuert Text auf dem Button (Speichern vs Aktualisieren)

// --- A. LÖSCHEN (DELETE) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delSql = "DELETE FROM `assets` WHERE `id` = ? AND `user_id` = ?";
    $delStmt = $db_obj->prepare($delSql);
    $delId = (int)$_GET['id'];
    
    $delStmt->bind_param("ii", $delId, $userId);
    
    if ($delStmt->execute()) {
        $successMessage = "Position erfolgreich gelöscht.";
    } else {
        $errors[] = "Fehler beim Löschen: " . $delStmt->error;
    }
    $delStmt->close();
}

// --- B. FORMULAR ABSENDEN (INSERT ODER UPDATE) ---
if ($isPost) {
    $result = validate_asset_input($_POST, $_FILES);
    
    // ID aus dem versteckten Feld holen (leer = neu, Zahl = update)
    $currentAssetId = $_POST['asset_id'] ?? ''; 

    // Bei Fehler: Eingaben behalten, damit User nicht neu tippen muss
    $name  = $result['data']['name'] ?? '';
    $isin  = $result['data']['isin'] ?? '';
    $date  = $result['data']['date'] ?? '';
    $qty   = htmlspecialchars($_POST['quantity'] ?? '', ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars($_POST['purchase_price'] ?? '', ENT_QUOTES, 'UTF-8');
    
    // Wenn ID vorhanden ist, sind wir im Edit-Modus (auch wenn Fehler passieren)
    if (!empty($currentAssetId)) {
        $isEditMode = true;
    }

    if ($result['success']) {
        $valQty = $result['data']['quantity'];
        $valPrice = $result['data']['price'];
        $assetType = 'stock';

        if ($isEditMode) {
            // --- UPDATE (Bestehende ändern) ---
            $sql = "UPDATE `assets` SET `name`=?, `isin`=?, `quantity`=?, `purchase_price`=?, `purchase_date`=? WHERE `id`=? AND `user_id`=?";
            $stmt = $db_obj->prepare($sql);
            // Parameter: sddsii (string, string, double, double, string, int, int)
            $stmt->bind_param("ssddsii", $name, $isin, $valQty, $valPrice, $date, $currentAssetId, $userId);
            
            if ($stmt->execute()) {
                $successMessage = "Position erfolgreich aktualisiert!";
                $targetAssetId = $currentAssetId; // Für Datei-Upload
                // Nach Update Formular leeren (oder man könnte im Edit-Modus bleiben)
                $name = $isin = $qty = $price = $date = $currentAssetId = ''; 
                $isEditMode = false;
            } else {
                $errors[] = "Datenbankfehler beim Update: " . $stmt->error;
            }
        } else {
            // --- INSERT (Neu erstellen) ---
            $sql = "INSERT INTO `assets` (`user_id`, `name`, `isin`, `quantity`, `purchase_price`, `purchase_date`, `asset_type`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db_obj->prepare($sql);
            // Parameter: issddss
            $stmt->bind_param("issddss", $userId, $name, $isin, $valQty, $valPrice, $date, $assetType);
            
            if ($stmt->execute()) {
                $successMessage = "Position erfolgreich gespeichert!";
                $targetAssetId = $db_obj->insert_id; // Neue ID für Upload
                // Formular leeren
                $name = $isin = $qty = $price = $date = ''; 
            } else {
                $errors[] = "Datenbankfehler beim Speichern: " . $stmt->error;
            }
        }

        // --- DATEI UPLOAD (Gemeinsam für Update & Insert) ---
        if (isset($stmt) && empty($errors) && isset($_FILES['asset_file']) && $_FILES['asset_file']['error'] === UPLOAD_ERR_OK) {
            $targetDir = __DIR__ . "/user_uploads/" . $userId . "/asset_attachment/" . $targetAssetId;
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            
            $fileName = basename($_FILES["asset_file"]["name"]);
            $targetFilePath = $targetDir . "/" . $fileName;
            move_uploaded_file($_FILES["asset_file"]["tmp_name"], $targetFilePath);
        }
        
        if (isset($stmt)) $stmt->close();
    } else {
        $errors = $result['errors'];
    }
} 
// --- C. BEARBEITEN VORBEREITEN (GET) ---
// Wenn kein POST, aber action=edit, dann Daten aus DB laden
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
        $name = $asset['name'];
        $isin = $asset['isin'];
        // Wichtig: float values für das Input-Feld formatieren (oder raw lassen)
        $qty  = $asset['quantity']; 
        $price = $asset['purchase_price'];
        $date = $asset['purchase_date'];
    }
    $stmt->close();
}

// --- D. LISTE LADEN (IMMER) ---
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

                    <form method="post" action="/PortfolioBuddy/positions.php" enctype="multipart/form-data">
                        <input type="hidden" name="asset_id" value="<?= htmlspecialchars($currentAssetId) ?>">
                        
                        <div class="mb-3">
                            <label for="assetName" class="form-label">Aktienname</label>
                            <input type="text" class="form-control" id="assetName" name="asset_name" value="<?= htmlspecialchars($name) ?>" placeholder="z.B. Apple Inc." required>
                        </div>
                        <div class="mb-3">
                            <label for="assetISIN" class="form-label">ISIN</label>
                            <input type="text" class="form-control" id="assetISIN" name="asset_ISIN" value="<?= htmlspecialchars($isin) ?>" placeholder="US0378331005" required>
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
                                <a href="/PortfolioBuddy/positions.php" class="btn btn-secondary">Abbrechen</a>
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
                                    <th scope="col">Aktienname</th>
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
                                                <small class="text-muted"><?= htmlspecialchars($asset['isin']) ?></small>
                                            </td>
                                            <td class="text-end"><?= number_format($asset['quantity'], 2, ',', '.') ?></td>
                                            <td class="text-end">€<?= number_format($asset['purchase_price'], 2, ',', '.') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($asset['purchase_date']) ?></td>
                                            <td class="text-end">€<?= number_format($total, 2, ',', '.') ?></td>
                                            <td class="text-center">
                                                <a href="/PortfolioBuddy/positions.php?action=edit&id=<?= $asset['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                   Bearbeiten
                                                </a>
                                                
                                                <a href="/PortfolioBuddy/positions.php?action=delete&id=<?= $asset['id'] ?>" 
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