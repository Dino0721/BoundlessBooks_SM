<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

// forgott
global $_db;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="CartStyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

    <?php include_once 'fetchCartItems.php'; ?>

    <!-- Button to proceed to payment -->
    <a href="../payment/PaymentPage.php" style="
    position: fixed;
    top: 10px;
    right: 10px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    text-align: center;">
        Proceed to Payment
    </a>

</body>

<script type="text/javascript" src="fetchTotalToPayment.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script type="text/javascript" src="main.js"></script>

<script type="text/javascript">
    // Listen for delete button click
    $(document).ready(function() {
        $('.item-delete-button i').on('click', function() {
            var bookId = $(this).data('book-id');  // Get the book_id from the clicked delete icon

            // Show confirmation alert
            if (confirm('Are you sure you want to delete this item from the cart?')) {
                // Send AJAX request to delete the item
                $.ajax({
                    url: 'deleteCartItem.php',  // The PHP script that will handle the deletion
                    type: 'POST',
                    data: { book_id: bookId },
                    success: function(response) {
                        // Check if the item was deleted successfully
                        if (response == 'success') {
                            // Remove the item from the cart display
                            $('[data-book-id="' + bookId + '"]').closest('.cartitem-container').remove();
                            alert('Item removed from the cart.');
                        } else {
                            alert('Failed to delete the item.');
                        }
                    },
                    error: function() {
                        alert('An error occurred while trying to delete the item.');
                    }
                });
            }
        });
    });
</script>

</html>
