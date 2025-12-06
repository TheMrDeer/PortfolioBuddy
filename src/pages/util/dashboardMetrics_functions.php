<?php


function getCombinedValue(array $assets): float {
    $total = 0.0;
    foreach ($assets as $asset) {
        $total += $asset['quantity'] * $asset['purchase_price'];
    }
    return $total;
}
