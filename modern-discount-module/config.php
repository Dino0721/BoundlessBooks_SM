<?php
/**
 * Database Configuration
 * Centralized configuration following 12-factor app principles
 */

return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'ebookdb',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'validation' => [
        'discount_min' => 0,
        'discount_max' => 100,
        'code_min_length' => 3,
        'code_max_length' => 50,
    ]
];
