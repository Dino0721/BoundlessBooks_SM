<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

// forgott
global $_db;
// Fetch the product details based on the `book_id` from the URL
$bookId = $_GET['book_id'] ?? 0;

try {
    // Prepare the SQL statement
    $stmt = $_db->prepare("SELECT * FROM book_item WHERE book_id = :book_id");
    $stmt->execute([':book_id' => $bookId]);

    // Fetch the result as an associative array
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the book exists
    if (!$book) {
        die('Product not found!');
    }
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
// Debugging the fetched data
// echo "<pre>";
// print_r($book); // Output the fetched data to check the result
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['book_name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>

    </style>
</head>

<body>
    <div class="product-detail">
        <h1><?= htmlspecialchars($book['book_name']) ?></h1>
        <p><?= htmlspecialchars($book['book_desc']) ?></p>
        <p class="price">Price: $<?= number_format($book['book_price'], 2) ?></p>

        <?php if ($book['book_status'] === 'DISABLED'): ?>
            <p class="unavailable">This book is currently unavailable.</p>
        <?php endif; ?>

        <button id="addToCartButton">Add to Cart</button>

        <script>
            document.getElementById('addToCartButton').addEventListener('click', function() {
                var bookId = <?php echo $bookId; ?>; // Pass the book_id dynamically from PHP

                var xhr = new XMLHttpRequest();
                xhr.open('GET', '../cartSide/addToCart.php?book_id=' + bookId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Action completed, handle response here (e.g., update UI)
                        alert('Book added to cart!');
                    }
                };
                xhr.send();
            });
        </script>

        <a href="<?= $_SERVER['HTTP_REFERER'] ?? 'index.php' ?>">Back to Listing</a>
    </div>
</body>

</html>