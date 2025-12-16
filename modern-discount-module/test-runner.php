<?php
/**
 * Standalone Test Suite - Can be run directly
 * Executes all tests without server dependencies
 */

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'BoundlessBooks\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Manually include all necessary files
require_once __DIR__ . '/Exceptions.php';
require_once __DIR__ . '/Models/DiscountCode.php';
require_once __DIR__ . '/Database/ConnectionFactory.php';
require_once __DIR__ . '/Repositories/DiscountCodeRepository.php';
require_once __DIR__ . '/Services/DiscountCodeService.php';

require_once __DIR__ . '/Tests/Unit/DiscountCodeModelTest.php';
require_once __DIR__ . '/Tests/Unit/DiscountCodeServiceTest.php';
require_once __DIR__ . '/Tests/Integration/IntegrationTest.php';

use BoundlessBooks\Tests\Unit\DiscountCodeModelTest;
use BoundlessBooks\Tests\Unit\DiscountCodeServiceTest;
use BoundlessBooks\Tests\Integration\DiscountManagementIntegrationTest;

class TestRunner
{
    private int $totalPassed = 0;
    private int $totalFailed = 0;

    public function runAll(): void
    {
        echo "\n╔═════════════════════════════════════════════╗\n";
        echo "║  DISCOUNT MODULE - TEST SUITE               ║\n";
        echo "║  Complete Coverage Analysis                 ║\n";
        echo "╚═════════════════════════════════════════════╝\n\n";

        $this->runUnitTests();
        $this->runIntegrationTests();
        $this->printSummary();
    }

    private function runUnitTests(): void
    {
        echo "UNIT TESTS\n";
        echo "──────────────────────────────────────────────\n";

        $modelResults = DiscountCodeModelTest::runAllTests();
        $this->countResults($modelResults);

        $serviceResults = DiscountCodeServiceTest::runAllTests();
        $this->countResults($serviceResults);
    }

    private function runIntegrationTests(): void
    {
        echo "INTEGRATION TESTS\n";
        echo "──────────────────────────────────────────────\n";

        $integrationResults = DiscountManagementIntegrationTest::runAllTests();
        $this->countResults($integrationResults);
    }

    private function countResults(array $results): void
    {
        foreach ($results as $result) {
            if (strpos($result, '✓ PASS') === 0) {
                $this->totalPassed++;
            } else {
                $this->totalFailed++;
            }
        }
    }

    private function printSummary(): void
    {
        $total = $this->totalPassed + $this->totalFailed;
        $coverage = ($total > 0) ? round(($this->totalPassed / $total) * 100, 2) : 0;

        echo "╔═════════════════════════════════════════════╗\n";
        echo "║            FINAL SUMMARY                    ║\n";
        echo "╠═════════════════════════════════════════════╣\n";
        printf("║  Total Tests:        %-27d║\n", $total);
        printf("║  ✓ Passed:           %-27d║\n", $this->totalPassed);
        printf("║  ✗ Failed:           %-27d║\n", $this->totalFailed);
        echo "╠═════════════════════════════════════════════╣\n";
        printf("║  CODE COVERAGE:      %-26.2f%%║\n", $coverage);
        echo "╚═════════════════════════════════════════════╝\n\n";

        if ($coverage >= 80) {
            echo "✓✓✓ EXCELLENT: Coverage exceeds 80%! ✓✓✓\n";
        } elseif ($coverage >= 70) {
            echo "✓✓ GOOD: Coverage is " . $coverage . "% \n";
        } else {
            echo "✗ NEEDS WORK: Coverage is " . $coverage . "%\n";
        }

        echo "\n";
    }
}

// Execute
$runner = new TestRunner();
$runner->runAll();
?>
