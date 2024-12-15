<?php
include '../pageFormat/base.php';

global $_db;

$user_id = $_SESSION['user_id'];
$book_id = $_GET['book_id'];

try {
    // Fetch the current active cart_id for the user
    $sql = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stm->execute();
    $cartId = $stm->fetchColumn(); // Fetch the cart_id

    if (!$cartId) {
        throw new Exception("No active cart found for the user.");
    }

    // Begin transaction
    $_db->beginTransaction();

    // Insert the book into the cart_item table
    $addItemIntoCartSql = "INSERT INTO cart_item (cart_id, book_id)
                           VALUES (:cart_id, :book_id)";
    $addItemIntoCartStmt = $_db->prepare($addItemIntoCartSql);
    $addItemIntoCartStmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $addItemIntoCartStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $addItemIntoCartStmt->execute();

    // Commit the transaction
    $_db->commit();
} catch (Exception $e) {
    // Rollback transaction in case of error
    $_db->rollBack();
    echo "Error: " . $e->getMessage();
}

header("Location: ../productCatalog/detail.php?book_id=$book_id"); // Replace with your desired URL
exit;
