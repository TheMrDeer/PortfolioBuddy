<?php

/**
 * Validates the input for adding/editing a position/asset.
 * Accepts $_POST and optionally $_FILES.
 */
function validate_asset_input(array $post, array $files = []): array
{
    $errors = [];

    // Wir nutzen deine get_field helper funktion (muss im aufrufenden Skript via utils.php eingebunden sein)
    $name = get_field($post, 'asset_name');
    $isin = get_field($post, 'asset_ISIN');
    $quantity = get_field($post, 'quantity');
    $price = get_field($post, 'purchase_price');
    $date = get_field($post, 'purchase_date');
    $assetId = get_field($post, 'asset_id'); // Optional, falls wir bearbeiten

    // 1. Asset Name
    if ($name === '') {
        $errors[] = 'Aktienname ist erforderlich.';
    } elseif (strlen($name) > 150) {
        $errors[] = 'Aktienname ist zu lang (max 150 Zeichen).';
    }

    // 2. ISIN Validation (12 Zeichen, Alphanumerisch)
    $isin = strtoupper($isin); // ISINs sind üblicherweise Großbuchstaben
    if ($isin === '') {
        $errors[] = 'ISIN ist erforderlich.';
    } elseif (!preg_match('/^[A-Z0-9]{12}$/', $isin)) {
        $errors[] = 'ISIN muss aus genau 12 alphanumerischen Zeichen bestehen.';
    }

    // 3. Quantity (Muss eine Zahl und > 0 sein)
    if ($quantity === '') {
        $errors[] = 'Anzahl ist erforderlich.';
    } elseif (!is_numeric($quantity)) {
        $errors[] = 'Anzahl muss eine Zahl sein.';
    } elseif ((float)$quantity <= 0) {
        $errors[] = 'Anzahl muss größer als 0 sein.';
    }

    // 4. Purchase Price (Muss eine Zahl sein)
    if ($price === '') {
        $errors[] = 'Kaufpreis ist erforderlich.';
    } elseif (!is_numeric($price)) {
        $errors[] = 'Kaufpreis muss eine Zahl sein.';
    } elseif ((float)$price < 0) {
        $errors[] = 'Kaufpreis kann nicht negativ sein.';
    }

    // 5. Purchase Date (Prüfung auf gültiges Datumsformat YYYY-MM-DD)
    if ($date === '') {
        $errors[] = 'Kaufdatum ist erforderlich.';
    } else {
        // Validiert, ob das Datum wirklich existiert (z.B. verhindert 30. Februar)
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!($d && $d->format('Y-m-d') === $date)) {
            $errors[] = 'Ungültiges Datumsformat.';
        }
    }

    // 6. File Upload Validation (Optional)
    // Laut PDF Seite 6: "The platform is going to allow uploads of JPG images and PDF files."
    if (isset($files['asset_file']) && $files['asset_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $files['asset_file'];
        
        // Prüfen auf Upload-Fehler (z.B. Datei zu groß für php.ini Settings)
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Fehler beim Hochladen der Datei (Code: ' . $file['error'] . ').';
        } else {
            // Dateigröße prüfen (z.B. max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Datei ist zu groß (Max 5MB).';
            }

            // Dateityp prüfen (MIME-Type)
            // Wir verlassen uns nicht auf die Endung (.jpg), sondern prüfen den echten Inhalt
            $allowedMimes = ['image/jpeg', 'image/jpg', 'application/pdf'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowedMimes)) {
                $errors[] = 'Nur JPG Bilder und PDF Dateien sind erlaubt.';
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
            'quantity' => (float)$quantity, // Als Float zurückgeben für DB
            'price' => (float)$price,       // Als Float zurückgeben für DB
            'date' => $date
        ]
    ];
}