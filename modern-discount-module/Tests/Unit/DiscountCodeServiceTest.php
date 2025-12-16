<?php
/**
 * Unit Tests for DiscountCodeService
 * Test: Business logic and discount calculations
 * Coverage: Service methods and calculations
 */

namespace BoundlessBooks\Tests\Unit;

use BoundlessBooks\Services\DiscountCodeService;
use BoundlessBooks\Repositories\DiscountCodeRepository;
use BoundlessBooks\Models\DiscountCode;
use BoundlessBooks\Exceptions\DiscountValidationException;

class MockDiscountRepository
{
    private array $storage = [];
    private int $nextId = 1;

    public function findById(int $id): ?DiscountCode
    {
        return $this->storage[$id] ?? null;
    }

    public function findByCode(string $code): ?DiscountCode
    {
        foreach ($this->storage as $discount) {
            if ($discount->getCode() === trim($code)) {
                return $discount;
            }
        }
        return null;
    }

    public function findAll(string $search = '', string $status = ''): array
    {
        $results = $this->storage;
        if (!empty($search)) {
            $results = array_filter($results, 
                fn($d) => stripos($d->getCode(), $search) !== false
            );
        }
        if (!empty($status)) {
            $results = array_filter($results, fn($d) => $d->getStatus() === $status);
        }
        return array_values($results);
    }

    public function findActiveByCode(string $code): ?DiscountCode
    {
        foreach ($this->storage as $discount) {
            if ($discount->getCode() === trim($code) && $discount->isActive()) {
                return $discount;
            }
        }
        return null;
    }

    public function create(DiscountCode $discountCode): DiscountCode
    {
        $reflection = new \ReflectionClass($discountCode);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($discountCode, $this->nextId);
        $this->storage[$this->nextId] = $discountCode;
        $this->nextId++;
        return $discountCode;
    }

    public function update(DiscountCode $discountCode): DiscountCode
    {
        if ($discountCode->getId()) {
            $this->storage[$discountCode->getId()] = $discountCode;
        }
        return $discountCode;
    }

    public function delete(int $id): bool
    {
        unset($this->storage[$id]);
        return true;
    }
}

class DiscountCodeServiceTest
{
    private static int $testsPassed = 0;
    private static int $testsFailed = 0;
    private static array $results = [];

    public static function runAllTests(): array
    {
        echo "═════════════════════════════════════════════\n";
        echo "  UNIT TESTS: DiscountCodeService\n";
        echo "═════════════════════════════════════════════\n";

        self::testCreateDiscountCode();
        self::testGetDiscountCodeById();
        self::testGetDiscountCodeByCode();
        self::testGetAllDiscountCodes();
        self::testToggleDiscountCodeStatus();
        self::testDeleteDiscountCode();
        self::testValidateDiscountCode();
        self::testCalculateDiscount();
        self::testCalculateDiscountZero();
        self::testCalculateDiscount100();

        self::printResults();
        return self::$results;
    }

