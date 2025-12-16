#!/usr/bin/env php
<?php
/**
 * Test Runner Script
 * Usage: php run-tests.php
 * 
 * This script demonstrates how to run PHPUnit tests programmatically
 * and display results in a formatted way
 */

use PHPUnit\TextUI\Application;

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         BoundlessBooks Module Test Suite Runner            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$projectRoot = __DIR__;
$vendorAutoload = $projectRoot . '/vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    echo "❌ ERROR: Composer dependencies not installed.\n";
    echo "   Run: php composer.phar install\n";
    exit(1);
}

require_once $vendorAutoload;

echo "📦 Environment Check:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Project Root: " . $projectRoot . "\n";
echo "   Working Directory: " . getcwd() . "\n\n";

// Define test suites
$suites = [
    'unit' => [
        'label' => 'Unit Tests',
        'dir' => 'tests/Unit',
        'description' => 'Model and Service layer tests'
    ],
    'integration' => [
        'label' => 'Integration Tests',
        'dir' => 'tests/Integration',
        'description' => 'End-to-end workflow tests'
    ]
];

echo "🧪 Available Test Suites:\n";
foreach ($suites as $key => $suite) {
    $dir = $projectRoot . '/' . $suite['dir'];
    $exists = is_dir($dir);
    $status = $exists ? "✓" : "✗";
    echo "   $status [{$suite['label']}] {$suite['description']}\n";
}

echo "\n";

// Parse command line arguments
$args = $_SERVER['argv'];
array_shift($args); // Remove script name

$runAll = empty($args);
$requestedSuite = $runAll ? null : $args[0];

echo "🚀 Running Tests...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Build PHPUnit command
$command = [
    PHP_BINARY,
    'vendor/bin/phpunit',
    '--configuration', 'phpunit.xml',
    '--coverage-text',
    '--colors=auto',
    '--verbose'
];

if ($requestedSuite && isset($suites[$requestedSuite])) {
    $command[] = '--testsuite';
    $command[] = $requestedSuite;
}

// Run PHPUnit
passthru(implode(' ', array_map('escapeshellarg', $command)), $exitCode);

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($exitCode === 0) {
    echo "✅ All tests passed!\n";
} else {
    echo "❌ Some tests failed (exit code: $exitCode)\n";
}

echo "\n📊 To view coverage report:\n";
echo "   start coverage/html/index.html\n";

exit($exitCode);
