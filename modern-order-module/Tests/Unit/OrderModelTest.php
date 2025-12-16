<?php
/**
 * Unit Tests for Order Model
 * Test: Model validation and behavior
 * Coverage: All setter/getter methods and validation
 */

namespace BoundlessBooks\Tests\Unit;

use BoundlessBooks\Models\Order;
use BoundlessBooks\Exceptions\ValidationException;

class OrderModelTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  UNIT TESTS: Order Model\n";
        echo "═════════════════════════════════════════════\n";

        self::testValidOrderCreation();
        self::testValidBookName();
        self::testInvalidBookNameTooLong();
        self::testInvalidBookNameEmpty();
        self::testValidBookPrice();
        self::testInvalidBookPriceNegative();
        self::testInvalidBookPriceToohigh();
        self::testValidPdfPath();
        self::testFormattedPrice();
        self::testPurchaseDateFormatted();
        self::testPurchaseTimeFormatted();
        self::testToArray();
        self::testConstructorWithAllParams();

        self::printResults();
        return self::$results;
    }

    private static function testValidOrderCreation(): void
    {
        try {
            $order = new Order(
                1,
                10,
                'Programming in PHP',
                99.99,
                new \DateTime('2025-01-01'),
                new \DateTime('2025-01-01 14:30:00')
            );
            self::assert($order->getBookName() === 'Programming in PHP', 'Valid order creation');
        } catch (\Exception $e) {
            self::fail('Valid order creation', $e->getMessage());
        }
    }

    private static function testValidBookName(): void
    {
        try {
            $order = new Order(1, 10, 'Valid Book Name', 50.0, new \DateTime(), new \DateTime());
            self::assert($order->getBookName() === 'Valid Book Name', 'Valid book name');
        } catch (\Exception $e) {
            self::fail('Valid book name', $e->getMessage());
        }
    }

    private static function testInvalidBookNameTooLong(): void
    {
        try {
            $name = str_repeat('A', 256);
            $order = new Order(1, 10, $name, 50.0, new \DateTime(), new \DateTime());
            self::fail('Invalid book name too long', 'Should have thrown exception');
        } catch (ValidationException $e) {
            self::pass('Invalid book name too long');
        }
    }

    private static function testInvalidBookNameEmpty(): void
    {
        try {
            $order = new Order(1, 10, '', 50.0, new \DateTime(), new \DateTime());
            self::fail('Invalid book name empty', 'Should have thrown exception');
        } catch (ValidationException $e) {
            self::pass('Invalid book name empty');
        }
    }

    private static function testValidBookPrice(): void
    {
        try {
            $order = new Order(1, 10, 'Book', 99.99, new \DateTime(), new \DateTime());
            self::assert($order->getBookPrice() === 99.99, 'Valid book price');
        } catch (\Exception $e) {
            self::fail('Valid book price', $e->getMessage());
        }
    }

    private static function testInvalidBookPriceNegative(): void
    {
        try {
            $order = new Order(1, 10, 'Book', -10.0, new \DateTime(), new \DateTime());
            self::fail('Invalid book price negative', 'Should have thrown exception');
        } catch (ValidationException $e) {
            self::pass('Invalid book price negative');
        }
    }

    private static function testInvalidBookPriceToohigh(): void
    {
        try {
            $order = new Order(1, 10, 'Book', 1000000.0, new \DateTime(), new \DateTime());
            self::fail('Invalid book price too high', 'Should have thrown exception');
        } catch (ValidationException $e) {
            self::pass('Invalid book price too high');
        }
    }

    private static function testValidPdfPath(): void
    {
        try {
            $order = new Order(1, 10, 'Book', 50.0, new \DateTime(), new \DateTime(), 'books/sample.pdf');
            self::assert($order->getPdfPath() === 'books/sample.pdf', 'Valid PDF path');
        } catch (\Exception $e) {
            self::fail('Valid PDF path', $e->getMessage());
        }
    }

    private static function testFormattedPrice(): void
    {
        try {
            $order = new Order(1, 10, 'Book', 99.99, new \DateTime(), new \DateTime());
            $formatted = $order->getFormattedPrice();
            self::assert($formatted === 'RM99.99', 'Formatted price');
        } catch (\Exception $e) {
            self::fail('Formatted price', $e->getMessage());
        }
    }

    private static function testPurchaseDateFormatted(): void
    {
        try {
            $date = new \DateTime('2025-01-15');
            $order = new Order(1, 10, 'Book', 50.0, $date, new \DateTime());
            $formatted = $order->getPurchaseDateFormatted();
            self::assert($formatted === '2025-01-15', 'Purchase date formatted');
        } catch (\Exception $e) {
            self::fail('Purchase date formatted', $e->getMessage());
        }
    }

    private static function testPurchaseTimeFormatted(): void
    {
        try {
            $time = new \DateTime('14:30:00');
            $order = new Order(1, 10, 'Book', 50.0, new \DateTime(), $time);
            $formatted = $order->getPurchaseTimeFormatted();
            self::assert(strpos($formatted, '14:30:00') !== false, 'Purchase time formatted');
        } catch (\Exception $e) {
            self::fail('Purchase time formatted', $e->getMessage());
        }
    }

    private static function testToArray(): void
    {
        try {
            $order = new Order(1, 10, 'Test Book', 75.50, new \DateTime('2025-01-01'), new \DateTime('14:30:00'), 'books/test.pdf', 5);
            $array = $order->toArray();
            
            self::assert($array['user_id'] === 1, 'toArray includes user_id');
            self::assert($array['book_id'] === 10, 'toArray includes book_id');
            self::assert($array['book_name'] === 'Test Book', 'toArray includes book_name');
            self::assert($array['book_price'] === 75.50, 'toArray includes book_price');
            self::pass('toArray method integration');
        } catch (\Exception $e) {
            self::fail('toArray method', $e->getMessage());
        }
    }

    private static function testConstructorWithAllParams(): void
    {
        try {
            $order = new Order(
                5,
                20,
                'Complete Order',
                125.00,
                new \DateTime('2025-02-01'),
                new \DateTime('16:45:30'),
                'books/complete.pdf',
                42
            );
            
            self::assert($order->getId() === 42, 'Constructor sets ID');
            self::assert($order->getUserId() === 5, 'Constructor sets user ID');
            self::assert($order->getBookId() === 20, 'Constructor sets book ID');
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
$results = OrderModelTest::runAllTests();
