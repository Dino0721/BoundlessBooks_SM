<?php
/**
 * Order Model
 * Represents a book ownership/order entity
 * Single Responsibility: Data representation and validation
 */

namespace BoundlessBooks\Models;

use BoundlessBooks\Exceptions\ValidationException;

class Order
{
    private ?int $id;
    private int $userId;
    private int $bookId;
    private string $bookName;
    private float $bookPrice;
    private \DateTime $purchaseDate;
    private \DateTime $purchaseTime;
    private ?string $pdfPath;

    public function __construct(
        int $userId,
        int $bookId,
        string $bookName,
        float $bookPrice,
        \DateTime $purchaseDate,
        \DateTime $purchaseTime,
        ?string $pdfPath = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->bookId = $bookId;
        $this->pdfPath = $pdfPath;
        
        $this->setBookName($bookName);
        $this->setBookPrice($bookPrice);
        $this->purchaseDate = $purchaseDate;
        $this->purchaseTime = $purchaseTime;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getBookId(): int { return $this->bookId; }
    public function getBookName(): string { return $this->bookName; }
    public function getBookPrice(): float { return $this->bookPrice; }
    public function getPurchaseDate(): \DateTime { return $this->purchaseDate; }
    public function getPurchaseTime(): \DateTime { return $this->purchaseTime; }
    public function getPdfPath(): ?string { return $this->pdfPath; }

    // Setters with validation
    public function setBookName(string $name): void
    {
        $name = trim($name);
        if (strlen($name) < 1 || strlen($name) > 255) {
            throw new ValidationException('Book name must be between 1 and 255 characters');
        }
        $this->bookName = $name;
    }

    public function setBookPrice(float $price): void
    {
        if ($price < 0 || $price > 999999.99) {
            throw new ValidationException('Book price must be between 0 and 999999.99');
        }
        $this->bookPrice = $price;
    }

    public function setPdfPath(?string $path): void
    {
        if ($path !== null) {
            $path = trim($path);
            if (strlen($path) === 0) {
                throw new ValidationException('PDF path cannot be empty string');
            }
        }
        $this->pdfPath = $path;
    }

    public function getFormattedPrice(): string
    {
        return 'RM' . number_format($this->bookPrice, 2);
    }

    public function getPurchaseDateFormatted(): string
    {
        return $this->purchaseDate->format('Y-m-d');
    }

    public function getPurchaseTimeFormatted(): string
    {
        return $this->purchaseTime->format('H:i:s');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
            'book_name' => $this->bookName,
            'book_price' => $this->bookPrice,
            'purchase_date' => $this->purchaseDate->format('Y-m-d'),
            'purchase_time' => $this->purchaseTime->format('H:i:s'),
            'pdf_path' => $this->pdfPath,
        ];
    }
}
