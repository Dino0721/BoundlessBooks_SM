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
        'book_name_min_length' => 1,
        'book_name_max_length' => 255,
        'price_min' => 0.00,
        'price_max' => 999999.99,
    ],
    'file' => [
        'max_download_size' => 100 * 1024 * 1024, // 100MB
        'allowed_extensions' => ['pdf'],
    ]
];
