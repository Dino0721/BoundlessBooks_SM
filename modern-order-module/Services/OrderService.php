<?php
/**
 * OrderService
 * Single Responsibility: Business logic for order operations
 * Orchestrates between Repository, Model, and File System
 */

namespace BoundlessBooks\Services;

use BoundlessBooks\Models\Order;
use BoundlessBooks\Repositories\OrderRepository;
use BoundlessBooks\Exceptions\OrderNotFoundException;
use BoundlessBooks\Exceptions\AccessDeniedException;
use BoundlessBooks\Exceptions\FileNotFoundException;
use BoundlessBooks\Exceptions\ValidationException;

class OrderService
{
    private OrderRepository $repository;
    private array $config;

    public function __construct(OrderRepository $repository, array $config = [])
    {
        $this->repository = $repository;
        $this->config = $config;
    }

    /**
     * Get user's order history
     */
    public function getUserOrderHistory(int $userId, string $search = ''): array
    {
        return $this->repository->findByUserId($userId, $search);
    }

    /**
     * Get single order
     */
    public function getOrder(int $orderId): ?Order
    {
        return $this->repository->findById($orderId);
    }

    /**
     * Get all orders (admin only)
     */
    public function getAllOrders(string $searchType = 'user', string $searchTerm = ''): array
    {
        // Validate search type
        $validTypes = ['user', 'book', 'order'];
        if (!in_array($searchType, $validTypes)) {
            throw new ValidationException('Invalid search type');
        }

        return $this->repository->findAll($searchType, $searchTerm);
    }

    /**
     * Get downloadable book for user
     * Ensures user has ownership before allowing download
     */
    public function getDownloadableBook(int $userId, int $bookId): Order
    {
        // Check if user owns the book
        if (!$this->repository->userOwnsBook($userId, $bookId)) {
            throw new AccessDeniedException('You do not have access to download this book');
        }

        // Get the order/ownership record
        $order = $this->repository->findById($bookId);
        if (!$order) {
            throw new OrderNotFoundException($bookId);
        }

        return $order;
    }

    /**
     * Get PDF file path with validation
     */
    public function getPdfFilePath(int $bookId): string
    {
        $pdfPath = $this->repository->getBookPdfPath($bookId);

        if (!$pdfPath) {
            throw new FileNotFoundException('No PDF path found for book ID: ' . $bookId);
        }

        // Validate and resolve the path
        $resolvedPath = realpath(__DIR__ . '/../../' . $pdfPath);

        if (!$resolvedPath || !file_exists($resolvedPath) || !is_readable($resolvedPath)) {
            throw new FileNotFoundException($pdfPath);
        }

        // Validate file extension
        $ext = strtolower(pathinfo($resolvedPath, PATHINFO_EXTENSION));
        $allowed = $this->config['file']['allowed_extensions'] ?? ['pdf'];
        if (!in_array($ext, $allowed)) {
            throw new ValidationException('Invalid file type: ' . $ext);
        }

        // Validate file size
        $maxSize = $this->config['file']['max_download_size'] ?? 100 * 1024 * 1024;
        if (filesize($resolvedPath) > $maxSize) {
            throw new ValidationException('File size exceeds maximum allowed');
        }

        return $resolvedPath;
    }

    /**
     * Count user's orders
     */
    public function countUserOrders(int $userId): int
    {
        $orders = $this->repository->findByUserId($userId);
        return count($orders);
    }

    /**
     * Get total spent by user
     */
    public function getUserTotalSpent(int $userId): float
    {
        $orders = $this->repository->findByUserId($userId);
        return array_sum(array_map(fn($order) => $order->getBookPrice(), $orders));
    }

    /**
     * Get most recent order for user
     */
    public function getUserMostRecentOrder(int $userId): ?Order
    {
        $orders = $this->repository->findByUserId($userId);
        return count($orders) > 0 ? $orders[0] : null;
    }
}
