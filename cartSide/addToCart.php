<?php

// Cart "Add to Cart" endpoint (from product catalog).
// Responsibilities:
// - Read user and book from request
// - Delegate business rules to CartService
// - Return a simple text message to the caller

include '../pageFormat/base.php';
require_once __DIR__ . '/cartService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? 0;
$bookId = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;

if (!$userId || !$bookId) {
    echo 'Invalid request.';
    exit;
}

try {
    $result = cart_add_book($_db, (int) $userId, $bookId);

    switch ($result) {
        case CartAddResult::ALREADY_OWNED:
            echo 'You already own this item.';
            break;
        case CartAddResult::ALREADY_IN_CART:
            echo 'Item already in cart.';
            break;
        case CartAddResult::ADDED_TO_CART:
        default:
            echo 'Item added to cart successfully.';
            break;
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
