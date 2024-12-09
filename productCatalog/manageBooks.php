<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

global $_db;

try {
    $stmt = $_db->prepare("SELECT * FROM book_item");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure $books is always an array, even if no books are found
    if (!$books) {
        $books = [];
    }
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $bookName = $_POST['book_name'] ?? '';
    $bookDesc = $_POST['book_desc'] ?? '';
    $bookPrice = $_POST['book_price'] ?? 0;
    $bookStatus = $_POST['book_status'] ?? 'AVAILABLE';

    // Validate book price
    if ($bookPrice < 1) {
        echo "<p style='color: red;'>Error: Book price must be at least $1.</p>";
    } else {
        try {
            // Insert book into the database
            $stmt = $_db->prepare("INSERT INTO book_item (book_name, book_desc, book_price, book_status) VALUES (:name, :desc, :price, :status)");
            $stmt->execute([
                ':name' => $bookName,
                ':desc' => $bookDesc,
                ':price' => $bookPrice,
                ':status' => $bookStatus,
            ]);
            header("Location: /productCatalog/manageBooks.php");
            exit;
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    try {
        // Delete the book from the database
        $stmt = $_db->prepare("DELETE FROM book_item WHERE book_id = :id");
        $stmt->execute([':id' => $deleteId]);
        echo "<script>alert('Book deleted successfully!');</script>";
        header("Location: /productCatalog/manageBooks.php"); // Refresh the page to see the updated list
        exit;
    } catch (PDOException $e) {
        die('Error: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <h1>Add New Book</h1>
    <form action="" method="POST">
        <label for="book_name">Book Name:</label>
        <input type="text" id="book_name" name="book_name" required><br>

        <label for="book_desc">Book Description:</label>
        <textarea id="book_desc" name="book_desc" required></textarea><br>

        <label for="book_price">Book Price:</label>
        <input type="number" id="book_price" name="book_price" min="1" required><br>

        <label for="book_status">Book Status:</label>
        <select id="book_status" name="book_status">
            <option value="AVAILABLE">Available</option>
            <option value="DISABLED">Disabled</option>
        </select><br>

        <button type="submit">Add Book</button>
    </form>
    <br>
    <!-- Table Showing All Books -->
    <h2>Book List</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Book Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($books)): ?>
                <tr>
                    <td colspan="5">No books found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['book_name']) ?></td>
                        <td><?= htmlspecialchars($book['book_desc']) ?></td>
                        <td>$<?= number_format($book['book_price'], 2) ?></td>
                        <td><?= htmlspecialchars($book['book_status']) ?></td>
                        <td>
                            <!-- Edit button that redirects to edit page -->
                            <a href="editBook.php?book_id=<?= $book['book_id'] ?>">Edit</a> |
                            <!-- Delete button -->
                            <a href="manageBooks.php?delete_id=<?= $book['book_id'] ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="../productCatalog/productCatalog.php">Go to Listing</a>
</body>

</html>