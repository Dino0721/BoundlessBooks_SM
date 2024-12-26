<?php
// Database connection (assuming $_db is already defined in your base.php)
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';

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
$book_photo = $book->book_photo;  // Default to current photo unless a new one is uploaded

// Fetch the possible book statuses dynamically (ENUM values from the database)
try {
    $stmt = $_db->query("SHOW COLUMNS FROM book_item WHERE Field = 'book_status'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    preg_match("/^enum\((.*)\)$/", $result['Type'], $matches);
    $bookStatuses = array_map(function ($value) {
        return trim($value, "'");
    }, explode(',', $matches[1]));
} catch (PDOException $e) {
    echo "Error fetching ENUM values: " . $e->getMessage();
    exit;
}

// You might want to fetch the categories from the database if they are stored in another table
$bookCategories = ['Fiction', 'Non-Fiction', 'Sci-Fi', 'Biography']; // Example categories, you can update as needed

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
                $book_photo = $fileName;  // Update photo if new one uploaded
            }
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

    // If there are fields to update, execute the update query
    if (count($updateFields) > 0) {
        // Build the update SQL dynamically
        $updateSQL = "UPDATE book_item SET " . implode(", ", $updateFields) . " WHERE book_id = :book_id";

        try {
            $stmt = $_db->prepare($updateSQL);
            $stmt->execute($updateParams);
            echo "Book updated successfully!";
            header("Location: productCatalog.php"); // Redirect after successful update
            exit;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo "No changes detected!";
        exit;
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

        <a href="<?= $_SERVER['HTTP_REFERER'] ?? 'index.php' ?>">Back to Manage Books</a>
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
            <label for="book_category">Category:</label>
            <select id="book_category" name="book_category" required>
                <?php foreach ($bookCategories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= $book->book_category === $category ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>



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

</html>