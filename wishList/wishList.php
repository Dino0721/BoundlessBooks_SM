<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wish List | BOUNDLESSBOOKS</title>
    <link rel="stylesheet" href="../wishList/wish.css"> 
</head>
</html>

<?php
$_title = 'Wish List';
include '../pageFormat/head.php';
//include '../cartSide/deleteWishlistBook.php';  

global $_db;

$user_id = $_SESSION['user_id'];

if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['book_name'])) {
    $book_name = htmlspecialchars($_GET['book_name']);
    echo '
    <div id="toast" class="toast-success">
        <p>The Book "' . $book_name . '" has been successfully removed from your wishlist.</p>
    </div>

    <script>
        // Show the toast message
        var toast = document.getElementById("toast");
        toast.className = "toast-success show-toast";
        
        // Hide the toast message after 3 seconds
        setTimeout(function() {
            toast.className = toast.className.replace("show-toast", "");
        }, 3000);
    </script>';
}

// Check if the success flag for adding to cart is set
if (isset($_GET['success']) && isset($_GET['book_id'])) {
    $success = $_GET['success'];
    $book_id = $_GET['book_id'];

    // Fetch the book name from the database
    $bookQuery = "SELECT book_name FROM book_item WHERE book_id = :book_id";
    $bookStmt = $_db->prepare($bookQuery);
    $bookStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookStmt->execute();
    $bookName = $bookStmt->fetchColumn();

    $message = '';
    $toastClass = '';

    switch ($success) {
        case 2:
            $message = '"' . htmlspecialchars($bookName) . '" has been successfully added to your cart.';
            $toastClass = 'toast-success';
            break;
        case 3:
            $message = 'You already own "' . htmlspecialchars($bookName) . '".';
            $toastClass = 'toast-warning';
            break;
        case 4:
            $message = '"' . htmlspecialchars($bookName) . '" is already in your cart.';
            $toastClass = 'toast-info';
            break;
        default:
            break;
    }

    echo '
    <div id="toast" class="' . $toastClass . '">
        <p>' . $message . '</p>
    </div>

    <script>
        // Show the toast message
        var toast = document.getElementById("toast");
        toast.className = toast.className + " show-toast";

        // Hide the toast message after 3 seconds
        setTimeout(function() {
            toast.className = toast.className.replace("show-toast", "");
        }, 3000);
    </script>';
}

// Fetch the book IDs in the user's wishlist
try {
    // Query to get the book_id values for the current user
    $wishlistQuery = "
        SELECT book_id
        FROM wishlist
        WHERE user_id = :user_id";
    $wishlistStmt = $_db->prepare($wishlistQuery);
    $wishlistStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $wishlistStmt->execute();
    $bookIds = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN); // Get an array of book IDs for this user

    if (empty($bookIds)) {
        echo "<p>Your wishlist is empty.</p>";
        echo "<p>Want to add some books to your wishlist?</p>";
        createNavItem("../productCatalog/productCatalog.php", "Start Browsing Now");
    } else {
        // Fetch the details of books in the wishlist
        $bookIdsPlaceholder = implode(',', array_fill(0, count($bookIds), '?')); // Create a placeholder string for the book IDs

        $wishlistItemsQuery = "
            SELECT 
                b.book_id,
                b.book_photo, 
                b.book_name, 
                b.book_desc, 
                b.book_price, 
                b.book_category, 
                b.book_status 
            FROM book_item b
            WHERE b.book_id IN ($bookIdsPlaceholder)";
        
        $wishlistItemsStmt = $_db->prepare($wishlistItemsQuery);
        $wishlistItemsStmt->execute($bookIds); // Bind the book IDs dynamically
        $wishlistItems = $wishlistItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Your Wishlist</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Book Cover</th>
                    <th>Book Title</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>";

        foreach ($wishlistItems as $item) {
            $defaultImage = "../images/default.jpg"; // Default image if none available
            $imageSrc = $item['book_photo'] ? "../images/" . htmlspecialchars(trim($item['book_photo'])) : $defaultImage;

            // Truncate description to 40 characters and add hover tooltip
            $fullDesc = htmlspecialchars($item['book_desc']);
            $shortDesc = strlen($fullDesc) > 40 ? substr($fullDesc, 0, 37) . '...' : $fullDesc;

            echo "<tr>
                        <td>
                            <img src='$imageSrc' alt='" . htmlspecialchars($item['book_name']) . "' style='width: 100px; height: auto;'>
                        </td>
                        <td>" . htmlspecialchars($item['book_name']) . "</td>
                        <td>
                            <span class='tooltip' title='$fullDesc'>$shortDesc</span>
                        </td>
                        <td>" . htmlspecialchars($item['book_category']) . "</td>
                        <td>" . htmlspecialchars(number_format($item['book_price'], 2)) . "</td>
                        <td class='" . strtolower(htmlspecialchars($item['book_status'])) . "'>
                            " . ($item['book_status'] === 'AVAILABLE' ? 'Available' : 'Unavailable') . "
                        </td>
                        <td>
                            <!-- Remove Book Button -->
                            <form method='POST' action='../wishList/deleteWishlist.php'>
                                <input type='hidden' name='delete_book_id' value='" . htmlspecialchars($item['book_id']) . "'>
                                <button type='submit'>Remove</button>
                            </form>

                            <!-- Add to Cart Button -->
                            <form method='POST' action='../wishList/addWishToCart.php'>
                                <input type='hidden' name='add_book_id' value='" . htmlspecialchars($item['book_id']) . "'>
                                <button type='submit'>Add to Cart</button>
                            </form>
                        </td>
                    </tr>";
        }

        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
