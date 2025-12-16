<?php

// AJAX endpoint for deleting an item from the cart (e.g. via JS).
// Uses CartService so all delete logic lives in one place.

include '../pageFormat/base.php';
require_once __DIR__ . '/cartService.php';

if (!isset($_POST['book_id'], $_SESSION['user_id'])) {
    echo 'failure';
    return;
}

$bookId = (int) $_POST['book_id'];
$userId = (int) $_SESSION['user_id'];

try {
    cart_remove_book($_db, $userId, $bookId);
    echo 'success';
} catch (PDOException $e) {
    echo 'failure';
}

/*
================================================================================
Documentation snapshot for assignment (Remove from cart – AJAX)
================================================================================

**Before:**
```php
include '../pageFormat/base.php';

if (isset($_POST['book_id'])) {
    $bookId = $_POST['book_id'];
    $userId = $_SESSION['user_id'];

    $sql = \"DELETE FROM cart_item WHERE book_id = :book_id AND cart_id IN
            (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0)\";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':book_id', $bookId, PDO::PARAM_INT);
    $stm->bindParam(':user_id', $userId, PDO::PARAM_INT);

    echo $stm->execute() ? 'success' : 'failure';
} else {
    echo 'failure';
}
```

**After:**
```php
include '../pageFormat/base.php';
require_once __DIR__ . '/cartService.php';

$bookId = (int) $_POST['book_id'];
$userId = (int) $_SESSION['user_id'];

cart_remove_book($_db, $userId, $bookId);
echo 'success';
```
*/
