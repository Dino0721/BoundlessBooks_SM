<?php
/**
 * Unit Tests for DiscountCode Model
 * Test: Model validation and behavior
 * Coverage: Setter/Getter methods, validation logic
 */

namespace BoundlessBooks\Tests\Unit;

use BoundlessBooks\Models\DiscountCode;
use BoundlessBooks\Exceptions\DiscountValidationException;

class DiscountCodeModelTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  UNIT TESTS: DiscountCode Model\n";
        echo "═════════════════════════════════════════════\n";

        self::testValidCodeCreation();
        self::testInvalidCodeTooShort();
        self::testInvalidCodeTooLong();
        self::testInvalidCodeSpecialChars();
        self::testValidDiscountPercentage();
        self::testInvalidDiscountNegative();
        self::testInvalidDiscountOver100();
        self::testValidStatus();
        self::testInvalidStatus();
        self::testToggleStatus();
        self::testIsActive();
        self::testToArray();
        self::testConstructorWithAllParams();

        self::printResults();
        return self::$results;
    }

    private static function testValidCodeCreation(): void
    {
        try {
            $code = new DiscountCode('SUMMER50', 50.0, 'active');
            self::assert($code->getCode() === 'SUMMER50', 'Valid code creation');
        } catch (\Exception $e) {
            self::fail('Valid code creation', $e->getMessage());
        }
    }

    private static function testInvalidCodeTooShort(): void
    {
        try {
            $code = new DiscountCode('AB', 50.0, 'active');
            self::fail('Invalid code too short', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid code too short');
        } catch (\Exception $e) {
            self::fail('Invalid code too short', $e->getMessage());
        }
    }

    private static function testInvalidCodeTooLong(): void
    {
        try {
            $code = new DiscountCode(str_repeat('A', 51), 50.0, 'active');
            self::fail('Invalid code too long', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid code too long');
        } catch (\Exception $e) {
            self::fail('Invalid code too long', $e->getMessage());
        }
    }

    private static function testInvalidCodeSpecialChars(): void
    {
        try {
            $code = new DiscountCode('SUMMER@50', 50.0, 'active');
            self::fail('Invalid code special chars', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid code special chars');
        } catch (\Exception $e) {
            self::fail('Invalid code special chars', $e->getMessage());
        }
    }

    private static function testValidDiscountPercentage(): void
    {
        try {
            $code = new DiscountCode('VALID', 75.5);
            self::assert($code->getDiscountPercentage() === 75.5, 'Valid discount percentage');
        } catch (\Exception $e) {
            self::fail('Valid discount percentage', $e->getMessage());
        }
    }

    private static function testInvalidDiscountNegative(): void
    {
        try {
            $code = new DiscountCode('VALID', -10.0);
            self::fail('Invalid discount negative', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid discount negative');
        }
    }

    private static function testInvalidDiscountOver100(): void
    {
        try {
            $code = new DiscountCode('VALID', 150.0);
            self::fail('Invalid discount over 100', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid discount over 100');
        }
    }

    private static function testValidStatus(): void
    {
        try {
            $codeActive = new DiscountCode('VALID', 50.0, 'active');
            $codeInactive = new DiscountCode('VALID2', 50.0, 'inactive');
            self::assert($codeActive->getStatus() === 'active', 'Valid status active');
            self::assert($codeInactive->getStatus() === 'inactive', 'Valid status inactive');
        } catch (\Exception $e) {
            self::fail('Valid status', $e->getMessage());
        }
    }

    private static function testInvalidStatus(): void
    {
        try {
            $code = new DiscountCode('VALID', 50.0, 'pending');
            self::fail('Invalid status', 'Should have thrown exception');
        } catch (DiscountValidationException $e) {
            self::pass('Invalid status');
        }
    }

    private static function testToggleStatus(): void
    {
        try {
            $code = new DiscountCode('VALID', 50.0, 'active');
            $code->toggleStatus();
            self::assert($code->getStatus() === 'inactive', 'Toggle status to inactive');
            
            $code->toggleStatus();
            self::assert($code->getStatus() === 'active', 'Toggle status back to active');
        } catch (\Exception $e) {
            self::fail('Toggle status', $e->getMessage());
        }
    }

    private static function testIsActive(): void
    {
        try {
            $codeActive = new DiscountCode('VALID', 50.0, 'active');
            $codeInactive = new DiscountCode('VALID2', 50.0, 'inactive');
            
            self::assert($codeActive->isActive() === true, 'isActive returns true for active');
            self::assert($codeInactive->isActive() === false, 'isActive returns false for inactive');
        } catch (\Exception $e) {
            self::fail('isActive method', $e->getMessage());
        }
    }

    private static function testToArray(): void
    {
        try {
            $code = new DiscountCode('SUMMER50', 50.0, 'active', 1);
            $array = $code->toArray();
            
            self::assert($array['code'] === 'SUMMER50', 'toArray includes code');
            self::assert($array['discount_percentage'] === 50.0, 'toArray includes discount');
            self::assert($array['status'] === 'active', 'toArray includes status');
            self::assert($array['id'] === 1, 'toArray includes id');
        } catch (\Exception $e) {
            self::fail('toArray method', $e->getMessage());
        }
    }

    private static function testConstructorWithAllParams(): void
    {
        try {
            $created = new \DateTime('2025-01-01 10:00:00');
            $updated = new \DateTime('2025-01-02 15:30:00');
            
            $code = new DiscountCode('FULLCODE', 25.0, 'active', 42, $created, $updated);
            
            self::assert($code->getId() === 42, 'Constructor sets ID');
            self::assert($code->getCreatedAt() === $created, 'Constructor sets created_at');
            self::assert($code->getUpdatedAt() === $updated, 'Constructor sets updated_at');
        } catch (\Exception $e) {
            self::fail('Constructor with all params', $e->getMessage());
        }
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
$results = DiscountCodeModelTest::runAllTests();
