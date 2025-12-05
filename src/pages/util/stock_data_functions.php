<?php

/* --------------------------------------------------------------------------
 * INTERNAL HELPER: HTTP REQUESTS
 * -------------------------------------------------------------------------- */

/**
 * Führt einen Request aus. Nutzt cURL, um robust gegen Blockierungen zu sein.
 * Funktioniert für Yahoo (benötigt User-Agent) und Frankfurter API.
 */
function fetch_url_content(string $url): ?array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Für lokale Tests (XAMPP/MAMP)
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Max 5 Sekunden warten
    // WICHTIG: Yahoo blockt oft Anfragen ohne Browser-Kennung (User-Agent)
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return null; // Fehler oder keine Daten
    }

    return json_decode($response, true);
}


/* --------------------------------------------------------------------------
 * 1. WÄHRUNGEN (via Frankfurter API - viel einfacher!)
 * -------------------------------------------------------------------------- */

/**
 * Holt den aktuellen Wechselkurs von EUR zu USD.
 * Quelle: Frankfurter API (EZB Daten).
 * * @return float Der Kurs (z.B. 1.08 bedeutet 1€ = 1.08$)
 */
function get_eur_usd_rate(): float {
    // Session-Cache prüfen, um die API nicht bei jedem Seitenaufruf zu hämmern
    if (isset($_SESSION['eur_usd_rate']) && (time() - $_SESSION['eur_usd_ts'] < 3600)) {
        return $_SESSION['eur_usd_rate']; // 1 Stunde Cache
    }

    $url = "https://api.frankfurter.app/latest?from=EUR&to=USD";
    $data = fetch_url_content($url);

    if (isset($data['rates']['USD'])) {
        $rate = (float)$data['rates']['USD'];
        
        // In Session cachen
        $_SESSION['eur_usd_rate'] = $rate;
        $_SESSION['eur_usd_ts'] = time();
        
        return $rate;
    }

    return 1.10; // Fallback-Wert, falls API down ist (sicherer als Absturz)
}


/* --------------------------------------------------------------------------
 * 2. SUCHE & ISIN RESOLVER (via Yahoo Query API)
 * -------------------------------------------------------------------------- */

/**
 * Sucht nach einem Ticker anhand der ISIN.
 * Bsp: "US0378331005" -> "AAPL"
 */
function get_ticker_from_isin(string $isin): ?string {
    $query = urlencode($isin);
    // Yahoo Search API - findet ISINs sehr zuverlässig
    $url = "https://query2.finance.yahoo.com/v1/finance/search?q={$query}&quotesCount=1";
    
    $data = fetch_url_content($url);

    if (!empty($data['quotes'][0]['symbol'])) {
        return $data['quotes'][0]['symbol'];
    }
    
    return null; // Nichts gefunden
}

/**
 * Sucht nach einem Ticker anhand des Namens.
 * Bsp: "Microsoft" -> "MSFT"
 */
function get_ticker_from_name(string $name): ?string {
    $query = urlencode($name);
    $url = "https://query2.finance.yahoo.com/v1/finance/search?q={$query}&quotesCount=1";
    
    $data = fetch_url_content($url);

    if (!empty($data['quotes'][0]['symbol'])) {
        return $data['quotes'][0]['symbol'];
    }
    return null;
}


/* --------------------------------------------------------------------------
 * 3. AKTIENKURSE & DATEN (via Yahoo Chart API v8)
 * -------------------------------------------------------------------------- */

/**
 * Holt den aktuellen Preis und Metadaten für einen Ticker.
 * @param string $ticker Das Symbol (z.B. "AAPL" oder "SIE.DE")
 */
function get_current_price_data(string $ticker): ?array {
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval=1d&range=1d";
    $data = fetch_url_content($url);

    if (!isset($data['chart']['result'][0]['meta'])) {
        return null;
    }

    $meta = $data['chart']['result'][0]['meta'];

    return [
        'ticker'   => $ticker,
        'price'    => $meta['regularMarketPrice'] ?? 0.0,
        'currency' => $meta['currency'] ?? 'USD', // USD, EUR, etc.
        'name'     => $meta['shortName'] ?? $ticker // Firmenname
    ];
}


/* --------------------------------------------------------------------------
 * 4. HISTORISCHE DATEN (für Charts)
 * -------------------------------------------------------------------------- */

/**
 * Holt historische Schlusskurse für Charts.
 * @param string $range Zeitraum: '1mo', '3mo', '6mo', '1y', 'ytd', 'max'
 */
function get_historical_data(string $ticker, string $range = '1mo'): array {
    // Intervall optimieren: Bei 1 Jahr (1y) nehmen wir Wochendaten (1wk), sonst Tagesdaten (1d)
    $interval = ($range === '1y' || $range === '5y') ? '1wk' : '1d';
    
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval={$interval}&range={$range}";
    $json = fetch_url_content($url);

    if (!isset($json['chart']['result'][0]['timestamp'])) {
        return [];
    }

    $timestamps = $json['chart']['result'][0]['timestamp'];
    $quotes = $json['chart']['result'][0]['indicators']['quote'][0]['close'];

    $history = [];
    $count = count($timestamps);

    for ($i = 0; $i < $count; $i++) {
        // Nur hinzufügen, wenn ein Preis existiert (null bei Feiertagen filtern)
        if (isset($quotes[$i])) {
            $history[] = [
                'date'  => date('Y-m-d', $timestamps[$i]), // Datum lesbar machen
                'price' => round($quotes[$i], 2)
            ];
        }
    }

    return $history;
}


/* --------------------------------------------------------------------------
 * 5. CALCULATOR (Alles zusammenführen)
 * -------------------------------------------------------------------------- */

/**
 * Berechnet den Wert einer Position in EUR und USD.
 * * @param float $quantity Anzahl der Aktien
 * @param float $currentPrice Aktueller Einzelpreis der Aktie
 * @param string $assetCurrency Die Währung der Aktie ('EUR' oder 'USD')
 * * @return array ['EUR' => 120.50, 'USD' => 130.10]
 */
function calculate_position_values(float $quantity, float $currentPrice, string $assetCurrency): array {
    $rateEurToUsd = get_eur_usd_rate(); // z.B. 1.08
    
    $totalValueNative = $quantity * $currentPrice;
    
    $values = [
        'EUR' => 0.0,
        'USD' => 0.0
    ];

    if ($assetCurrency === 'EUR') {
        $values['EUR'] = $totalValueNative;
        $values['USD'] = $totalValueNative * $rateEurToUsd;
    } 
    elseif ($assetCurrency === 'USD') {
        $values['EUR'] = $totalValueNative / $rateEurToUsd;
        $values['USD'] = $totalValueNative;
    } 
    else {
        // Fallback für andere Währungen (einfach 1:1 lassen oder erweitern)
        $values['EUR'] = $totalValueNative;
        $values['USD'] = $totalValueNative;
    }

    return $values;
}
/**
 * Kombiniert ISIN-Suche und Preisabfrage in einem Schritt.
 * Wird von positions.php benötigt.
 */
function get_data_from_isin(string $isin): ?array {
    // 1. Erst den Ticker zur ISIN finden
    $ticker = get_ticker_from_isin($isin);
    
    if (!$ticker) {
        return null; // Nichts gefunden
    }

    // 2. Dann die Details (Preis, Name) zum Ticker holen
    $data = get_current_price_data($ticker);
    
    // Falls Preisabfrage fehlschlägt (z.B. API Fehler), geben wir zumindest den Ticker zurück
    if (!$data) {
        return ['ticker' => $ticker];
    }
    
    return $data;
}
?>