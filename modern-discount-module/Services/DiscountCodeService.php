<?php
/**
 * DiscountCodeService
 * Single Responsibility: Business logic for discount code operations
 * Orchestrates between Repository and Model
 */

namespace BoundlessBooks\Services;

use BoundlessBooks\Models\DiscountCode;
use BoundlessBooks\Repositories\DiscountCodeRepository;
use BoundlessBooks\Exceptions\InvalidDiscountCodeException;
use BoundlessBooks\Exceptions\DiscountValidationException;

class DiscountCodeService
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new discount code
     */
    public function createDiscountCode(
        string $code,
        float $discountPercentage,
        string $status = 'active'
    ): DiscountCode {
        $discountCode = new DiscountCode($code, $discountPercentage, $status);
        return $this->repository->create($discountCode);
    }

    /**
     * Get discount code by ID
     */
    public function getDiscountCodeById(int $id): ?DiscountCode
    {
        return $this->repository->findById($id);
    }

    /**
     * Get discount code by code string
     */
    public function getDiscountCodeByCode(string $code): ?DiscountCode
    {
        return $this->repository->findByCode($code);
    }

    /**
     * Get all discount codes with optional search and filter
     */
    public function getAllDiscountCodes(string $search = '', string $status = ''): array
    {
        return $this->repository->findAll($search, $status);
    }

    /**
     * Update discount code
     */
    public function updateDiscountCode(
        int $id,
        string $code,
        float $discountPercentage,
        string $status
    ): DiscountCode {
        $discountCode = $this->repository->findById($id);
        if (!$discountCode) {
            throw new InvalidDiscountCodeException('Code ID not found: ' . $id);
        }

        $discountCode->setCode($code);
        $discountCode->setDiscountPercentage($discountPercentage);
        $discountCode->setStatus($status);

        return $this->repository->update($discountCode);
    }

    /**
     * Toggle status between active/inactive
     */
    public function toggleDiscountCodeStatus(int $id): DiscountCode
    {
        $discountCode = $this->repository->findById($id);
        if (!$discountCode) {
            throw new InvalidDiscountCodeException('Code ID not found: ' . $id);
        }

        $discountCode->toggleStatus();
        return $this->repository->update($discountCode);
    }

    /**
     * Delete discount code
     */
    public function deleteDiscountCode(int $id): bool
    {
        $discountCode = $this->repository->findById($id);
        if (!$discountCode) {
            throw new InvalidDiscountCodeException('Code ID not found: ' . $id);
        }

        return $this->repository->delete($id);
    }

    /**
     * Validate and apply discount code
     */
    public function validateDiscountCode(string $code): ?DiscountCode
    {
        return $this->repository->findActiveByCode($code);
    }

    /**
     * Calculate discounted price
     */
    public function calculateDiscount(float $originalPrice, float $discountPercentage): array
    {
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new DiscountValidationException('Invalid discount percentage');
        }

        $discountAmount = ($originalPrice * $discountPercentage) / 100;
        $finalPrice = $originalPrice - $discountAmount;

        return [
            'original_price' => $originalPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => round($discountAmount, 2),
            'final_price' => round($finalPrice, 2),
        ];
    }
}
