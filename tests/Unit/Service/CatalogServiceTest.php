<?php
declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Model\Book;
use App\Service\BookRepositoryInterface;
use App\Service\CatalogService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogServiceTest extends TestCase
{
    private CatalogService $service;
    private BookRepositoryInterface&MockObject $mockRepository;

    protected function setUp(): void
    {
        // Create a mock repository
        $this->mockRepository = $this->createMock(BookRepositoryInterface::class);
        $this->service = new CatalogService($this->mockRepository);
    }

    public function testGetBookReturnsBookFromRepository(): void
    {
        // Arrange
        $expectedBook = new Book(1, 'Test Book', 'Author', 29.99, 10);
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedBook);

        // Act
        $result = $this->service->getBook(1);

        // Assert
        $this->assertSame($expectedBook, $result);
    }

    public function testGetBookReturnsNullWhenNotFound(): void
    {
        // Arrange
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Act
        $result = $this->service->getBook(999);

        // Assert
        $this->assertNull($result);
    }

    public function testPurchaseBookDecreaseStock(): void
    {
        // Arrange
        $book = new Book(1, 'Test Book', 'Author', 29.99, 10);
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);
        $this->mockRepository
            ->expects($this->once())
            ->method('save')
            ->with($book);

        // Act
        $result = $this->service->purchaseBook(1, 3);

        // Assert
        $this->assertEquals(7, $result->getStock());
    }

    public function testPurchaseBookNotFoundThrowsException(): void
    {
        // Arrange
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Book not found');

        // Act
        $this->service->purchaseBook(999, 1);
    }

    public function testPurchaseWithInvalidQuantityThrowsException(): void
    {
        // Arrange
        $book = new Book(1, 'Test Book', 'Author', 29.99, 10);
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be positive');

        // Act
        $this->service->purchaseBook(1, 0);
    }

    public function testRestockBookIncreasesStock(): void
    {
        // Arrange
        $book = new Book(1, 'Test Book', 'Author', 29.99, 10);
        $this->mockRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($book);
        $this->mockRepository
            ->expects($this->once())
            ->method('save');

        // Act
        $result = $this->service->restockBook(1, 5);

        // Assert
        $this->assertEquals(15, $result->getStock());
    }
}
