<?php

/* API HELPER
 * -------------------------------------------------------------------------- */

/**
 * Holt JSON von einer URL und gibt ein Array zuruck.
 */
function fetch_url_content(string $url): array {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\n"
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/* ISIN -> TICKER (Yahoo Search)
 * -------------------------------------------------------------------------- */

/**
 * Holt den Ticker (und optional den Namen) uber ISIN.
 */
function get_data_from_isin(string $isin): array {
    $isin = strtoupper(trim($isin));
    if ($isin === '') {
        return [];
    }

    $url = 'https://query1.finance.yahoo.com/v1/finance/search?q=' . urlencode($isin) . '&quotesCount=1&newsCount=0';
    $json = fetch_url_content($url);

    if (!isset($json['quotes'][0])) {
        return [];
    }

    $quote = $json['quotes'][0];
    $ticker = $quote['symbol'] ?? '';
    if ($ticker === '') {
        return [];
    }

    $name = $quote['shortname'] ?? ($quote['longname'] ?? '');

    return [
        'ticker' => $ticker,
        'name' => $name
    ];
}

/* HISTORISCHE DATEN (für Charts)
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
