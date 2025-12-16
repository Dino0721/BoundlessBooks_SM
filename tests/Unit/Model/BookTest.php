<?php
declare(strict_types=1);

namespace Tests\Unit\Model;

use App\Model\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    private Book $book;

    protected function setUp(): void
    {
        // Arrange: Create a test book before each test
        $this->book = new Book(
            id: 1,
            title: 'Clean Code',
            author: 'Robert C. Martin',
            price: 29.99,
            stock: 10
        );
    }

    public function testBookCanBeCreated(): void
    {
        // Assert: Verify book properties
        $this->assertInstanceOf(Book::class, $this->book);
        $this->assertEquals(1, $this->book->getId());
        $this->assertEquals('Clean Code', $this->book->getTitle());
    }

    public function testBookTitleIsTrimmed(): void
    {
        $book = new Book(1, '  Trimmed Title  ', 'Author', 29.99);
        $this->assertEquals('Trimmed Title', $book->getTitle());
    }

    public function testNegativePriceThrowsException(): void
    {
        // Assert: Expect exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price cannot be negative');

        new Book(1, 'Title', 'Author', -10.00);
    }

    public function testNegativeStockThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock cannot be negative');

        new Book(1, 'Title', 'Author', 29.99, -5);
    }

    public function testDecreaseStockReducesStock(): void
    {
        // Act
        $this->book->decreaseStock(3);

        // Assert
        $this->assertEquals(7, $this->book->getStock());
    }

    public function testDecreaseStockBeyondAvailableThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->book->decreaseStock(15);
    }

    public function testIncreaseStockIncreasesStock(): void
    {
        // Act
        $this->book->increaseStock(5);

        // Assert
        $this->assertEquals(15, $this->book->getStock());
    }

    public function testIsInStockReturnsTrueWhenStockAvailable(): void
    {
        $this->assertTrue($this->book->isInStock());
    }

    public function testIsInStockReturnsFalseWhenNoStock(): void
    {
        // Arrange
        $book = new Book(1, 'Title', 'Author', 29.99, 0);

        // Assert
        $this->assertFalse($book->isInStock());
    }

    public function testPriceIsStoredAccurately(): void
    {
        $this->assertEquals(29.99, $this->book->getPrice());
    }

    public function testZeroPriceIsAllowed(): void
    {
        // Act
        $book = new Book(1, 'Free Book', 'Author', 0.0);

        // Assert
        $this->assertEquals(0.0, $book->getPrice());
    }
}
