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
        <img
            src="../images/<?= htmlspecialchars(!empty($book['book_photo']) ? $book['book_photo'] : 'default.jpg') ?>"
            alt="<?= htmlspecialchars($book['book_name']) ?>"
            class="book-detail-image">
        <h1>
            <?= htmlspecialchars($book['book_name']) ?> |
            <span class="category" style="color: grey;"><?= htmlspecialchars($book['book_category']) ?></span>
        </h1>
        <p><?= htmlspecialchars($book['book_desc']) ?></p>
        <p class="price">Price: $<?= number_format($book['book_price'], 2) ?></p>

        <?php if ($book['book_status'] === 'DISABLED'): ?>
            <p class="unavailable">This book is currently unavailable.</p>
        <?php endif; ?>

        <button id="addToCartButton" class="<?= $book['book_status'] === 'DISABLED' ? 'disabled' : '' ?>">
            <?= $book['book_status'] === 'DISABLED' ? 'Unavailable' : 'Add to Cart' ?>
        </button>

        <button id="backToListingButton">Back to Listing</button>
    </div>
    <script>
        document.getElementById('addToCartButton').addEventListener('click', function() {
            var bookId = <?php echo $bookId; ?>; // Pass the book_id dynamically from PHP

            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../cartSide/addToCart.php?book_id=' + bookId, true);

            // Ensure this is only triggered when the request is complete
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) { // Only proceed when the request is finished
                    if (xhr.status === 200) { // Check if the request was successful
                        var response = xhr.responseText.trim(); // Trim any extra spaces or unwanted characters
                        if (response === "success") {
                            alert("Book has been added to your cart!");
                        } else {
                            alert(response); // Display error or other messages
                        }
                    } else {
                        alert("Error: Unable to add book to cart.");
                    }
                }
            };

            xhr.send();
        });

        $(document).ready(function() {
            $('#backToListingButton').click(function() {
                window.location.href = '<?= $_SERVER['HTTP_REFERER'] ?? 'index.php' ?>';
            });
        });
    </script>
</body>

</html>