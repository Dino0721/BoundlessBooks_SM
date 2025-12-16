<?php
/**
 * Exceptions for Order Management
 * Custom exception classes following PSR-11
 */

namespace BoundlessBooks\Exceptions;

class OrderException extends \Exception {}

class OrderNotFoundException extends OrderException
{
    public function __construct(int $orderId)
    {
        parent::__construct("Order not found: $orderId");
    }
}

class BookNotFoundException extends OrderException
{
    public function __construct(int $bookId)
    {
        parent::__construct("Book not found: $bookId");
    }
}

class FileNotFoundException extends OrderException
{
    public function __construct(string $filePath)
    {
        parent::__construct("File not found or inaccessible: $filePath");
    }
}

class AccessDeniedException extends OrderException
{
    public function __construct(string $reason = "Access denied")
    {
        parent::__construct($reason);
    }
}

class ValidationException extends OrderException {}

class DatabaseException extends OrderException {}
