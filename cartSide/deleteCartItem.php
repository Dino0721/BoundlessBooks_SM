<?php
include '../pageFormat/base.php'; // Assuming this is where the database connection is made

// Check if the book_id is passed in the POST request
if (isset($_POST['book_id'])) {
    $bookId = $_POST['book_id'];
    $userId = $_SESSION['user_id'];  // Get the user_id from session

    // Prepare the SQL query to delete the item from the cart_item table
    $sql = "DELETE FROM cart_item WHERE book_id = :book_id AND cart_id IN (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0)";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':book_id', $bookId, PDO::PARAM_INT);
    $stm->bindParam(':user_id', $userId, PDO::PARAM_INT);

    // Execute the query and check if it was successful
    if ($stm->execute()) {
        echo 'success';  // Return success response
    } else {
        echo 'failure';  // Return failure response
    }
} else {
    echo 'failure';  // If book_id is not provided
}
?>
