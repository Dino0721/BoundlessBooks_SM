<?php
$_title = 'Product Catalog';
include '../pageFormat/base.php';
include '../pageFormat/head.php';

// $_title = 'Product Catalog';
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$showAll = isset($_GET['show_all']) ? $_GET['show_all'] : false;

$sql = "SELECT * FROM book_item WHERE book_status = 'AVAILABLE'";
if ($search) {
    $sql .= " AND book_name LIKE :search";
}
if ($showAll == 'yes') {
    $sql = "SELECT * FROM book_item"; // Show all books if "Show All" is clicked
}

try {
    // Use the global PDO object `$_db` from the base script
    global $_db;
    $stmt = $_db->prepare($sql);

    if ($search) {
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $stmt->execute();
    }

    // Fetch all matching records
    $books = $stmt->fetchAll();
    $bookCount = count($books); // Count total number of records

} catch (PDOException $e) {
    // Handle database errors
    echo 'Error: ' . $e->getMessage();
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
        <form method="GET" action="productCatalog.php">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
        <!-- Filter buttons -->
        <button class="toggle-books" onclick="window.location.href='productCatalog.php?show_all=yes'">Show All Books</button>
        <button class="toggle-books" onclick="window.location.href='productCatalog.php?show_all=no'">Show Available Books Only</button>
        <!-- <button class="toggle-books" data-show="all">Show All Books</button>
        <button class="toggle-books" data-show="available">Show Available Books Only</button> -->

        <p>Total Books: <?= $bookCount ?></p>
        <!-- Product Listing -->
        <div class="product-list">
            <?php if ($books): ?>
                <?php foreach ($books as $book): ?>
                    <div class="product-item" data-status="<?= htmlspecialchars($book->book_status) ?>">
                        <h2><?= htmlspecialchars($book->book_name) ?></h2>
                        <p><?= htmlspecialchars($book->book_desc) ?></p>
                        <p>Price: $<?= number_format($book->book_price, 2) ?></p>
                        <a href="detail.php?book_id=<?= $book->book_id ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found!</p>
            <?php endif; ?>
        </div>
    </main>
    <script>
        $(document).ready(function() {
            // Toggle between showing all books or only available books
            $(".toggle-books").click(function() {
                var filter = $(this).data("show");

                if (filter === "all") {
                    // Show all books
                    $(".product-item").show();
                } else if (filter === "available") {
                    // Show only books with AVAILABLE status
                    $(".product-item").each(function() {
                        var status = $(this).data("status");
                        if (status === "AVAILABLE") {
                            $(this).show(); // Show available books
                        } else {
                            $(this).hide(); // Hide disabled books
                        }
                    });
                }
            });
        });
    </script>
    <script src="../js/main.js"></script>
</body>

</html>

<?php
include '../pageFormat/footer.php';
?>