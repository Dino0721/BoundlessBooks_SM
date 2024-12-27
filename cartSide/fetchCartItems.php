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
if (!$cartId) {
    // No active cart found, create a new cart for the user
    $sql = "INSERT INTO cart (user_id, paid, total_price) VALUES (:user_id, 0, 0.00)";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':user_id', $user, PDO::PARAM_INT);
    $stm->execute();

    // Get the newly created cart_id
    $cartId = $_db->lastInsertId();
}

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
    // Loop through and display each cart item
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        echo '
            <div class="cartitem-container" 
                data-book-id="' . $row["book_id"] . '" >
                <div class="img-container">
                    <img src="bookPhoto/kris.jpeg" alt="Book Image" />
                </div>

                <div class="item-infos">
                    <div class="item-name">Book Name: ' . $row["book_name"] . '</div>
                </div>

                <div class="item-edit">
                    <div class="item-price">Book Price: RM' . number_format($row["book_price"], 2) . '</div>
                    <div class="item-delete-button">
                        <i class="fas fa-trash" data-book-id="' . $row["book_id"] . '"></i>
                    </div>
                </div>
            </div>
        ';
    }
} else {
    echo "No cart items found.";
}
?>
