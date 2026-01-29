<?php

/* ==========================
   SANITIZE (SAFE)
========================== */
function sanitize($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* ==========================
   CURRENCY SYMBOL (SAFE)
========================== */
function currency_symbol($c = 'INR'): string {

    if ($c === null || $c === '') {
        $c = 'INR';
    }

    return match ($c) {
        'USD' => '$',
        'EUR' => '€',
        'INR' => '₹',
        default => '₹',
    };
}
