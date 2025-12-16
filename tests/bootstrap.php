<?php
declare(strict_types=1);

// Bootstrap file for PHPUnit tests
// Autoload project classes and set up test environment

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

// Load Composer autoloader first
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Simple PSR-4 autoloader for app/ and tests/ directories
spl_autoload_register(function ($class) use ($projectRoot) {
    $prefixes = [
        'App\\' => '/app/',
        'Tests\\' => '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $projectRoot . $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');