    private static function testCreateDiscountCode(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $code = $service->createDiscountCode('NEW50', 50.0, 'active');
            self::assert($code->getCode() === 'NEW50', 'Create discount code');
        } catch (\Exception $e) {
            self::fail('Create discount code', $e->getMessage());
        }
    }

    private static function testGetDiscountCodeById(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $created = $service->createDiscountCode('TEST', 25.0);
            $retrieved = $service->getDiscountCodeById($created->getId());
            
            self::assert($retrieved !== null && $retrieved->getCode() === 'TEST', 'Get discount by ID');
        } catch (\Exception $e) {
            self::fail('Get discount by ID', $e->getMessage());
        }
    }

    private static function testGetDiscountCodeByCode(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $service->createDiscountCode('UNIQUE', 30.0);
            $retrieved = $service->getDiscountCodeByCode('UNIQUE');
            
            self::assert($retrieved !== null && $retrieved->getCode() === 'UNIQUE', 'Get discount by code');
        } catch (\Exception $e) {
            self::fail('Get discount by code', $e->getMessage());
        }
    }

    private static function testGetAllDiscountCodes(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $service->createDiscountCode('CODE1', 10.0, 'active');
            $service->createDiscountCode('CODE2', 20.0, 'inactive');
            $service->createDiscountCode('CODE3', 30.0, 'active');
            
            $all = $service->getAllDiscountCodes();
            $active = $service->getAllDiscountCodes('', 'active');
            $inactive = $service->getAllDiscountCodes('', 'inactive');
            
            self::assert(count($all) === 3, 'Get all discount codes count 3');
            self::assert(count($active) === 2, 'Get active discount codes count 2');
            self::assert(count($inactive) === 1, 'Get inactive discount codes count 1');
        } catch (\Exception $e) {
            self::fail('Get all discount codes', $e->getMessage());
        }
    }

    private static function testToggleDiscountCodeStatus(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $created = $service->createDiscountCode('TOGGLE', 50.0, 'active');
            $toggled = $service->toggleDiscountCodeStatus($created->getId());
            
            self::assert($toggled->getStatus() === 'inactive', 'Toggle to inactive');
            
            $toggled2 = $service->toggleDiscountCodeStatus($created->getId());
            self::assert($toggled2->getStatus() === 'active', 'Toggle back to active');
        } catch (\Exception $e) {
            self::fail('Toggle discount code status', $e->getMessage());
        }
    }

    private static function testDeleteDiscountCode(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $created = $service->createDiscountCode('DELETE', 50.0);
            $deleted = $service->deleteDiscountCode($created->getId());
            $retrieved = $service->getDiscountCodeById($created->getId());
            
            self::assert($deleted === true && $retrieved === null, 'Delete discount code');
        } catch (\Exception $e) {
            self::fail('Delete discount code', $e->getMessage());
        }
    }

    private static function testValidateDiscountCode(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $service->createDiscountCode('ACTIVE', 50.0, 'active');
            $service->createDiscountCode('INACTIVE', 30.0, 'inactive');
            
            $activeCode = $service->validateDiscountCode('ACTIVE');
            $inactiveCode = $service->validateDiscountCode('INACTIVE');
            
            self::assert($activeCode !== null, 'Validate active code returns result');
            self::assert($inactiveCode === null, 'Validate inactive code returns null');
        } catch (\Exception $e) {
            self::fail('Validate discount code', $e->getMessage());
        }
    }

    private static function testCalculateDiscount(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $result = $service->calculateDiscount(100.0, 25.0);
            
            self::assert($result['original_price'] === 100.0, 'Calculate discount original price');
            self::assert($result['discount_percentage'] === 25.0, 'Calculate discount percentage');
            self::assert($result['discount_amount'] === 25.0, 'Calculate discount amount');
            self::assert($result['final_price'] === 75.0, 'Calculate discount final price');
        } catch (\Exception $e) {
            self::fail('Calculate discount', $e->getMessage());
        }
    }

    private static function testCalculateDiscountZero(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $result = $service->calculateDiscount(100.0, 0.0);
            
            self::assert($result['discount_amount'] === 0.0, 'Calculate discount 0% amount');
            self::assert($result['final_price'] === 100.0, 'Calculate discount 0% final price');
        } catch (\Exception $e) {
            self::fail('Calculate discount 0%', $e->getMessage());
        }
    }

    private static function testCalculateDiscount100(): void
    {
        try {
            $repo = new MockDiscountRepository();
            $service = new DiscountCodeService($repo);
            
            $result = $service->calculateDiscount(100.0, 100.0);
            
            self::assert($result['discount_amount'] === 100.0, 'Calculate discount 100% amount');
            self::assert($result['final_price'] === 0.0, 'Calculate discount 100% final price');
        } catch (\Exception $e) {
            self::fail('Calculate discount 100%', $e->getMessage());
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
$results = DiscountCodeServiceTest::runAllTests();
