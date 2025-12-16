<?php

// Controller endpoint for "Add wishlist item to cart" button.
// Delegates business rules to WishlistService and only handles redirects/messages.

include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? 0;
$bookId = isset($_POST['add_book_id']) ? (int) $_POST['add_book_id'] : 0;

if (!$userId || !$bookId) {
    echo 'Invalid request.';
    exit;
}

try {
    $result = wishlist_add_book_to_cart($_db, (int) $userId, $bookId);

    // Fetch the book name once for messages
    $bookQuery = 'SELECT book_name FROM book_item WHERE book_id = :book_id';
    $bookStmt  = $_db->prepare($bookQuery);
    $bookStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $bookStmt->execute();
    $bookName = $bookStmt->fetchColumn();

    switch ($result) {
        case WishlistAddToCartResult::ALREADY_OWNED:
            header('Location: ../wishList/wishList.php?success=3&book_name=' . urlencode($bookName));
            break;

        case WishlistAddToCartResult::ALREADY_IN_CART:
            header('Location: ../wishList/wishList.php?success=4&book_name=' . urlencode($bookName));
            break;

        case WishlistAddToCartResult::ADDED_TO_CART:
        default:
            header('Location: ../wishList/wishList.php?success=2&book_id=' . urlencode((string) $bookId));
            break;
    }
    exit;
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

/*
================================================================================
Documentation snapshot for assignment (Wishlist → Cart flow)
================================================================================
```
**Before:**
<?php
include '../pageFormat/base.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['add_book_id'] ?? 0;

if (!$user_id || !$book_id) {
    echo "Invalid request.";
    exit;
}

try {
    // Check if the user already owns the book
    $checkOwnershipSql = "SELECT 1 FROM book_ownership WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $_db->prepare($checkOwnershipSql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookStmt = $_db->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $bookStmt->execute();
        $bookName = $bookStmt->fetchColumn();

        header("Location: ../wishList/wishlist.php?success=3&book_name=" . urlencode($bookName));
        exit;
    }

    // Check if the book is already in the cart
    $checkCartSql = "SELECT 1 FROM cart_item WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0) AND book_id = :book_id";
    $stmt = $_db->prepare($checkCartSql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookStmt = $_db->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $bookStmt->execute();
        $bookName = $bookStmt->fetchColumn();

        header("Location: ../wishList/wishlist.php?success=4&book_name=" . urlencode($bookName));
        exit;
    }

    // Fetch or create active cart
    $sql = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        $sql = "INSERT INTO cart (user_id) VALUES (:user_id)";
        $stmt = $_db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cartId = $_db->lastInsertId();
    }

    $addItemSql = "INSERT INTO cart_item (cart_id, book_id) VALUES (:cart_id, :book_id)";
    $stmt = $_db->prepare($addItemSql);
    $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: ../wishList/wishlist.php?success=2&book_id=" . urlencode($book_id));
    exit;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

```
**After:**
```phpphp
include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? 0;
$bookId = isset($_POST['add_book_id']) ? (int) $_POST['add_book_id'] : 0;

if (!$userId || !$bookId) {
    echo 'Invalid request.';
    exit;
}

try {
    $result = wishlist_add_book_to_cart($_db, (int) $userId, $bookId);

    $bookStmt = $_db->prepare('SELECT book_name FROM book_item WHERE book_id = :book_id');
    $bookStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $bookStmt->execute();
    $bookName = $bookStmt->fetchColumn();

    switch ($result) {
        case WishlistAddToCartResult::ALREADY_OWNED:
            header('Location: ../wishList/wishList.php?success=3&book_name=' . urlencode($bookName));
            break;
        case WishlistAddToCartResult::ALREADY_IN_CART:
            header('Location: ../wishList/wishList.php?success=4&book_name=' . urlencode($bookName));
            break;
        default:
            header('Location: ../wishList/wishList.php?success=2&book_id=' . urlencode((string) $bookId));
            break;
    }
    exit;
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
```

*/
