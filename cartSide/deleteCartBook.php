<?php

try {
    $_db = new PDO("mysql:host=localhost;dbname=ebookDB", "root", "");
    $_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Ensure that $_db is available globally
global $_db;

// Assume user_id = 1 for testing
$user_id = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $bookIdToDelete = $_POST['delete_book_id'];
    try {
        // Prepare the DELETE query to remove the book from the cart
        $deleteQuery = "
            DELETE FROM cart_item
            WHERE cart_id = (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0)
            AND book_id = :book_id";
        $deleteStmt = $_db->prepare($deleteQuery);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':book_id', $bookIdToDelete, PDO::PARAM_INT);
        $deleteStmt->execute();

        // If delete is successful, show the alert and redirect
        echo "
        <script>
            alert('Book ID $bookIdToDelete has been removed from your cart.');
            window.location.href = '../cartSide/cartMain.php';  
        </script>";
    } catch (Exception $e) {
        // Handle any errors
        echo "Error: " . $e->getMessage();
    }
}
?>