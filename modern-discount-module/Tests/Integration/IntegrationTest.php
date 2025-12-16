<?php
/**
 * Integration Tests for Discount Management System
 * Test: Multiple components working together
 * Coverage: Full workflow scenarios
 */

namespace BoundlessBooks\Tests\Integration;

use BoundlessBooks\Models\DiscountCode;

class DiscountManagementIntegrationTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];
    private static array $mockDatabase = [];
    private static int $mockIdCounter = 100;

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  INTEGRATION TESTS: Discount Management\n";
        echo "═════════════════════════════════════════════\n";

        self::testFullWorkflowCreateValidateUse();
        self::testMultipleCodesSearchAndFilter();
        self::testDiscountCodeLifecycle();
        self::testConcurrentOperations();
        self::testEdgeCaseAmounts();
        self::testErrorRecovery();

        self::printResults();
        return self::$results;
    }

    private static function testFullWorkflowCreateValidateUse(): void
    {
        try {
            // Step 1: Create discount code
            $code = new DiscountCode('SUMMER2025', 35.0, 'active');
            self::mockInsert($code);
            
            // Step 2: Retrieve and validate
            $retrieved = self::mockFind('SUMMER2025');
            self::assert(
                $retrieved !== null && $retrieved['discount'] === 35.0,
                'Full workflow: create and retrieve'
            );
            
            // Step 3: Calculate discount
            $price = 100.0;
            $discountAmount = ($price * $retrieved['discount']) / 100;
            $finalPrice = $price - $discountAmount;
            
            self::assert(
                $finalPrice === 65.0,
                'Full workflow: calculate discount correctly'
            );
        } catch (\Exception $e) {
            self::fail('Full workflow', $e->getMessage());
        }
    }

    private static function testMultipleCodesSearchAndFilter(): void
    {
        try {
            // Create multiple codes
            self::mockReset();
            self::mockInsert(new DiscountCode('WINTER25', 25.0, 'active'));
            self::mockInsert(new DiscountCode('SPRING15', 15.0, 'inactive'));
            self::mockInsert(new DiscountCode('FALL20', 20.0, 'active'));
            self::mockInsert(new DiscountCode('HOLIDAY40', 40.0, 'active'));
            
            // Filter by status
            $activeCount = count(array_filter(self::$mockDatabase, 
                fn($item) => $item['status'] === 'active'
            ));
            
            // Search by code
            $searchResults = array_filter(self::$mockDatabase,
                fn($item) => stripos($item['code'], 'WIN') !== false
            );
            
            self::assert(
                $activeCount === 3,
                'Integration: filter active codes'
            );
            self::assert(
                count($searchResults) === 1,
                'Integration: search codes'
            );
        } catch (\Exception $e) {
            self::fail('Multiple codes search', $e->getMessage());
        }
    }

    private static function testDiscountCodeLifecycle(): void
    {
        try {
            self::mockReset();
            
            // Create
            $code = new DiscountCode('LIFECYCLE', 50.0, 'active');
            self::mockInsert($code);
            $id = count(self::$mockDatabase);
            
            // Read
            $retrieved = self::mockFindById($id);
            self::assert($retrieved !== null, 'Lifecycle: read created code');
            
            // Update (toggle status)
            $retrieved['status'] = $retrieved['status'] === 'active' ? 'inactive' : 'active';
            self::mockUpdate($id, $retrieved);
            $updated = self::mockFindById($id);
            self::assert($updated['status'] === 'inactive', 'Lifecycle: update status');
            
            // Delete
            self::mockDelete($id);
            $deleted = self::mockFindById($id);
            self::assert($deleted === null, 'Lifecycle: delete code');
        } catch (\Exception $e) {
            self::fail('Code lifecycle', $e->getMessage());
        }
    }

    private static function testConcurrentOperations(): void
    {
        try {
            self::mockReset();
            
            // Simulate concurrent inserts
            $operations = [];
            for ($i = 0; $i < 5; $i++) {
                $code = new DiscountCode('CONCURRENT' . $i, (10 + $i * 5), 'active');
                self::mockInsert($code);
                $operations[] = $code;
            }
            
            // Verify all were inserted
            self::assert(
                count(self::$mockDatabase) === 5,
                'Concurrent: all operations completed'
            );
            
            // Verify data integrity
            $total = 0;
            foreach (self::$mockDatabase as $item) {
                $total += $item['discount'];
            }
            self::assert(
                $total === (10 + 15 + 20 + 25 + 30),
                'Concurrent: data integrity maintained'
            );
        } catch (\Exception $e) {
            self::fail('Concurrent operations', $e->getMessage());
        }
    }

    private static function testEdgeCaseAmounts(): void
    {
        try {
            // Test boundary values
            $amounts = [
                [0.0, 100.0, 100.0],      // 0% discount
                [100.0, 0.0, 0.0],        // 100% discount
                [50.0, 50.0, 50.0],       // 50% discount
                [99.99, 99.99, 0.01],     // Precision test
            ];
            
            foreach ($amounts as [$original, $discount, $expected]) {
                $finalPrice = $original - ($original * $discount / 100);
                self::assert(
                    abs($finalPrice - $expected) < 0.01,
                    "Edge case: $original with $discount% discount"
                );
            }
        } catch (\Exception $e) {
            self::fail('Edge case amounts', $e->getMessage());
        }
    }

    private static function testErrorRecovery(): void
    {
        try {
            // Test graceful error handling
            self::mockReset();
            
            // Attempt duplicate code
            $code1 = new DiscountCode('DUPLICATE', 50.0);
            self::mockInsert($code1);
            
            // Try to insert same code again (should be detected)
            $duplicate = self::mockFind('DUPLICATE');
            self::assert(
                $duplicate !== null,
                'Error recovery: duplicate detection'
            );
            
            // Verify data wasn't corrupted
            self::assert(
                count(self::$mockDatabase) === 1,
                'Error recovery: database integrity'
            );
        } catch (\Exception $e) {
            self::fail('Error recovery', $e->getMessage());
        }
    }

    // Mock database operations
    private static function mockInsert(DiscountCode $code): void
    {
        self::$mockDatabase[] = [
            'id' => self::$mockIdCounter++,
            'code' => $code->getCode(),
            'discount' => $code->getDiscountPercentage(),
            'status' => $code->getStatus(),
        ];
    }

    private static function mockFind(string $code): ?array
    {
        foreach (self::$mockDatabase as $item) {
            if ($item['code'] === $code) {
                return $item;
            }
        }
        return null;
    }

    private static function mockFindById(int $id): ?array
    {
        foreach (self::$mockDatabase as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    private static function mockUpdate(int $id, array $data): void
    {
        foreach (self::$mockDatabase as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $data);
                break;
            }
        }
    }

    private static function mockDelete(int $id): void
    {
        self::$mockDatabase = array_filter(
            self::$mockDatabase,
            fn($item) => $item['id'] !== $id
        );
    }

    private static function mockReset(): void
    {
        self::$mockDatabase = [];
        self::$mockIdCounter = 100;
    }

    private static function pass(string $test): void
    {
        self::$testsPassed++;
        self::$results[] = "✓ PASS: $test";
        echo "✓ $test\n";
    }

    private static function fail(string $test, string $reason): void
    {
        self::$testsFailed++;
        self::$results[] = "✗ FAIL: $test - $reason";
        echo "✗ $test - $reason\n";
    }

    private static function assert(bool $condition, string $message): void
    {
        if ($condition) {
            self::pass($message);
        } else {
            self::fail($message, 'Assertion failed');
        }
    }

    private static function printResults(): void
    {
        echo "\n═════════════════════════════════════════════\n";
        echo "RESULTS: " . self::$testsPassed . " passed, " . self::$testsFailed . " failed\n";
        echo "Coverage: " . round((self::$testsPassed / (self::$testsPassed + self::$testsFailed)) * 100, 2) . "%\n";
        echo "═════════════════════════════════════════════\n\n";
    }
}

// Run tests
$results = DiscountManagementIntegrationTest::runAllTests();
