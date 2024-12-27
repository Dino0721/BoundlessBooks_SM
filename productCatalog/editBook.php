<?php
// Database connection (assuming $_db is already defined in your base.php)
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';

if (isset($_SESSION['message'])) {
    echo "<p style='color: green; text-align: center; margin-top: 20px;'>" . htmlspecialchars($_SESSION['message']) . "</p>";
    unset($_SESSION['message']); // Clear the message after displaying it
}

$book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);
if (!$book_id) {
    echo "Invalid book ID!";
    exit;
}

// Fetch the current book details
$sql = "SELECT * FROM book_item WHERE book_id = :book_id";
$stmt = $_db->prepare($sql);
$stmt->execute([':book_id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_OBJ);

if (!$book) {
    echo "Book not found!";
    exit;
}

// Initialize variables with current book data (defaults when form is first loaded)
$book_name = $book->book_name;
$book_desc = $book->book_desc;
$book_price = $book->book_price;
$book_status = $book->book_status;
$book_category = $book->book_category;
$book_photo = $book->book_photo;
$current_pdf_path = $book->pdf_path; // Store the current PDF path

// Fetch the distinct book categories dynamically from the database
try {
    $stmt = $_db->query("SHOW COLUMNS FROM book_item WHERE Field = 'book_category'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    preg_match("/^enum\('(.*)'\)$/", $result['Type'], $matches);
    $bookCategories = array_map(function ($value) {
        return trim($value, "'");
    }, explode(',', $matches[1]));
} catch (PDOException $e) {
    echo "Error fetching book categories: " . $e->getMessage();
    exit;
}

// Fetch the possible book statuses dynamically (ENUM values from the database)
try {
    $stmt = $_db->query("SHOW COLUMNS FROM book_item WHERE Field = 'book_status'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    preg_match("/^enum\('(.*)'\)$/", $result['Type'], $matches);

    // Extract the values and format them
    $bookStatuses = array_map(function ($value) {
        return trim($value, "'");
    }, explode(',', $matches[1]));
} catch (PDOException $e) {
    echo "Error fetching book statuses: " . $e->getMessage();
    exit;
}

// Handle form submission for updating book details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get updated book details from the form
    $book_name = !empty($_POST['book_name']) ? $_POST['book_name'] : $book->book_name;
    $book_desc = !empty($_POST['book_desc']) ? $_POST['book_desc'] : $book->book_desc;
    $book_price = !empty($_POST['book_price']) ? $_POST['book_price'] : $book->book_price;
    $book_status = !empty($_POST['book_status']) ? $_POST['book_status'] : $book->book_status;
    $book_category = !empty($_POST['book_category']) ? $_POST['book_category'] : $book->book_category;

    // Handle image upload only if a new photo is provided
    if (isset($_FILES['book_photo']) && $_FILES['book_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/';
        $fileName = basename($_FILES['book_photo']['name']);
        $uploadPath = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['book_photo']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['book_photo']['tmp_name'], $uploadPath)) {
                $book_photo = $fileName;
            }
        }
    }

    // Handle PDF upload if provided
    if (isset($_FILES['book_pdf']) && $_FILES['book_pdf']['error'] === UPLOAD_ERR_OK) {
        $pdfUploadDir = '../books/';
        $pdfFileName = basename($_FILES['book_pdf']['name']);
        $pdfUploadFile = $pdfUploadDir . $pdfFileName;

        if (move_uploaded_file($_FILES['book_pdf']['tmp_name'], $pdfUploadFile)) {
            $new_pdf_path = 'books/' . $pdfFileName; // Relative path for database
        } else {
            echo "<p style='color: red;'>Error uploading PDF file.</p>";
        }
    }

    // Prepare an array to hold the fields to be updated
    $updateFields = [];
    $updateParams = [':book_id' => $book_id];

    // Check if each field has been modified and add to the update query if necessary
    if ($book_name !== $book->book_name) {
        $updateFields[] = "book_name = :book_name";
        $updateParams[':book_name'] = $book_name;
    }

    if ($book_desc !== $book->book_desc) {
        $updateFields[] = "book_desc = :book_desc";
        $updateParams[':book_desc'] = $book_desc;
    }

    if ($book_price !== $book->book_price) {
        $updateFields[] = "book_price = :book_price";
        $updateParams[':book_price'] = $book_price;
    }

    if ($book_status !== $book->book_status) {
        $updateFields[] = "book_status = :book_status";
        $updateParams[':book_status'] = $book_status;
    }

    if ($book_category !== $book->book_category) {
        $updateFields[] = "book_category = :book_category";
        $updateParams[':book_category'] = $book_category;
    }

    if ($book_photo !== $book->book_photo) {
        $updateFields[] = "book_photo = :book_photo";
        $updateParams[':book_photo'] = $book_photo;
    }

    if (isset($new_pdf_path)) {
        $updateFields[] = "pdf_path = :pdf_path";
        $updateParams[':pdf_path'] = $new_pdf_path;
    }

    // If there are fields to update, execute the update query
    if (count($updateFields) > 0) {
        $updateSQL = "UPDATE book_item SET " . implode(", ", $updateFields) . " WHERE book_id = :book_id";

        try {
            $stmt = $_db->prepare($updateSQL);
            $stmt->execute($updateParams);

            // Set the session message
            $_SESSION['message'] = "Book updated successfully!";
            header("Location: editBook.php?book_id=$book_id"); // Redirect back to the same page
            exit;
        } catch (PDOException $e) {
            echo "<p style='color: red; text-align: center; margin-top: 20px;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
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
        <div>
            <a href="../productCatalog/manageCategory.php">Manage Category</a> |
            <a href="../productCatalog/manageBooks.php">Manage Book</a> |
            <a href="../productCatalog/productCatalog.php">View Product Catalog</a> |
        </div>
        <h1>Edit Book Details</h1>

        <!-- Edit Book Form -->
        <form method="POST" action="editBook.php?book_id=<?= $book->book_id ?>" class="edit-book-form" id="edit-book-form" enctype="multipart/form-data">
            <!-- Add Image Upload -->
            <label for="book_photo">Book Image:</label>
            <input type="file" id="book_photo" name="book_photo" accept="image/*">
            <?php if (!empty($book->book_photo)): ?>
                <div>
                    <p>Current Image:</p>
                    <img src="../images/<?= htmlspecialchars($book->book_photo) ?>" alt="Book Image" style="max-width: 200px; height: auto;">
                </div>
            <?php endif; ?>
            <label for="book_name">Book Name:</label>
            <input type="text" id="book_name" name="book_name" value="<?= htmlspecialchars($book->book_name) ?>" required>

            <label for="book_desc">Book Description:</label>
            <textarea id="book_desc" name="book_desc" required><?= htmlspecialchars($book->book_desc) ?></textarea>

            <label for="book_price">Book Price:</label>
            <input type="number" id="book_price" name="book_price" value="<?= number_format($book->book_price, 2) ?>" step="0.01" required>

            <!-- Populate Book Status Dynamically -->
            <label for="book_status">Book Status:</label>
            <select id="book_status" name="book_status" required>
                <?php foreach ($bookStatuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $book->book_status === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst(strtolower($status))) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Add Category Selection -->
            <label for="book_category">Book Category:</label>
            <select id="book_category" name="book_category" required>
                <?php foreach ($bookCategories as $category): ?>
                    <option value="<?= htmlspecialchars($category); ?>" <?= $category === $book_category ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="book_pdf">Book PDF:</label>
            <input type="file" id="book_pdf" name="book_pdf" accept=".pdf">
            <?php if (!empty($current_pdf_path)): ?>
                <p>Current PDF: <a href="../<?= htmlspecialchars($current_pdf_path) ?>" target="_blank"><?= basename($current_pdf_path) ?></a></p>
            <?php endif; ?>

            <button type="submit">Update Book</button>
        </form>
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
<script>
    setTimeout(() => {
        const messageElement = document.querySelector('p[style*="color: green"]');
        if (messageElement) {
            messageElement.style.display = 'none';
        }
    }, 2500);
</script>

</html>