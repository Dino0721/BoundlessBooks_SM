<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Book;
use App\Service\BookRepositoryInterface;
use App\Service\CatalogService;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests exercise full workflows across service boundaries
 */
class CatalogWorkflowTest extends TestCase
{
    private CatalogService $service;
    private BookRepositoryInterface $repository;

    protected function setUp(): void
    {
        // Create a stub repository for integration testing
        $this->repository = new class implements BookRepositoryInterface {
            private array $books = [];

            public function findById(int $id): ?Book {
                return $this->books[$id] ?? null;
            }

            public function save(Book $book): void {
                $this->books[$book->getId()] = $book;
            }

            public function setBook(Book $book): void {
                $this->books[$book->getId()] = $book;
            }
        };

        $this->service = new CatalogService($this->repository);
    }

    public function testCompleteCheckoutWorkflow(): void
    {
        // Arrange: Create a book and store it
        $book = new Book(1, 'Clean Code', 'Robert C. Martin', 49.99, 100);
        $this->repository->findById(1) === null && $this->repository->save($book);

        // Simulate: User adds 3 books to cart
        $this->repository->save(new Book(1, 'Clean Code', 'Robert C. Martin', 49.99, 100));

        // Act: Purchase books
        $purchased = $this->service->purchaseBook(1, 3);

        // Assert: Stock decreased correctly
        $this->assertEquals(97, $purchased->getStock());
    }

    public function testRestockAfterInventoryAdjustment(): void
    {
        // Arrange
        $book = new Book(1, 'Design Patterns', 'Gamma et al.', 59.99, 5);
        $this->repository->save($book);

        // Act: Restock after purchase order arrived
        $restocked = $this->service->restockBook(1, 50);

        // Assert
        $this->assertEquals(55, $restocked->getStock());
    }
}
