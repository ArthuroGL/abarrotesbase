<?php

return [
    'currency' => env('ABARROTESBASE_CURRENCY', 'MXN'),
    'timezone' => env('ABARROTESBASE_TIMEZONE', 'America/Mexico_City'),
    'inventory' => [
        'cost_method' => env('ABARROTESBASE_COST_METHOD', 'weighted_average'),
        'allow_negative_stock' => (bool) env('ABARROTESBASE_ALLOW_NEGATIVE_STOCK', false),
    ],
    'cash' => [
        'one_open_session_per_register' => true,
    ],
];
