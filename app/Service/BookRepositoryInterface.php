<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Book;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;
    public function save(Book $book): void;
}
