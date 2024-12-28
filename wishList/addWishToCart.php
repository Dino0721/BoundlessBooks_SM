<?php
include '../pageFormat/base.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
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
        // User already owns the book
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
        // Item is already in the cart
        $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookStmt = $_db->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $bookStmt->execute();
        $bookName = $bookStmt->fetchColumn();

        header("Location: ../wishList/wishlist.php?success=4&book_name=" . urlencode($bookName));
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

    // Fetch the book name
    $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
    $bookStmt = $_db->prepare($bookQuery);
    $bookStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookStmt->execute();
    $bookName = $bookStmt->fetchColumn();

    // Redirect to wishlist page with success message
    header("Location: ../wishList/wishlist.php?success=2&book_id=" . urlencode($book_id));
    exit;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
