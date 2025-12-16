<?php

// Wishlist add/remove endpoint used by AJAX toggles.
// Responsibilities:
// - Read input from POST
// - Delegate business logic to the WishlistService
// - Return a simple text response for the frontend

include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

$bookId   = isset($_POST['book_id']) ? (int) $_POST['book_id'] : 0;
$isChecked = isset($_POST['is_checked']) ? (bool) $_POST['is_checked'] : false;
$userId   = $_SESSION['user_id'] ?? 0;

if (!$userId || !$bookId) {
    echo 'invalid';
    exit;
}

try {
    if ($isChecked) {
        wishlist_add($_db, (int) $userId, $bookId);
        echo 'added';
    } else {
        wishlist_remove($_db, (int) $userId, $bookId);
        echo 'removed';
    }
} catch (PDOException $e) {
    echo 'error';
}

/*
================================================================================
Documentation snapshot for assignment (Toggle wishlist AJAX)
================================================================================

**Before:**
```php
include '../pageFormat/base.php';

$bookId    = $_POST['book_id'] ?? 0;
$isChecked = $_POST['is_checked'] ?? false;
$userId    = $_SESSION['user_id'] ?? 0;

try {
    if ($isChecked) {
        $stmt = $_db->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (:user_id, :book_id)");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        echo "added";
    } else {
        $stmt = $_db->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND book_id = :book_id");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        echo "removed";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
```

**After:**
```php
include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

$bookId    = isset($_POST['book_id']) ? (int) $_POST['book_id'] : 0;
$isChecked = isset($_POST['is_checked']) ? (bool) $_POST['is_checked'] : false;
$userId    = $_SESSION['user_id'] ?? 0;

if (!$userId || !$bookId) {
    echo 'invalid';
    exit;
}

try {
    if ($isChecked) {
        wishlist_add($_db, (int) $userId, $bookId);
        echo 'added';
    } else {
        wishlist_remove($_db, (int) $userId, $bookId);
        echo 'removed';
    }
} catch (PDOException $e) {
    echo 'error';
}
```
*/

