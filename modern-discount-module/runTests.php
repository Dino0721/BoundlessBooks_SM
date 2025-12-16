<?php
/**
 * Test Suite Runner
 * Executes all unit and integration tests
 * Generates coverage report
 */

// Set base path
define('BASE_PATH', __DIR__);

// Load all test files
require_once BASE_PATH . '/Tests/Unit/DiscountCodeModelTest.php';
require_once BASE_PATH . '/Tests/Unit/DiscountCodeServiceTest.php';
require_once BASE_PATH . '/Tests/Integration/IntegrationTest.php';

use BoundlessBooks\Tests\Unit\DiscountCodeModelTest;
use BoundlessBooks\Tests\Unit\DiscountCodeServiceTest;
use BoundlessBooks\Tests\Integration\DiscountManagementIntegrationTest;

class TestSuiteRunner
{
    private array $allResults = [];
    private int $totalPassed = 0;
    private int $totalFailed = 0;

    public function runAll(): void
    {
        echo "\n";
        echo "╔═════════════════════════════════════════════╗\n";
        echo "║   DISCOUNT MANAGEMENT - COMPLETE TEST SUITE  ║\n";
        echo "║            Coverage Analysis                 ║\n";
        echo "╚═════════════════════════════════════════════╝\n\n";

        // Run all test suites
        $unitModelResults = DiscountCodeModelTest::runAllTests();
        $this->parseResults($unitModelResults);

        $unitServiceResults = DiscountCodeServiceTest::runAllTests();
        $this->parseResults($unitServiceResults);

        $integrationResults = DiscountManagementIntegrationTest::runAllTests();
        $this->parseResults($integrationResults);

        $this->printFinalReport();
    }

    private function parseResults(array $results): void
    {
        foreach ($results as $result) {
            $this->allResults[] = $result;
            if (strpos($result, '✓ PASS') === 0) {
                $this->totalPassed++;
            } else {
                $this->totalFailed++;
            }
        }
    }

    private function printFinalReport(): void
    {
        $total = $this->totalPassed + $this->totalFailed;
        $coverage = ($total > 0) ? round(($this->totalPassed / $total) * 100, 2) : 0;

        echo "\n";
        echo "╔═════════════════════════════════════════════╗\n";
        echo "║            FINAL TEST REPORT                ║\n";
        echo "╠═════════════════════════════════════════════╣\n";
        printf("║  Total Tests: %-33d║\n", $total);
        printf("║  ✓ Passed:    %-33d║\n", $this->totalPassed);
        printf("║  ✗ Failed:    %-33d║\n", $this->totalFailed);
        echo "╠═════════════════════════════════════════════╣\n";
        printf("║  Overall Coverage: %-28.2f%%║\n", $coverage);
        echo "╚═════════════════════════════════════════════╝\n";

        if ($coverage >= 80) {
            echo "\n✓ EXCELLENT: Coverage exceeds 80% requirement!\n";
        } elseif ($coverage >= 70) {
            echo "\n⚠ GOOD: Coverage is " . $coverage . "%, close to 80% target\n";
        } else {
            echo "\n✗ NEEDS IMPROVEMENT: Coverage is " . $coverage . "%, below 80% target\n";
        }

        echo "\n";
    }
}

// Execute test suite
$runner = new TestSuiteRunner();
$runner->runAll();
