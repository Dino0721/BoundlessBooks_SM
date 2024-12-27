<?php
$_title = 'Product Catalog';
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';
include 'searchForm.php';

// $_title = 'Product Catalog';
// Get search and filter inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$showAll = isset($_GET['show_all']) ? $_GET['show_all'] : false;

// Default SQL query
$sql = "SELECT * FROM book_item WHERE book_status = 'AVAILABLE'";

// Modify query for search and category filters
if ($search) {
    $sql .= " AND book_name LIKE :search";
}
if ($category && $category !== 'all') {
    $sql .= " AND book_category = :category";
}
if ($showAll == 'yes') {
    $sql = "SELECT * FROM book_item"; // Show all books if "Show All" is clicked
}

// Fetch ENUM values for book_category
try {
    global $_db;

    // Fetch ENUM values from book_category column
    $categoryStmt = $_db->query("SHOW COLUMNS FROM book_item LIKE 'book_category'");
    $categoryRow = $categoryStmt->fetch(PDO::FETCH_ASSOC);
    preg_match_all("/'([^']+)'/", $categoryRow['Type'], $matches);
    $categories = $matches[1]; // Array of category values

    // Prepare the SQL query
    $stmt = $_db->prepare($sql);
    $params = [];
    if ($search) {
        $params[':search'] = '%' . $search . '%';
    }
    if ($category && $category !== 'all') {
        $params[':category'] = $category;
    }
    $stmt->execute($params);

    // Fetch matching records
    $books = $stmt->fetchAll();
    $bookCount = count($books); // Count total books
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    $categories = [];
    $books = [];
    $bookCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog | BOUNDLESSBOOKS</title>

    <link rel="stylesheet" href="../css/main.css">
</head>

<body>
    <main>
        <!-- Search Form -->
        <form method="GET" action="productCatalog.php" class="search-form">
            <input
                type="text"
                name="search"
                placeholder="Search products..."
                value="<?= htmlspecialchars($search) ?>"
                class="search-input">
            <select name="category" class="category-dropdown">
                <option value="all" <?= !$category || $category === 'all' ? 'selected' : '' ?>>All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="search-button">Search</button>
        </form>
        <!-- Filter buttons -->
        <div class="filter-buttons">
            <button
                class="toggle-books"
                id="show-all-books"
                onclick="window.location.href='productCatalog.php?show_all=yes'">
                Show All Books
            </button>
            <button
                class="toggle-books"
                id="show-available-books"
                onclick="window.location.href='productCatalog.php?show_all=no'">
                Show Available Books Only
            </button>
        </div>


        <p class="book-count">Total Books: <span><?= $bookCount ?></span></p>
        <!-- Product Listing -->
        <div class="product-list">
            <?php if ($books): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-item"
                        data-url="detail.php?book_id=<?= $book->book_id ?>"
                        data-status="<?= htmlspecialchars($book->book_status) ?>">

                        <!-- Display Book Image -->
                        <?php
                        $defaultImage = "../images/default.jpg"; // Path to the default image
                        $imageSrc = $book->book_photo ? "../images/" . htmlspecialchars(trim($book->book_photo)) : $defaultImage;
                        ?>
                        <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($book->book_name) ?>" class="book-detail-image">
                        <h2>
                            <?= htmlspecialchars($book->book_name) ?> |
                            <span class="category" style="color: grey;"><?= htmlspecialchars($book->book_category) ?></span>
                        </h2>
                        <p><?= htmlspecialchars($book->book_desc) ?></p>
                        <p class="price">Price: $<?= number_format($book->book_price, 2) ?></p>
                        <p class="status <?= strtolower(htmlspecialchars($book->book_status)) ?>">
                            <?= htmlspecialchars($book->book_status) === 'AVAILABLE' ? 'Available' : 'Unavailable' ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found!</p>
            <?php endif; ?>
        </div>


    </main>
    <script>
        $(document).ready(function() {
            // Get current "show_all" state
            let showAll = '<?= $showAll ?>';

            // If 'Show All Books' is active
            if (showAll === 'yes') {
                $('#show-all-books').addClass('active');
                $('#show-available-books').removeClass('active');
            } else {
                $('#show-available-books').addClass('active');
                $('#show-all-books').removeClass('active');
            }

            // Toggle between showing all books or only available books
            $(".toggle-books").click(function() {
                $(".toggle-books").removeClass("active"); // Remove 'active' class from all buttons
                $(this).addClass("active"); // Add 'active' class to clicked button
            });
        });
        $(document).ready(function() {
            // Make entire product-item div clickable
            $(".product-item").on("click", function() {
                // Get the URL from the data-url attribute
                const url = $(this).data("url");
                // Navigate to the URL
                window.location.href = url;
            });

            // Optional: Change the cursor to a pointer for better UX
            $(".product-item").css("cursor", "pointer");
        });
    </script>
    <script src="../js/main.js"></script>
</body>

</html>

<?php
// include '../pageFormat/footer.php';
?>