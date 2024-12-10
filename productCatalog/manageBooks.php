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

            // Set success message in session
            $_SESSION['message'] = 'Book added successfully!';
            header("Location: /productCatalog/manageBooks.php");
            exit;
        } catch (PDOException $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    try {
        // Delete the book from the database
        $stmt = $_db->prepare("DELETE FROM book_item WHERE book_id = :id");
        $stmt->execute([':id' => $deleteId]);

        // Set delete message in session
        $_SESSION['message'] = 'Book deleted successfully!';
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
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert-message">
            <?= htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); // Clear the message after displaying it 
        ?>
    <?php endif; ?>

    <h1>Add New Book</h1>

    <!-- Add Book Form Section -->
    <div class="add-book-container">
        <form action="" method="POST" class="add-book-form">
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
    </div>

    <br>
    <!-- Book List Table -->
    <div class="table-section">
        <h2>Book List</h2>
        <table class="book-table">
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
                                <a href="editBook.php?book_id=<?= $book['book_id'] ?>" class="action-button">Edit</a> |
                                <a href="manageBooks.php?delete_id=<?= $book['book_id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <br>
    <a href="../productCatalog/productCatalog.php">Go to Listing</a>
</body>
<script>
    $(document).ready(function() {
        // Attach the confirmation before form submission
        $('.add-book-form').on('submit', function(e) {
            var confirmation = confirm("Are you sure you want to add this book?");
            if (!confirmation) {
                e.preventDefault(); // Prevent form submission if user clicks "Cancel"
            }
        });
    });
    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        var message = document.querySelector('.alert-message');
        if (message) {
            message.style.display = 'none';
        }
    }, 2500); // 5000 ms = 5 seconds
</script>

</html>