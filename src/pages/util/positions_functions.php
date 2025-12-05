<?php

/**
 * Validates the input for adding/editing a position/asset.
 * Accepts $_POST and optionally $_FILES.
 */
function validate_asset_input(array $post, array $files = []): array
{
    $errors = [];

    // Helper function aus utils.php
    $name = get_field($post, 'asset_name');
    $isin = get_field($post, 'asset_ISIN');
    $ticker = get_field($post, 'asset_ticker'); // NEU
    $quantity = get_field($post, 'quantity');
    $price = get_field($post, 'purchase_price');
    $date = get_field($post, 'purchase_date');
    $assetId = get_field($post, 'asset_id');

    // 1. Asset Name
    if ($name === '') {
        $errors[] = 'Aktienname ist erforderlich.';
    } elseif (strlen($name) > 150) {
        $errors[] = 'Aktienname ist zu lang (max 150 Zeichen).';
    }

    // 2. ISIN Validation
    $isin = strtoupper($isin);
    if ($isin === '') {
        $errors[] = 'ISIN ist erforderlich.';
    } elseif (!preg_match('/^[A-Z0-9]{12}$/', $isin)) {
        $errors[] = 'ISIN muss aus genau 12 alphanumerischen Zeichen bestehen.';
    }

    // 3. Ticker Validation (NEU - Optional, da oft automatisch befüllt)
    if (strlen($ticker) > 20) {
        $errors[] = 'Ticker-Symbol ist zu lang (max 20 Zeichen).';
    }

    // 4. Quantity
    if ($quantity === '') {
        $errors[] = 'Anzahl ist erforderlich.';
    } elseif (!is_numeric($quantity)) {
        $errors[] = 'Anzahl muss eine Zahl sein.';
    } elseif ((float)$quantity <= 0) {
        $errors[] = 'Anzahl muss größer als 0 sein.';
    }

    // 5. Purchase Price
    if ($price === '') {
        $errors[] = 'Kaufpreis ist erforderlich.';
    } elseif (!is_numeric($price)) {
        $errors[] = 'Kaufpreis muss eine Zahl sein.';
    } elseif ((float)$price < 0) {
        $errors[] = 'Kaufpreis kann nicht negativ sein.';
    }

    // 6. Date
    if ($date === '') {
        $errors[] = 'Kaufdatum ist erforderlich.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!($d && $d->format('Y-m-d') === $date)) {
            $errors[] = 'Ungültiges Datumsformat.';
        }
    }

    // 7. File Upload (Unverändert)
    if (isset($files['asset_file']) && $files['asset_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $files['asset_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Fehler beim Hochladen (Code: ' . $file['error'] . ').';
        } else {
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Datei ist zu groß (Max 5MB).';
            }
            $allowedMimes = ['image/jpeg', 'image/jpg', 'application/pdf'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, $allowedMimes)) {
                $errors[] = 'Nur JPG und PDF erlaubt.';
            }
        }
    }

    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => [
            'asset_id' => $assetId,
            'name' => $name,
            'isin' => $isin,
            'ticker' => strtoupper($ticker), // NEU: Immer Großschreiben
            'quantity' => (float)$quantity,
            'price' => (float)$price,
            'date' => $date
        ]
    ];
}
?>