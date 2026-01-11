<?php

/**
 * Summiert den Portfolio-Wert basierend auf Menge und Kaufpreis pro Asset.
 */
function getNetWorth(array $assets): float
{
    $total = 0.0;
    foreach ($assets as $asset) {
        $qty = (float)($asset['quantity'] ?? 0);
        $price = (float)($asset['purchase_price'] ?? 0);
        $total += $qty * $price;
    }
    return $total;
}
