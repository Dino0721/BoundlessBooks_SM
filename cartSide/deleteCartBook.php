<?php

// Controller endpoint for "Delete" button in cart table.
// Uses CartService for data access, keeps this file focused on UI feedback.

require_once '../pageFormat/base.php';
require_once __DIR__ . '/cartService.php';

$userId = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    die('User is not logged in.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $bookIdToDelete = (int) $_POST['delete_book_id'];

    try {
        // Fetch the book name before deletion for nicer UX
        $bookStmt = $_db->prepare('SELECT book_name FROM book_item WHERE book_id = :book_id');
        $bookStmt->bindValue(':book_id', $bookIdToDelete, PDO::PARAM_INT);
        $bookStmt->execute();
        $bookName = $bookStmt->fetchColumn();

        if (!$bookName) {
            throw new Exception('Book not found.');
        }

        cart_remove_book($_db, (int) $userId, $bookIdToDelete);

        echo "
            <script>
                alert('The book \"$bookName\" has been removed from your cart.');
                window.location.href = 'cartMain.php';
            </script>";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

/*
================================================================================
Documentation snapshot for assignment (Remove from cart – form submit)
================================================================================

**Before:**
```php
require_once '../pageFormat/base.php';

$_db = new PDO(\"mysql:host=localhost;dbname=ebookDB\", \"root\", \"\");

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $bookIdToDelete = $_POST['delete_book_id'];

    // fetch book name...
    // manual DELETE FROM cart_item WHERE cart_id = (SELECT ...) AND book_id = :book_id
}
```

**After:**
```php
require_once '../pageFormat/base.php';
require_once __DIR__ . '/cartService.php';

$userId = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $bookIdToDelete = (int) $_POST['delete_book_id'];

    // fetch book name for message
    cart_remove_book($_db, (int) $userId, $bookIdToDelete);
    // alert + redirect
}
```
*/
