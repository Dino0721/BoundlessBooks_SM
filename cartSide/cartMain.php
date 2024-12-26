<?php
$_title = 'Shopping Cart';
include '../pageFormat/head.php';
include '../cartSide/deleteCartBook.php';

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
                    <th>Select</th>
                    <th>Book ID</th>
                    <th>Book Name</th>
                    <th>Book Description</th>
                    <th>Book Price</th>
                    <th>Book Status</th>
                    <th>Action</th>
                </tr>";
        foreach ($cartItems as $item) {
            $disabled = $item['book_status'] === 'DISABLED' ? 'disabled' : '';
            echo "<tr>
                    <td>
                        <input type='checkbox' 
                               class='select-book' 
                               data-price='{$item['book_price']}' 
                               $disabled>
                    </td>
                    <td>{$item['book_id']}</td>
                    <td>{$item['book_name']}</td>
                    <td>{$item['book_desc']}</td>
                    <td>{$item['book_price']}</td>
                    <td>{$item['book_status']}</td>
                    <td>
                        <form method='POST' action='deleteCartBook.php'>
                            <input type='hidden' name='delete_book_id' value='{$item['book_id']}'>
                            <button type='submit'>Delete</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
        echo "<div id='summary-container'>
                <p id='total-summary'>
                    Selected: <span id='selected-count'>0</span> out of <span id='total-count'>" . count($cartItems). "</span>
                    books | Total Price: $<span id='total-price'>0.00</span>
                </p>
                <button id='checkout-button'>Checkout</button>
            </div>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<script>
    // JavaScript for updating selected count and total price
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.select-book');
        const selectedCountElem = document.getElementById('selected-count');
        const totalPriceElem = document.getElementById('total-price');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateTotals);
        });

        function updateTotals() {
            let selectedCount = 0;
            let totalPrice = 0;

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedCount++;
                    totalPrice += parseFloat(checkbox.dataset.price);
                }
            });

            selectedCountElem.textContent = selectedCount;
            totalPriceElem.textContent = totalPrice.toFixed(2);
        }
    });
</script>

<style>
    #summary-container {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: #f1f1f1;
        padding: 10px 20px;
        text-align: center;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 1000; /* Ensure it's above all content */
    }

    #total-summary {
        margin: 0;
        font-size: 16px;
    }

    #checkout-button {
        padding: 8px 15px;
        font-size: 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #checkout-button:hover {
        background-color: #0056b3;
    }

    body {
        margin-bottom: 80px; /* Ensure space for fixed summary */
    }
</style>