<?php
$_title = 'Manage Book';
include '../pageFormat/base.php';
include '../pageFormat/head.php';

global $_db;

try {
    $stmt = $_db->prepare("SELECT * FROM book_item");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure $books is always an array, even if no books are found
    if (!$books) {
        $bookImage = 'default.jpg';;
    }
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

$sortOrder = 'ASC'; // Default to ascending
if (isset($_GET['sort']) && ($_GET['sort'] === 'asc' || $_GET['sort'] === 'desc')) {
    $sortOrder = $_GET['sort']; // If 'asc' or 'desc' is set, use it
}

// Modify the query to use the sorting order
try {
    $stmt = $_db->prepare("SELECT * FROM book_item ORDER BY book_name " . $sortOrder);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure $books is always an array, even if no books are found
    if (!$books) {
        $bookImage = 'default.jpg';
    }
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

try {
    $stmt = $_db->prepare("SHOW COLUMNS FROM book_item LIKE 'book_category'");
    $stmt->execute();
    $categoryColumn = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($categoryColumn) {
        // Extract ENUM values from the 'Type' field
        preg_match("/^enum\('(.*)'\)$/", $categoryColumn['Type'], $matches);
        $bookCategories = explode("','", $matches[1]); // Convert to an array
    } else {
        $bookCategories = [];
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
    $bookCategory = $_POST['book_category'] ?? '';
    $bookImage = null;

    // Validate book price
    if ($bookPrice < 1) {
        echo "<p style='color: red;'>Error: Book price must be at least $1.</p>";
    } else {
        // Handle the image upload if provided
        if (isset($_FILES['book_photo']) && $_FILES['book_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $fileName = basename($_FILES['book_photo']['name']);
            $uploadFile = $uploadDir . $fileName;

            // Check if file is an image
            if (getimagesize($_FILES['book_photo']['tmp_name'])) {
                // Move the uploaded file to the server directory
                if (move_uploaded_file($_FILES['book_photo']['tmp_name'], $uploadFile)) {
                    $bookImage = $uploadFile; // Save the image path in the database
                } else {
                    echo "<p style='color: red;'>Error uploading the image.</p>";
                }
            } else {
                echo "<p style='color: red;'>Uploaded file is not a valid image.</p>";
            }
        }

        try {
            // Insert book into the database
            $stmt = $_db->prepare("INSERT INTO book_item (book_name, book_desc, book_price, book_status, book_category, book_photo) 
                VALUES (:name, :desc, :price, :status, :category, :image)");
            $stmt->execute([
                ':name' => $bookName,
                ':desc' => $bookDesc,
                ':price' => $bookPrice,
                ':status' => $bookStatus,
                ':category' => $bookCategory,
                ':image' => $bookImage, // Save the image path
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

// if (isset($_GET['delete_id'])) {
//     $deleteId = $_GET['delete_id'];

//     try {
//         // Delete the book from the database
//         $stmt = $_db->prepare("DELETE FROM book_item WHERE book_id = :id");
//         $stmt->execute([':id' => $deleteId]);

//         // Set delete message in session
//         $_SESSION['message'] = 'Book deleted successfully!';
//         header("Location: /productCatalog/manageBooks.php"); // Refresh the page to see the updated list
//         exit;
//     } catch (PDOException $e) {
//         die('Error: ' . $e->getMessage());
//     }
// }

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

    <div>
        <a href="../productCatalog/manageCategory.php">Manage Category</a> |
        <a href="../productCatalog/manageBooks.php">Manage Book</a> |
        <a href="../productCatalog/productCatalog.php">View Product Catalog</a> |
    </div>

    <h1>Add New Book</h1>
    <!-- Add Book Form Section -->
    <div class="add-book-container">
        <form action="" method="POST" enctype="multipart/form-data" class="add-book-form">
            <div class="add-book-row">
                <label for="book_name">Book Name:</label>
                <input type="text" id="book_name" name="book_name" required>
            </div>

            <div class="add-book-row">
                <label for="book_desc">Book Description:</label>
                <textarea id="book_desc" name="book_desc" required></textarea>
            </div>

            <div class="add-book-row">
                <label for="book_price">Book Price:</label>
                <input type="number" id="book_price" name="book_price" min="1" required>
            </div>

            <div class="add-book-row">
                <label for="book_status">Book Status:</label>
                <select id="book_status" name="book_status">
                    <option value="AVAILABLE">Available</option>
                    <option value="DISABLED">Disabled</option>
                </select>
            </div>

            <div class="add-book-row">
                <label for="book_category">Category:</label>
                <select id="book_category" name="book_category" required>
                    <?php foreach ($bookCategories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="add-book-row">
                <label for="book_image">Book Image:</label>
                <input type="file" id="book_image" name="book_image" accept="image/*">
            </div>

            <button type="submit">Add Book</button>
        </form>
    </div>



    <br>
    <!-- Book List Table -->
    <div class="table-section">
        <h2>Book List</h2>
        <button id="sortAscButton">Sort A-Z (Ascending)</button>
        <button id="sortDescButton">Sort Z-A (Descending)</button>

        <br><br>
        <p class="book-count">Total Book: <span><?= count($books) ?></span></p>
        <table class="book-table">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Book Photo</th>
                    <th>Book Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="6">No books found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['book_id']) ?></td>
                            <td>
                                <?php if ($book['book_photo']): ?>
                                    <img src="../images/<?= htmlspecialchars($book['book_photo']) ?>" alt="Book Image" class="book-image">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($book['book_name']) ?></td>
                            <td><?= htmlspecialchars($book['book_desc']) ?></td>
                            <td>$<?= number_format($book['book_price'], 2) ?></td>
                            <td><?= htmlspecialchars($book['book_status']) ?></td>
                            <td><?= htmlspecialchars($book['book_category']) ?></td>
                            <td>
                                <button class="edit-button" data-book-id="<?= $book['book_id'] ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
    <br>
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
    $(document).ready(function() {
        // When the edit button is clicked
        $('.edit-button').click(function() {
            var bookId = $(this).data('book-id'); // Get the book_id from the data attribute
            window.location.href = 'editBook.php?book_id=' + bookId; // Redirect to the edit page with the book_id in the query string
        });
    });
    // Automatically hide the message after 5 seconds
    setTimeout(function() {
        var message = document.querySelector('.alert-message');
        if (message) {
            message.style.display = 'none';
        }
    }, 2500); // 5000 ms = 5 seconds
    $(document).ready(function() {
        // When the sort button is clicked
        $('#sortAscButton').click(function() {
            // Add the "sort=asc" query string to the URL and reload the page
            window.location.href = window.location.pathname + '?sort=asc';
        });
    });
    $(document).ready(function() {
        // When the sort button is clicked for descending order
        $('#sortDescButton').click(function() {
            // Add the "sort=desc" query string to the URL and reload the page
            window.location.href = window.location.pathname + '?sort=desc';
        });
    });
</script>

</html>