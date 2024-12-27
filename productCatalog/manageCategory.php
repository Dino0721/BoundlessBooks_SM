<?php
$_title = 'Manage Category';
include '../pageFormat/base.php';
include '../pageFormat/head.php';


global $_db;

try {
    $stmt = $_db->query("SHOW COLUMNS FROM book_item LIKE 'book_category'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extract the ENUM values
    preg_match("/^enum\('(.*)'\)$/", $row['Type'], $matches);
    $categories = explode("','", $matches[1]);
} catch (PDOException $e) {
    die('Error fetching categories: ' . $e->getMessage());
}

// Sort categories in ascending order
sort($categories);

// Query to count books for each category
$categoryCounts = [];
foreach ($categories as $category) {
    $stmt = $_db->prepare("SELECT COUNT(*) FROM book_item WHERE book_category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    $categoryCounts[$category] = $count;
}

// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $newCategory = trim($_POST['new_category']); // Normalize input

    if (empty($newCategory)) {
        $_SESSION['message'] = "Error: Category name cannot be empty.";
        $_SESSION['message_type'] = 'error';
        header("Location: /productCatalog/manageCategory.php");
        exit;
    } elseif (in_array(strtolower($newCategory), array_map('strtolower', $categories))) { // Case-insensitive check
        // If the category already exists in the system
        $_SESSION['message'] = "$newCategory is already in the system!";
        $_SESSION['message_type'] = 'error';
        header("Location: /productCatalog/manageCategory.php");
        exit;
    } else {
        try {
            // Add the new category to the ENUM column
            $categories[] = $newCategory; // Add the new category to the existing array
            $enumValues = implode("','", $categories); // Create the ENUM definition

            $alterSQL = "ALTER TABLE book_item MODIFY book_category ENUM('$enumValues') NOT NULL";
            $_db->exec($alterSQL);

            // Success message
            $_SESSION['message'] = "Category '$newCategory' added successfully!";
            $_SESSION['message_type'] = 'success';
            header("Location: /productCatalog/manageCategory.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['message'] = "<p style='color: red;'>Error adding category: " . $e->getMessage() . "</p>";
            header("Location: /productCatalog/manageCategory.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div>
        <a href="../productCatalog/manageCategory.php">Manage Category</a> |
        <a href="../productCatalog/manageBooks.php">Manage Book</a> |
        <a href="../productCatalog/productCatalog.php">View Product Catalog</a> |
    </div>
    <div class="category-management">
        <h1>Manage Book Categories</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert-message <?= isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '' ?>">
                <?= htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']); // Clear the message after displaying it
            unset($_SESSION['message_type']); // Clear the message type
            ?>
        <?php endif; ?>

        <div class="add-category-container">
            <h3>Add New Category</h3>
            <form method="POST" id="addCategoryForm">
                <div class="add-category-row">
                    <input type="text" name="new_category" id="newCategory" placeholder="Enter new category" required class="add-category-input">
                    <button type="submit" class="add-category-button">Add Category</button>
                </div>
            </form>
        </div>

        <!-- Existing Categories Table -->
        <div class="table-section">
            <h2>Category List</h2>
            <p class="book-count">Total Categories: <span><?= count($categories) ?></span></p>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Category Name</th>
                        <th>Books in Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3">No categories found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $category): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($category) ?></td>
                                <td><?= $categoryCounts[$category] ?></td> <!-- Display book count -->
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        setTimeout(function() {
            var message = document.querySelector('.alert-message');
            if (message) {
                message.style.display = 'none';
                location.reload();
            }
        }, 2500); // 2500 ms = 2.5 seconds
    </script>
</body>

</html>