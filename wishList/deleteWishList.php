<?php

// Controller endpoint for "Remove from wishlist" button.
// Uses WishlistService to encapsulate data access.

session_start();
include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

if (!isset($_POST['delete_book_id'], $_SESSION['user_id'])) {
    echo 'Error: Missing book ID or user ID.';
    exit;
}

$bookId = (int) $_POST['delete_book_id'];
$userId = (int) $_SESSION['user_id'];

try {
    wishlist_remove($_db, $userId, $bookId);

    // Fetch the book name for the success message
    $bookNameQuery = 'SELECT book_name FROM book_item WHERE book_id = :book_id';
    $bookNameStmt  = $_db->prepare($bookNameQuery);
    $bookNameStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $bookNameStmt->execute();
    $bookName = $bookNameStmt->fetchColumn();

    header('Location: ../wishList/wishList.php?success=1&book_name=' . urlencode($bookName));
    exit;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

/*
================================================================================
Documentation snapshot for assignment (Remove from wishlist)
================================================================================

**Before:**
```php
session_start();
include '../pageFormat/base.php';

try {
    $_db = new PDO("mysql:host=localhost;dbname=ebookDB", "root", "");
    $_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

global $_db;

if (isset($_POST['delete_book_id']) && isset($_SESSION['user_id'])) {
    $book_id = $_POST['delete_book_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $deleteQuery = "
            DELETE FROM wishlist 
            WHERE user_id = :user_id AND book_id = :book_id";
        $deleteStmt = $_db->prepare($deleteQuery);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $bookNameQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookNameStmt = $_db->prepare($bookNameQuery);
        $bookNameStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $bookNameStmt->execute();
        $bookName = $bookNameStmt->fetchColumn();

        header("Location: ../wishList/wishList.php?success=1&book_name=" . urlencode($bookName));
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Missing book ID or user ID.";
}
```

**After:**
```php
session_start();
include '../pageFormat/base.php';
require_once __DIR__ . '/wishlistService.php';

if (!isset($_POST['delete_book_id'], $_SESSION['user_id'])) {
    echo 'Error: Missing book ID or user ID.';
    exit;
}

$bookId = (int) $_POST['delete_book_id'];
$userId = (int) $_SESSION['user_id'];

try {
    wishlist_remove($_db, $userId, $bookId);

    $bookNameStmt  = $_db->prepare('SELECT book_name FROM book_item WHERE book_id = :book_id');
    $bookNameStmt->bindValue(':book_id', $bookId, PDO::PARAM_INT);
    $bookNameStmt->execute();
    $bookName = $bookNameStmt->fetchColumn();

    header('Location: ../wishList/wishList.php?success=1&book_name=' . urlencode($bookName));
    exit;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```
*/

