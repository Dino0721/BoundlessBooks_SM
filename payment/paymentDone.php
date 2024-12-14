<?php
include '../pageFormat/base.php';

global $_db;

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    // Begin transaction
    $_db->beginTransaction();

    // Update the current active cart's paid column to 1
    $updateCartSql = "UPDATE cart SET paid = 1 WHERE user_id = :user_id AND paid = 0";
    $updateCartStmt = $_db->prepare($updateCartSql);
    $updateCartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $updateCartStmt->execute();

    // Insert a new cart for the user with paid = 0
    $insertCartSql = "INSERT INTO cart (user_id, paid) VALUES (:user_id, 0)";
    $insertCartStmt = $_db->prepare($insertCartSql);
    $insertCartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $insertCartStmt->execute();

    // Commit transaction
    $_db->commit();
} catch (Exception $e) {
    // Rollback transaction in case of error
    $_db->rollBack();
    echo "Error: " . $e->getMessage();
}
?>