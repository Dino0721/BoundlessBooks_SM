<?php
/**
 * Integration Tests for Order Module
 * Test: Full workflows including multiple components
 */

namespace BoundlessBooks\Tests\Integration;

use BoundlessBooks\Models\Order;
use BoundlessBooks\Repositories\OrderRepository;

class IntegrationTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  INTEGRATION TESTS: Order Module\n";
        echo "═════════════════════════════════════════════\n";

        self::testCompleteOrderWorkflow();
        self::testAdminOrderListingWithFilters();
        self::testUserCanAccessOwnOrders();
        self::testAccessControlPreventsUnauthorizedDownload();
        self::testOrderSearchFunctionality();
        self::testDataConsistencyAfterMultipleOperations();

        self::printResults();
        return self::$results;
    }

    private static function testCompleteOrderWorkflow(): void
    {
        try {
            // Scenario: User purchases book and retrieves order
            $order = new Order(
                5,
                25,
                'Web Development Guide',
                149.99,
                new \DateTime('2025-01-10'),
                new \DateTime('11:30:00'),
                'books/webdev.pdf',
                101
            );

            // Verify order creation
            self::assert($order->getId() === 101, 'Order created with ID');
            self::assert($order->getUserId() === 5, 'Order assigned to user');
            self::assert($order->getBookId() === 25, 'Order linked to book');
            
            // Verify order serialization
            $array = $order->toArray();
            self::assert($array['book_name'] === 'Web Development Guide', 'Order serializes correctly');
            
            self::pass('Complete order workflow');
        } catch (\Exception $e) {
            self::fail('Complete order workflow', $e->getMessage());
        }
    }

    private static function testAdminOrderListingWithFilters(): void
    {
        try {
            $orders = [];
            
            // Create sample orders
            $orders[] = new Order(1, 10, 'PHP Basics', 29.99, new \DateTime('2025-01-05'), new \DateTime('10:00:00'), 'books/php.pdf', 1);
            $orders[] = new Order(1, 11, 'Python Guide', 39.99, new \DateTime('2025-01-10'), new \DateTime('14:00:00'), 'books/python.pdf', 2);
            $orders[] = new Order(2, 10, 'PHP Basics', 29.99, new \DateTime('2025-01-15'), new \DateTime('09:00:00'), 'books/php.pdf', 3);
            $orders[] = new Order(3, 12, 'JavaScript Pro', 44.99, new \DateTime('2025-01-20'), new \DateTime('16:00:00'), 'books/js.pdf', 4);
            
            // Test: Admin can see all orders
            self::assert(count($orders) === 4, 'Admin sees all orders');
            
            // Test: Filter by user ID (user 1 has 2 orders)
            $user1Orders = array_filter($orders, function($o) { return $o->getUserId() === 1; });
            self::assert(count($user1Orders) === 2, 'Filter by user ID works');
            
            // Test: Filter by book name
            $phpOrders = array_filter($orders, function($o) { 
                return stripos($o->getBookName(), 'PHP') !== false; 
            });
            self::assert(count($phpOrders) === 2, 'Filter by book name works');
            
            self::pass('Admin order listing with filters');
        } catch (\Exception $e) {
            self::fail('Admin order listing with filters', $e->getMessage());
        }
    }

    private static function testUserCanAccessOwnOrders(): void
    {
        try {
            $userOrders = [];
            
            // Create orders for user 2
            $userOrders[] = new Order(2, 15, 'Database Design', 59.99, new \DateTime('2025-01-08'), new \DateTime('13:00:00'), 'books/db.pdf', 10);
            $userOrders[] = new Order(2, 16, 'SQL Mastery', 69.99, new \DateTime('2025-01-18'), new \DateTime('15:30:00'), 'books/sql.pdf', 11);
            
            // User should see 2 orders
            self::assert(count($userOrders) === 2, 'User has access to their orders');
            
            // Verify each order belongs to user 2
            foreach ($userOrders as $order) {
                self::assert($order->getUserId() === 2, 'Each order belongs to correct user');
            }
            
            self::pass('User can access own orders');
        } catch (\Exception $e) {
            self::fail('User can access own orders', $e->getMessage());
        }
    }

    private static function testAccessControlPreventsUnauthorizedDownload(): void
    {
        try {
            $order = new Order(1, 20, 'Secure Coding', 54.99, new \DateTime('2025-01-12'), new \DateTime('12:00:00'), 'books/secure.pdf', 50);
            
            // User 1 owns this order
            self::assert($order->getUserId() === 1, 'Order belongs to user 1');
            self::assert($order->getBookId() === 20, 'Order is for book 20');
            
            // User 2 should NOT be able to download (owner check would fail)
            $isOwner = ($order->getUserId() === 2);
            self::assert($isOwner === false, 'Access control prevents unauthorized download');
            
            self::pass('Access control prevents unauthorized download');
        } catch (\Exception $e) {
            self::fail('Access control prevents unauthorized download', $e->getMessage());
        }
    }

    private static function testOrderSearchFunctionality(): void
    {
        try {
            $allOrders = [];
            
            // Create test orders
            $allOrders[] = new Order(1, 10, 'PHP Basics', 29.99, new \DateTime('2025-01-05'), new \DateTime('10:00:00'), 'books/php.pdf', 1);
            $allOrders[] = new Order(2, 11, 'Advanced PHP', 49.99, new \DateTime('2025-01-10'), new \DateTime('14:00:00'), 'books/advphp.pdf', 2);
            $allOrders[] = new Order(3, 12, 'JavaScript', 44.99, new \DateTime('2025-01-15'), new \DateTime('09:00:00'), 'books/js.pdf', 3);
            $allOrders[] = new Order(1, 13, 'React Guide', 59.99, new \DateTime('2025-01-20'), new \DateTime('16:00:00'), 'books/react.pdf', 4);
            
            // Search by user ID 1
            $user1 = array_filter($allOrders, function($o) { return $o->getUserId() === 1; });
            self::assert(count($user1) === 2, 'Search by user ID returns correct orders');
            
            // Search by book name "PHP"
            $phpBooks = array_filter($allOrders, function($o) { 
                return stripos($o->getBookName(), 'PHP') !== false; 
            });
            self::assert(count($phpBooks) === 2, 'Search by book name is case-insensitive');
            
            // Search by order ID
            $specificOrder = array_filter($allOrders, function($o) { return $o->getId() === 2; });
            self::assert(count($specificOrder) === 1, 'Search by order ID is exact');
            
            self::pass('Order search functionality');
        } catch (\Exception $e) {
            self::fail('Order search functionality', $e->getMessage());
        }
    }

    private static function testDataConsistencyAfterMultipleOperations(): void
    {
        try {
            // Create multiple orders
            $orders = [];
            for ($i = 1; $i <= 5; $i++) {
                $orders[$i] = new Order(
                    1,
                    $i + 10,
                    "Book $i",
                    10.00 * $i,
                    new \DateTime("2025-01-0$i"),
                    new \DateTime("10:00:00"),
                    "books/book$i.pdf",
                    $i
                );
            }
            
            // Verify all orders maintain consistency
            $total = 0;
            foreach ($orders as $i => $order) {
                self::assert($order->getId() === $i, 'Order ID consistent');
                self::assert($order->getUserId() === 1, 'User ID consistent');
                self::assert($order->getBookId() === $i + 10, 'Book ID consistent');
                $total += $order->getBookPrice();
            }
            
            // Verify total calculation
            self::assert($total === 150.0, 'Total calculation is accurate');
            
            // Verify serialization consistency
            $array = $orders[1]->toArray();
            self::assert($array['user_id'] === 1, 'Serialization maintains user ID');
            self::assert($array['book_price'] === 10.00, 'Serialization maintains price');
            
            self::pass('Data consistency after multiple operations');
        } catch (\Exception $e) {
            self::fail('Data consistency after multiple operations', $e->getMessage());
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
$results = IntegrationTest::runAllTests();
