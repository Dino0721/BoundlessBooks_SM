<?php
/**
 * Exceptions for Discount Management
 * Custom exception classes following PSR-11
 */

namespace BoundlessBooks\Exceptions;

class DiscountException extends \Exception {}

class InvalidDiscountCodeException extends DiscountException
{
    public function __construct(string $code)
    {
        parent::__construct("Invalid discount code: $code");
    }
}

class DuplicateDiscountCodeException extends DiscountException
{
    public function __construct(string $code)
    {
        parent::__construct("Discount code already exists: $code");
    }
}

class DiscountValidationException extends DiscountException {}

class DatabaseException extends DiscountException {}
