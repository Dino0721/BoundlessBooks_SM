<?php
/**
 * DiscountCodeRepository
 * Single Responsibility: Data persistence operations
 * Repository Pattern: Abstracts database operations
 */

namespace BoundlessBooks\Repositories;

use PDO;
use BoundlessBooks\Models\DiscountCode;
use BoundlessBooks\Exceptions\DatabaseException;
use BoundlessBooks\Exceptions\InvalidDiscountCodeException;

class DiscountCodeRepository
{
    private PDO $db;
    private const TABLE = 'discount_code';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find discount code by ID
     */
    public function findById(int $id): ?DiscountCode
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE code_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            return $result ? $this->hydrate($result) : null;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error finding discount code: ' . $e->getMessage());
        }
    }

    /**
     * Find discount code by code string
     */
    public function findByCode(string $code): ?DiscountCode
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE code = ?");
            $stmt->execute([trim($code)]);
            $result = $stmt->fetch();
            
            return $result ? $this->hydrate($result) : null;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error finding discount code: ' . $e->getMessage());
        }
    }

    /**
     * Find all discount codes with optional filtering
     */
    public function findAll(string $search = '', string $status = ''): array
    {
        try {
            $query = "SELECT * FROM " . self::TABLE . " WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $query .= " AND code LIKE ?";
                $params[] = '%' . trim($search) . '%';
            }

            if (!empty($status)) {
                $query .= " AND status = ?";
                $params[] = trim($status);
            }

            $query .= " ORDER BY code_id DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            return array_map(fn($result) => $this->hydrate($result), $results);
        } catch (\PDOException $e) {
            throw new DatabaseException('Error fetching discount codes: ' . $e->getMessage());
        }
    }

    /**
     * Save a new discount code
     */
    public function create(DiscountCode $discountCode): DiscountCode
    {
        try {
            // Check if code already exists
            if ($this->findByCode($discountCode->getCode())) {
                throw new InvalidDiscountCodeException('Code already exists: ' . $discountCode->getCode());
            }

            $stmt = $this->db->prepare(
                "INSERT INTO " . self::TABLE . " (code, discount_percentage, status) VALUES (?, ?, ?)"
            );
            $stmt->execute([
                $discountCode->getCode(),
                $discountCode->getDiscountPercentage(),
                $discountCode->getStatus(),
            ]);

            return $this->findByCode($discountCode->getCode());
        } catch (\PDOException $e) {
            throw new DatabaseException('Error creating discount code: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing discount code
     */
    public function update(DiscountCode $discountCode): DiscountCode
    {
        try {
            if ($discountCode->getId() === null) {
                throw new DatabaseException('Cannot update discount code without ID');
            }

            $stmt = $this->db->prepare(
                "UPDATE " . self::TABLE . " SET code = ?, discount_percentage = ?, status = ? WHERE code_id = ?"
            );
            $stmt->execute([
                $discountCode->getCode(),
                $discountCode->getDiscountPercentage(),
                $discountCode->getStatus(),
                $discountCode->getId(),
            ]);

            return $this->findById($discountCode->getId());
        } catch (\PDOException $e) {
            throw new DatabaseException('Error updating discount code: ' . $e->getMessage());
        }
    }

    /**
     * Delete a discount code
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM " . self::TABLE . " WHERE code_id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            throw new DatabaseException('Error deleting discount code: ' . $e->getMessage());
        }
    }

    /**
     * Find active code by string
     */
    public function findActiveByCode(string $code): ?DiscountCode
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM " . self::TABLE . " WHERE code = ? AND status = 'active'"
            );
            $stmt->execute([trim($code)]);
            $result = $stmt->fetch();
            
            return $result ? $this->hydrate($result) : null;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error finding active discount code: ' . $e->getMessage());
        }
    }

    /**
     * Hydrate raw DB result into DiscountCode model
     */
    private function hydrate(object $result): DiscountCode
    {
        return new DiscountCode(
            $result->code,
            (float)$result->discount_percentage,
            $result->status,
            $result->code_id,
            $result->created_at ? new \DateTime($result->created_at) : null,
            $result->updated_at ? new \DateTime($result->updated_at) : null
        );
    }
}
