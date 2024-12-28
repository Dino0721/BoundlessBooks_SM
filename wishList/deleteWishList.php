<?php
session_start();
include '../pageFormat/base.php'; // Assuming this is where the database connection is made

try {
    // Establish the database connection
    $_db = new PDO("mysql:host=localhost;dbname=ebookDB", "root", "");
    $_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    die("Could not connect to the database: " . $e->getMessage());
}

global $_db;

// Ensure that user_id and book_id are set in the POST request
if (isset($_POST['delete_book_id']) && isset($_SESSION['user_id'])) {
    // Get the book ID from the POST data and user ID from the session
    $book_id = $_POST['delete_book_id'];
    $user_id = $_SESSION['user_id'];  // Use the session user ID

    try {
        // Prepare SQL query to delete the book from the wishlist
        $deleteQuery = "
            DELETE FROM wishlist 
            WHERE user_id = :user_id AND book_id = :book_id";
        
        // Prepare and execute the query
        $deleteStmt = $_db->prepare($deleteQuery);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Fetch the book name after deletion for the success message
        $bookNameQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookNameStmt = $_db->prepare($bookNameQuery);
        $bookNameStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $bookNameStmt->execute();
        $bookName = $bookNameStmt->fetchColumn();

        // Redirect back to the wishlist page with success and the book name
        header("Location: ../wishList/wishList.php?success=1&book_name=" . urlencode($bookName));
        exit();
    } catch (Exception $e) {
        // Handle any errors
        echo "Error: " . $e->getMessage();
    }
} else {
    // Handle case where the necessary POST data is not set
    echo "Error: Missing book ID or user ID.";
}
?>
