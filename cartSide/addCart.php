<?php
$_title = 'Shopping Cart';
include '../pageFormat/base.php';
include '../pageFormat/head.php';

global $_db;

// Assume user_id = 1 for testing
$user_id = 1;

try {
    // Fetch the active cart ID for the user
    $cartQuery = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
    $cartStmt = $_db->prepare($cartQuery);
    $cartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $cartStmt->execute();
    $cartId = $cartStmt->fetchColumn();

    if (!$cartId) {
        throw new Exception("No active cart found for the user.");
    }

    // Fetch details of books in the cart
    $cartItemsQuery = "
        SELECT 
            b.book_id, 
            b.book_name, 
            b.book_desc, 
            b.book_price, 
            b.book_status 
        FROM cart_item ci
        INNER JOIN book_item b ON ci.book_id = b.book_id
        WHERE ci.cart_id = :cart_id";
    $cartItemsStmt = $_db->prepare($cartItemsQuery);
    $cartItemsStmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $cartItemsStmt->execute();
    $cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        echo "<p>Your cart is empty.</p>";
    } else {
        echo "<h2>Cart Details for User ID: $user_id</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Book ID</th>
                    <th>Book Name</th>
                    <th>Book Description</th>
                    <th>Book Price</th>
                    <th>Book Status</th>
                </tr>";
        foreach ($cartItems as $item) {
            echo "<tr>
                    <td>{$item['book_id']}</td>
                    <td>{$item['book_name']}</td>
                    <td>{$item['book_desc']}</td>
                    <td>{$item['book_price']}</td>
                    <td>{$item['book_status']}</td>
                  </tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
