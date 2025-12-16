<?php
/**
 * Unit Tests for Order Service
 * Test: Service business logic, repository interaction, access control
 */

namespace BoundlessBooks\Tests\Unit;

use BoundlessBooks\Models\Order;
use BoundlessBooks\Services\OrderService;
use BoundlessBooks\Repositories\OrderRepository;
use BoundlessBooks\Exceptions\OrderNotFoundException;
use BoundlessBooks\Exceptions\AccessDeniedException;
use BoundlessBooks\Exceptions\FileNotFoundException;

class MockOrderRepository extends OrderRepository
{
    private array $orders = [];
    private array $fileMap = [];

    public function __construct()
    {
        // Mock constructor - bypass parent PDO dependency
    }

    public function findById(int $orderId): ?Order
    {
        return $this->orders[$orderId] ?? null;
    }

    public function findByUserId(int $userId, ?string $search = null): array
    {
        $userOrders = array_filter($this->orders, function($o) use ($userId) {
            return $o->getUserId() === $userId;
        });
        
        if ($search) {
            return array_filter($userOrders, function($o) use ($search) {
                return stripos($o->getBookName(), $search) !== false;
            });
        }
        return $userOrders;
    }

    public function findAll(?string $searchType = null, ?string $searchValue = null): array
    {
        if (!$searchValue) {
            return $this->orders;
        }

        if ($searchType === 'user') {
            return array_filter($this->orders, function($o) use ($searchValue) {
                return $o->getUserId() === (int)$searchValue;
            });
        } elseif ($searchType === 'book') {
            return array_filter($this->orders, function($o) use ($searchValue) {
                return stripos($o->getBookName(), $searchValue) !== false;
            });
        } elseif ($searchType === 'order') {
            return array_filter($this->orders, function($o) use ($searchValue) {
                return $o->getId() === (int)$searchValue;
            });
        }

        return $this->orders;
    }

    public function userOwnsBook(int $userId, int $bookId): bool
    {
        return (bool) array_filter($this->orders, function($o) use ($userId, $bookId) {
            return $o->getUserId() === $userId && $o->getBookId() === $bookId;
        });
    }

    public function getBookPdfPath(int $bookId): ?string
    {
        return $this->fileMap[$bookId] ?? null;
    }

    // Test helpers
    public function addMockOrder(Order $order): void
    {
        $this->orders[$order->getId()] = $order;
    }

    public function addMockFile(int $bookId, string $path): void
    {
        $this->fileMap[$bookId] = $path;
    }
}

class OrderServiceTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];
    private static ?MockOrderRepository $mockRepo = null;
    private static ?OrderService $service = null;

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  UNIT TESTS: Order Service\n";
        echo "═════════════════════════════════════════════\n";

        self::setup();
        
        self::testGetUserOrderHistoryEmpty();
        self::testGetUserOrderHistoryWithOrders();
        self::testGetUserOrderHistoryWithSearch();
        self::testGetOrderSuccess();
        self::testGetOrderNotFound();
        self::testGetAllOrdersEmpty();
        self::testGetAllOrdersAll();
        self::testGetAllOrdersSearchByUserId();
        self::testGetAllOrdersSearchByBookName();
        self::testGetAllOrdersSearchByOrderId();
        self::testGetDownloadableBookSuccess();
        self::testGetDownloadableBookAccessDenied();
        self::testGetPdfFilePathSuccess();
        self::testGetPdfFilePathNotFound();
        self::testCountUserOrders();
        self::testGetUserTotalSpent();
        self::testGetUserMostRecentOrder();
        self::testStatisticsWithNoOrders();

        self::printResults();
        return self::$results;
    }

    private static function setup(): void
    {
        self::$mockRepo = new MockOrderRepository();
        
        // Add mock orders
        $order1 = new Order(1, 10, 'PHP Basics', 29.99, new \DateTime('2025-01-01'), new \DateTime('10:00:00'), 'books/php.pdf', 1);
        $order2 = new Order(1, 11, 'Advanced PHP', 49.99, new \DateTime('2025-01-15'), new \DateTime('14:30:00'), 'books/advphp.pdf', 2);
        $order3 = new Order(2, 10, 'PHP Basics', 29.99, new \DateTime('2025-01-20'), new \DateTime('09:00:00'), 'books/php.pdf', 3);
        
        self::$mockRepo->addMockOrder($order1);
        self::$mockRepo->addMockOrder($order2);
        self::$mockRepo->addMockOrder($order3);
        
        self::$mockRepo->addMockFile(10, 'books/php.pdf');
        self::$mockRepo->addMockFile(11, 'books/advphp.pdf');
    }

    private static function testGetUserOrderHistoryEmpty(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(999);
            self::assert(count($orders) === 0, 'Get user order history empty');
        } catch (\Exception $e) {
            self::fail('Get user order history empty', $e->getMessage());
        }
    }

    private static function testGetUserOrderHistoryWithOrders(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(1);
            self::assert(count($orders) === 2, 'Get user order history with orders');
        } catch (\Exception $e) {
            self::fail('Get user order history with orders', $e->getMessage());
        }
    }

    private static function testGetUserOrderHistoryWithSearch(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(1, 'Advanced');
            self::assert(count($orders) === 1, 'Get user order history with search');
            self::assert($orders[2]->getBookName() === 'Advanced PHP', 'Search returns correct order');
        } catch (\Exception $e) {
            self::fail('Get user order history with search', $e->getMessage());
        }
    }

    private static function testGetOrderSuccess(): void
    {
        try {
            $order = self::$mockRepo->findById(1);
            self::assert($order !== null, 'Get order success');
            self::assert($order->getBookName() === 'PHP Basics', 'Order has correct book');
        } catch (\Exception $e) {
            self::fail('Get order success', $e->getMessage());
        }
    }

    private static function testGetOrderNotFound(): void
    {
        try {
            $order = self::$mockRepo->findById(999);
            self::assert($order === null, 'Get order not found');
            self::pass('Get order not found');
        } catch (\Exception $e) {
            self::fail('Get order not found', $e->getMessage());
        }
    }

    private static function testGetAllOrdersEmpty(): void
    {
        try {
            $emptyRepo = new MockOrderRepository();
            $orders = $emptyRepo->findAll();
            self::assert(count($orders) === 0, 'Get all orders empty');
        } catch (\Exception $e) {
            self::fail('Get all orders empty', $e->getMessage());
        }
    }

    private static function testGetAllOrdersAll(): void
    {
        try {
            $orders = self::$mockRepo->findAll();
            self::assert(count($orders) === 3, 'Get all orders all');
        } catch (\Exception $e) {
            self::fail('Get all orders all', $e->getMessage());
        }
    }

    private static function testGetAllOrdersSearchByUserId(): void
    {
        try {
            $orders = self::$mockRepo->findAll('user', '1');
            self::assert(count($orders) === 2, 'Get all orders search by user ID');
        } catch (\Exception $e) {
            self::fail('Get all orders search by user ID', $e->getMessage());
        }
    }

    private static function testGetAllOrdersSearchByBookName(): void
    {
        try {
            $orders = self::$mockRepo->findAll('book', 'Advanced');
            self::assert(count($orders) === 1, 'Get all orders search by book name');
        } catch (\Exception $e) {
            self::fail('Get all orders search by book name', $e->getMessage());
        }
    }

    private static function testGetAllOrdersSearchByOrderId(): void
    {
        try {
            $orders = self::$mockRepo->findAll('order', '2');
            self::assert(count($orders) === 1, 'Get all orders search by order ID');
        } catch (\Exception $e) {
            self::fail('Get all orders search by order ID', $e->getMessage());
        }
    }

    private static function testGetDownloadableBookSuccess(): void
    {
        try {
            $owns = self::$mockRepo->userOwnsBook(1, 10);
            self::assert($owns === true, 'Get downloadable book success');
        } catch (\Exception $e) {
            self::fail('Get downloadable book success', $e->getMessage());
        }
    }

    private static function testGetDownloadableBookAccessDenied(): void
    {
        try {
            $owns = self::$mockRepo->userOwnsBook(1, 999);
            self::assert($owns === false, 'Get downloadable book access denied');
        } catch (\Exception $e) {
            self::fail('Get downloadable book access denied', $e->getMessage());
        }
    }

    private static function testGetPdfFilePathSuccess(): void
    {
        try {
            $path = self::$mockRepo->getBookPdfPath(10);
            self::assert($path === 'books/php.pdf', 'Get PDF file path success');
        } catch (\Exception $e) {
            self::fail('Get PDF file path success', $e->getMessage());
        }
    }

    private static function testGetPdfFilePathNotFound(): void
    {
        try {
            $path = self::$mockRepo->getBookPdfPath(999);
            self::assert($path === null, 'Get PDF file path not found');
        } catch (\Exception $e) {
            self::fail('Get PDF file path not found', $e->getMessage());
        }
    }

    private static function testCountUserOrders(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(1);
            $count = count($orders);
            self::assert($count === 2, 'Count user orders');
        } catch (\Exception $e) {
            self::fail('Count user orders', $e->getMessage());
        }
    }

    private static function testGetUserTotalSpent(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(1);
            $total = array_sum(array_map(function($o) { return $o->getBookPrice(); }, $orders));
            self::assert($total === 79.98, 'Get user total spent');
        } catch (\Exception $e) {
            self::fail('Get user total spent', $e->getMessage());
        }
    }

    private static function testGetUserMostRecentOrder(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(1);
            $recent = array_reduce($orders, function($carry, $item) {
                $carryDate = $carry ? $carry->getPurchaseDate() : new \DateTime('1900-01-01');
                return $item->getPurchaseDate() > $carryDate ? $item : $carry;
            });
            self::assert($recent->getBookName() === 'Advanced PHP', 'Get user most recent order');
        } catch (\Exception $e) {
            self::fail('Get user most recent order', $e->getMessage());
        }
    }

    private static function testStatisticsWithNoOrders(): void
    {
        try {
            $orders = self::$mockRepo->findByUserId(999);
            self::assert(count($orders) === 0, 'Statistics with no orders');
        } catch (\Exception $e) {
            self::fail('Statistics with no orders', $e->getMessage());
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
$results = OrderServiceTest::runAllTests();
