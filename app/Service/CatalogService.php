<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Book;

class CatalogService
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {}

    public function getBook(int $id): ?Book
    {
        return $this->bookRepository->findById($id);
    }

    public function purchaseBook(int $bookId, int $quantity): Book
    {
        $book = $this->bookRepository->findById($bookId);

        if ($book === null) {
            throw new \RuntimeException('Book not found');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $book->decreaseStock($quantity);
        $this->bookRepository->save($book);

        return $book;
    }

    public function restockBook(int $bookId, int $quantity): Book
    {
        $book = $this->bookRepository->findById($bookId);

        if ($book === null) {
            throw new \RuntimeException('Book not found');
        }

        $book->increaseStock($quantity);
        $this->bookRepository->save($book);

        return $book;
    }
}
