<?php
require_once '../pageFormat/base.php';
?>
<?php
try {
    $_db = new PDO("mysql:host=localhost;dbname=ebookDB", "root", "");
    $_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

global $_db;

$user_id = $_SESSION['user_id']; // Get the user_id from the session

if (!$user_id) {
    die("User is not logged in."); // Handle cases where user_id is not set
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $bookIdToDelete = $_POST['delete_book_id'];

    try {
        // Fetch the book name before deletion
        $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
        $bookStmt = $_db->prepare($bookQuery);
        $bookStmt->bindParam(':book_id', $bookIdToDelete, PDO::PARAM_INT);
        $bookStmt->execute();
        $bookName = $bookStmt->fetchColumn(); // Fetch the book name

        if (!$bookName) {
            throw new Exception("Book not found.");
        }

        // Prepare the DELETE query to remove the book from the cart_item table
        $deleteQuery = "
            DELETE FROM cart_item
            WHERE cart_id = (
                SELECT cart_id 
                FROM cart 
                WHERE user_id = :user_id AND paid = 0
            )
            AND book_id = :book_id";
        $deleteStmt = $_db->prepare($deleteQuery);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':book_id', $bookIdToDelete, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Check if the book was successfully deleted
        if ($deleteStmt->rowCount() > 0) {
            // If delete is successful, show the alert with book name and redirect
            echo "
            <script>
                alert('The book \"$bookName\" has been removed from your cart.');
                window.location.href = 'cartMain.php';  // Redirect to the cart page to show updated cart
            </script>";
        } else {
            throw new Exception("Failed to delete the book. It might not exist in your cart.");
        }
    } catch (Exception $e) {
        // Handle any errors
        echo "Error: " . $e->getMessage();
    }
}
?>
