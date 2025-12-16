<?php
/**
 * DiscountCode Model
 * Represents a discount code entity with validation
 * Single Responsibility: Data representation and validation
 */

namespace BoundlessBooks\Models;

use BoundlessBooks\Exceptions\DiscountValidationException;

class DiscountCode
{
    private ?int $id;
    private string $code;
    private float $discountPercentage;
    private string $status; // 'active' or 'inactive'
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct(
        string $code,
        float $discountPercentage,
        string $status = 'active',
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        
        $this->setCode($code);
        $this->setDiscountPercentage($discountPercentage);
        $this->setStatus($status);
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getDiscountPercentage(): float { return $this->discountPercentage; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    // Setters with validation
    public function setCode(string $code): void
    {
        $code = trim($code);
        if (strlen($code) < 3 || strlen($code) > 50) {
            throw new DiscountValidationException('Code must be between 3 and 50 characters');
        }
        if (!preg_match('/^[A-Z0-9\-]+$/', $code)) {
            throw new DiscountValidationException('Code must contain only uppercase letters, numbers, and hyphens');
        }
        $this->code = $code;
    }

    public function setDiscountPercentage(float $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new DiscountValidationException('Discount percentage must be between 0 and 100');
        }
        $this->discountPercentage = $percentage;
    }

    public function setStatus(string $status): void
    {
        $status = strtolower(trim($status));
        if (!in_array($status, ['active', 'inactive'])) {
            throw new DiscountValidationException('Status must be "active" or "inactive"');
        }
        $this->status = $status;
    }

    public function toggleStatus(): void
    {
        $this->status = $this->status === 'active' ? 'inactive' : 'active';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'discount_percentage' => $this->discountPercentage,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
