<?php
// Database connection (assuming $_db is already defined in your base.php)
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';

// Get the book ID from the URL query string
$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get updated book details from the form
    $book_name = trim($_POST['book_name']);
    $book_desc = trim($_POST['book_desc']);
    $book_price = (float)$_POST['book_price'];
    $book_status = $_POST['book_status'];

    // Prepare the update SQL statement
    $sql = "UPDATE book_item SET book_name = :book_name, book_desc = :book_desc, book_price = :book_price, book_status = :book_status WHERE book_id = :book_id";

    try {
        // Update the book in the database using PDO
        $stmt = $_db->prepare($sql);
        $stmt->execute([
            ':book_name' => $book_name,
            ':book_desc' => $book_desc,
            ':book_price' => $book_price,
            ':book_status' => $book_status,
            ':book_id' => $book_id
        ]);
        // Redirect to the product catalog page after successful update
        header("Location: productCatalog.php");
        exit;
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

// Fetch the current book details to display in the form
$sql = "SELECT * FROM book_item WHERE book_id = :book_id";
$stmt = $_db->prepare($sql);
$stmt->execute([':book_id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_OBJ);

if (!$book) {
    echo "Book not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book | BOUNDLESSBOOKS</title>
    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <main>
        <h1>Edit Book Details</h1>

        <!-- Edit Book Form -->
        <form method="POST" action="editBook.php?book_id=<?= $book->book_id ?>" class="edit-book-form" id="edit-book-form">
            <label for="book_name">Book Name:</label>
            <input type="text" id="book_name" name="book_name" value="<?= htmlspecialchars($book->book_name) ?>" required>

            <label for="book_desc">Book Description:</label>
            <textarea id="book_desc" name="book_desc" required><?= htmlspecialchars($book->book_desc) ?></textarea>

            <label for="book_price">Book Price:</label>
            <input type="number" id="book_price" name="book_price" value="<?= number_format($book->book_price, 2) ?>" step="0.01" required>

            <label for="book_status">Book Status:</label>
            <select id="book_status" name="book_status" required>
                <option value="AVAILABLE" <?= $book->book_status === 'AVAILABLE' ? 'selected' : '' ?>>Available</option>
                <option value="DISABLED" <?= $book->book_status === 'DISABLED' ? 'selected' : '' ?>>Disabled</option>
            </select>

            <button type="submit">Update Book</button>
        </form>

        <a href="<?= $_SERVER['HTTP_REFERER'] ?? 'index.php' ?>">Back to Manage Books</a>
    </main>

    <script>
        // JavaScript confirmation before submitting the form
        document.getElementById("edit-book-form").addEventListener("submit", function(event) {
            // Display confirmation dialog
            var confirmed = confirm("Are you sure you want to update the book details?");
            if (!confirmed) {
                // Prevent form submission if the user cancels
                event.preventDefault();
            }
        });
    </script>
    <script src="../js/main.js"></script>
</body>

</html>