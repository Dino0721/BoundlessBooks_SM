<?php
/**
 * OrderRepository
 * Single Responsibility: Data persistence operations
 * Repository Pattern: Abstracts database operations
 */

namespace BoundlessBooks\Repositories;

use PDO;
use BoundlessBooks\Models\Order;
use BoundlessBooks\Exceptions\DatabaseException;
use BoundlessBooks\Exceptions\OrderNotFoundException;

class OrderRepository
{
    private PDO $db;
    private const TABLE = 'book_ownership';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find order by ID (ownership_id)
     */
    public function findById(int $id): ?Order
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT bo.ownership_id, bo.user_id, bo.book_id, b.book_name, b.book_price, 
                        bo.purchase_date, bo.purchase_time, b.pdf_path
                 FROM " . self::TABLE . " bo
                 JOIN book_item b ON b.book_id = bo.book_id
                 WHERE bo.ownership_id = ?"
            );
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            return $result ? $this->hydrate($result) : null;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error finding order: ' . $e->getMessage());
        }
    }

    /**
     * Find all orders for a user
     */
    public function findByUserId(int $userId, string $search = ''): array
    {
        try {
            $query = "SELECT bo.ownership_id, bo.user_id, bo.book_id, b.book_name, b.book_price, 
                             bo.purchase_date, bo.purchase_time, b.pdf_path
                      FROM " . self::TABLE . " bo
                      JOIN book_item b ON b.book_id = bo.book_id
                      WHERE bo.user_id = ?";
            $params = [$userId];

            if (!empty($search)) {
                $query .= " AND b.book_name LIKE ?";
                $params[] = '%' . trim($search) . '%';
            }

            $query .= " ORDER BY bo.purchase_date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            return array_map(fn($result) => $this->hydrate($result), $results);
        } catch (\PDOException $e) {
            throw new DatabaseException('Error fetching user orders: ' . $e->getMessage());
        }
    }

    /**
     * Find all orders (admin listing)
     */
    public function findAll(string $searchType = 'user', string $searchTerm = ''): array
    {
        try {
            $query = "SELECT bo.ownership_id, bo.user_id, bo.book_id, b.book_name, b.book_price, 
                             bo.purchase_date, bo.purchase_time, b.pdf_path, u.email
                      FROM " . self::TABLE . " bo
                      JOIN book_item b ON b.book_id = bo.book_id
                      JOIN user u ON u.user_id = bo.user_id
                      WHERE 1=1";
            $params = [];

            if (!empty($searchTerm)) {
                if ($searchType === 'user') {
                    $query .= " AND u.email LIKE ?";
                } elseif ($searchType === 'book') {
                    $query .= " AND b.book_name LIKE ?";
                } elseif ($searchType === 'order') {
                    $query .= " AND bo.ownership_id LIKE ?";
                }
                $params[] = '%' . trim($searchTerm) . '%';
            }

            $query .= " ORDER BY bo.purchase_date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            return array_map(fn($result) => $this->hydrate($result), $results);
        } catch (\PDOException $e) {
            throw new DatabaseException('Error fetching all orders: ' . $e->getMessage());
        }
    }

    /**
     * Check if user owns a book
     */
    public function userOwnsBook(int $userId, int $bookId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM " . self::TABLE . " WHERE user_id = ? AND book_id = ?"
            );
            $stmt->execute([$userId, $bookId]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error checking ownership: ' . $e->getMessage());
        }
    }

    /**
     * Get book PDF path by ID
     */
    public function getBookPdfPath(int $bookId): ?string
    {
        try {
            $stmt = $this->db->prepare("SELECT pdf_path FROM book_item WHERE book_id = ?");
            $stmt->execute([$bookId]);
            $result = $stmt->fetch();
            return $result ? $result->pdf_path : null;
        } catch (\PDOException $e) {
            throw new DatabaseException('Error fetching PDF path: ' . $e->getMessage());
        }
    }

    /**
     * Hydrate raw DB result into Order model
     */
    private function hydrate(object $result): Order
    {
        return new Order(
            (int)$result->user_id,
            (int)$result->book_id,
            $result->book_name,
            (float)$result->book_price,
            new \DateTime($result->purchase_date),
            new \DateTime($result->purchase_time),
            $result->pdf_path ?? null,
            $result->ownership_id
        );
    }
}
