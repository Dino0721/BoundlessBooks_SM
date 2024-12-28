<?php
include '../pageFormat/base.php';

$bookId = $_POST['book_id'] ?? 0;
$isChecked = $_POST['is_checked'] ?? false;
$userId = $_SESSION['user_id'] ?? 0; // Assuming the user is logged in

try {
    if ($isChecked) {
        // Add the book to the wishlist
        $stmt = $_db->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (:user_id, :book_id)");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        echo "added";
    } else {
        // Remove the book from the wishlist
        $stmt = $_db->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND book_id = :book_id");
        $stmt->execute([':user_id' => $userId, ':book_id' => $bookId]);
        echo "removed";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
