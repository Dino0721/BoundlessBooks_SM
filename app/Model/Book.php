<?php
declare(strict_types=1);

namespace App\Model;

class Book
{
    private int $id;
    private string $title;
    private string $author;
    private float $price;
    private int $stock;

    public function __construct(
        int $id,
        string $title,
        string $author,
        float $price,
        int $stock = 0
    ) {
        $this->validatePrice($price);
        $this->validateStock($stock);

        $this->id = $id;
        $this->title = trim($title);
        $this->author = trim($author);
        $this->price = $price;
        $this->stock = $stock;
    }

    private function validatePrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
    }

    private function validateStock(int $stock): void
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException('Insufficient stock');
        }
        $this->stock -= $quantity;
    }

    public function increaseStock(int $quantity): void
    {
        $this->validateStock($this->stock + $quantity);
        $this->stock += $quantity;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
