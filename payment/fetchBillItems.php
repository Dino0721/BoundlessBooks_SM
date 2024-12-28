<?php
// Assuming $_db is the PDO connection from base.php
// SQL query to fetch the cart_id for the current user
$user = $_SESSION['user_id'];

// First query to get the cart_id
$sql = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
$stm = $_db->prepare($sql);
$stm->bindParam(':user_id', $user, PDO::PARAM_INT);
$stm->execute();
$cartId = $stm->fetchColumn(); // Fetch the cart_id

// Check if we found a cart
if ($cartId) {
    // Second query to get all items in the cart with book details using JOIN
    $sql = "
        SELECT ci.*, b.book_name, b.book_price 
        FROM cart_item ci
        JOIN book_item b ON ci.book_id = b.book_id
        WHERE ci.cart_id = :cart_id
    ";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $stm->execute();

    // Check if there are results
    if ($stm->rowCount() > 0) {
        echo '<div class="billing-summary">';
        echo '<h2>Billing Summary</h2>';
        echo '<table class="billing-table">';
        echo '<thead>
                <tr>
                    <th>Book Name</th>
                    <th>Price</th>
                </tr>
              </thead>';
        echo '<tbody>';

        $total = 0; // To calculate the total price

        // Loop through and display each cart item
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $price = $row["book_price"];
            $subtotal = $price;
            $total += $subtotal;

            echo '
                <tr>
                    <td>' . htmlspecialchars($row["book_name"]) . '</td>
                    <td>RM ' . number_format($price, 2) . '</td>
                </tr>
            ';
        }

        echo '</tbody>';
        echo '<tfoot>
                <tr>
                    <div class="total-container">
                    <td colspan="1"><strong>Total</strong></td>
                    <td id="totalPrice"><strong>RM ' . number_format($total, 2) . '</strong></td>
                    </div>
                </tr>
              </tfoot>';
        echo '</table>';
        echo '</div>';
    } else {
        echo "No items found in your cart.";
    }
} else {
    echo "No active cart found.";
}
?>
