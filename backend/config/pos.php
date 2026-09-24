<?php

// Konfiguracion sa POS system
return [

    // Ngalan sa restaurant
    'restaurant_name' => env('RESTAURANT_NAME', 'RestoPos'),

    // Default currency
    'currency' => env('CURRENCY', 'PHP'),

    // Receipt header text
    'receipt_header' => env('RECEIPT_HEADER', 'Thank you for your order!'),

    // Mga pwedeng roles
    'roles' => [
        'admin',
        'manager',
        'cashier',
    ],

    // Mga pwedeng status sa order
    'order_statuses' => [
        'pending',
        'preparing',
        'served',
        'cancelled',
    ],
];
