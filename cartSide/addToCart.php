<?php
include '../pageFormat/base.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

$user_id = $_SESSION['user_id'];
$book_id = $_GET['book_id'] ?? 0;

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
        // User already owns the book
        echo "You already own this item.";
        exit;
    }

    // Check if the item is already in the cart
    $checkCartSql = "SELECT 1 FROM cart_item WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0) AND book_id = :book_id";
    $stmt = $_db->prepare($checkCartSql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Item is already in the cart
        echo "Item already in cart.";
        exit;
    }

    // Fetch the current active cart_id for the user
    $sql = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
    $stmt = $_db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        // No active cart, create one
        $sql = "INSERT INTO cart (user_id) VALUES (:user_id)";
        $stmt = $_db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $cartId = $_db->lastInsertId();
    }

    // Insert the book into the cart_item table
    $addItemSql = "INSERT INTO cart_item (cart_id, book_id) VALUES (:cart_id, :book_id)";
    $stmt = $_db->prepare($addItemSql);
    $stmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    // Return success message
    echo "Item added to cart successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
