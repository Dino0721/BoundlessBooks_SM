<?php
$_title = 'Product Catalog';
include '../pageFormat/head.php';
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
    <header>Product Catalog</header>
    <main>
        <div class="catalog">
            <?php 
            $products = [
                ["title" => "Product 1", "price" => "$19.99", "description" => "This is a brief description of Product 1. It's amazing!", "image" => "https://via.placeholder.com/250"],
                ["title" => "Product 2", "price" => "$29.99", "description" => "This is a brief description of Product 2. You'll love it!", "image" => "https://via.placeholder.com/250"],
                ["title" => "Product 3", "price" => "$39.99", "description" => "This is a brief description of Product 3. Don't miss out!", "image" => "https://via.placeholder.com/250"],
            ];

            // Loop through products and display them
            foreach ($products as $product) {
                echo "<div class=\"product\">";
                echo "    <img src=\"{$product['image']}\" alt=\"{$product['title']}\">";
                echo "    <div class=\"product-info\">";
                echo "        <h2 class=\"product-title\">{$product['title']}</h2>";
                echo "        <p class=\"product-price\">{$product['price']}</p>";
                echo "        <p class=\"product-description\">{$product['description']}</p>";
                echo "        <a href=\"#\" class=\"button\">Buy Now</a>";
                echo "    </div>";
                echo "</div>";
            }
            ?>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>

<?php
include '../pageFormat/footer.php';
?>