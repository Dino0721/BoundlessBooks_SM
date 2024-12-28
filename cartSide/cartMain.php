<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | BOUNDLESSBOOKS</title>

    <link rel="stylesheet" type="" href="../cartSide/CartStyles.css">
</head>

<?php
$_title = 'Shopping Cart';
include '../pageFormat/head.php';
include '../cartSide/deleteCartBook.php';

global $_db;

// Fetch the books the user already owns
$ownedBooksQuery = "SELECT book_id FROM book_ownership WHERE user_id = :user_id";
$ownedBooksStmt = $_db->prepare($ownedBooksQuery);
$ownedBooksStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$ownedBooksStmt->execute();
$ownedBooks = $ownedBooksStmt->fetchAll(PDO::FETCH_COLUMN, 0);  // Get book_ids of owned books

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
            b.book_photo, 
            b.book_name, 
            b.book_desc, 
            b.book_price, 
            b.book_category, 
            b.book_status 
        FROM cart_item ci
        INNER JOIN book_item b ON ci.book_id = b.book_id
        WHERE ci.cart_id = :cart_id";
    $cartItemsStmt = $_db->prepare($cartItemsQuery);
    $cartItemsStmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
    $cartItemsStmt->execute();
    $cartItems = $cartItemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        echo "<p>Your shopping cart is empty.</p>";
        echo "<p>Wanna add some book in cart?</p>";
        createNavItem("../productCatalog/productCatalog.php", "Let's Shopping Now");

    } else {
        //echo "<h2>Cart Details for User ID: $user_id</h2>";
        echo "<div id='sort-btn'>
                <h5>Sort By :
                <button class='sort-btn' data-sort='book_name' data-order='asc'>Name</button>
                <button class='sort-btn' data-sort='book_price' data-order='asc'>Price (Low to High)</button>
                <button class='sort-btn' data-sort='book_category' data-order='asc'>Category</button>
                <label for='category-sort'>Category By : </label>
                <select id='category-sort'>
                    <option value=''>-- Select Category --</option>
                    <option value='fiction'>Fiction</option>
                    <option value='non-fiction'>Non-Fiction</option>
                    <option value='science'>Science</option>
                    <option value='fantasy'>Fantasy</option>
                    <option value='romance'>Romance</option>
                    <option value='history'>History</option>
                    <option value='mystery'>Mystery</option>
                    <option value='thriller'>Thriller</option>
                    <option value='biography'>Biography</option>
                    <option value='self-help'>Self-Help</option>
                    <option value='health-wellness'>Health & Wellness</option>
                    <option value='art-photography'>Art & Photography</option>
                    <option value='business-economics'>Business & Economics</option>
                    <option value='cookbooks'>Cookbooks</option>
                    <option value='travel'>Travel</option>
                    <option value='young-adult'>Young Adult (YA)</option>
                    <option value='poetry'>Poetry</option>
                    <option value='philosophy'>Philosophy</option>
                    <option value='religion-spirituality'>Religion & Spirituality</option>
                </select>
                </h5>
            </div>";

        echo "<table border='1'>
                <tr>
                    <th>Select</th>
                    <th>Book Cover</th>
                    <th>Book Title</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>";

        foreach ($cartItems as $item) {
            $defaultImage = "../images/default.jpg"; // Path to the default image
            $imageSrc = $item['book_photo'] ? "../images/" . htmlspecialchars(trim($item['book_photo'])) : $defaultImage;
        
            // Truncate description to 40 characters and add hover tooltip
            $fullDesc = htmlspecialchars($item['book_desc']);
            $shortDesc = strlen($fullDesc) > 40 ? substr($fullDesc, 0, 37) . '...' : $fullDesc;
        
            // Check if the user already owns this book
            $isOwned = in_array($item['book_id'], $ownedBooks);
            
            // Disable the checkbox and add the 'disabled' class for owned books
            $disabled = $isOwned ? 'disabled' : '';
            $rowClass = $isOwned ? 'disabled-row' : ''; // Add class for styling the disabled row
            
                echo "<tr class='$rowClass'>
                <td>
                    <input type='checkbox' 
                            class='select-book' 
                            data-price='{$item['book_price']}' 
                            $disabled>
                </td>
                <td>
                    <img src='$imageSrc' alt='" . htmlspecialchars($item['book_name']) . "' style='width: 100px; height: auto;'>
                </td>
                <td data-book_name='" . htmlspecialchars($item['book_name']) . "'>" . htmlspecialchars($item['book_name']) . "</td>
                <td>
                    <span class='tooltip' title='$fullDesc'>$shortDesc</span>
                </td>
                <td data-book_category='" . htmlspecialchars($item['book_category']) . "'>" . htmlspecialchars($item['book_category']) . "</td>
                <td data-book_price='" . htmlspecialchars($item['book_price']) . "'>" . htmlspecialchars(number_format($item['book_price'], 2)) . "</td>
                <td class='" . strtolower(htmlspecialchars($item['book_status'])) . "'>
                    " . ($item['book_status'] === 'AVAILABLE' ? 'Available' : 'Unavailable') . "
                </td>
                <td>
                    <form method='POST' action='../cartSide/deleteCartBook.php'>
                        <input type='hidden' name='delete_book_id' value='" . htmlspecialchars($item['book_id']) . "'>
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
                <button id='checkout-button' onclick=\"window.location.href='../payment/paymentpage.php';\">Checkout</button>
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

    document.querySelectorAll('.sort-btn').forEach(button => {
        button.addEventListener('click', function () {
            const sortKey = this.dataset.sort;
            let sortOrder = this.dataset.order;

            const rows = Array.from(document.querySelectorAll('table tr:nth-child(n+2)')); // All table rows excluding header
            
            rows.sort((a, b) => {
                let aValue = a.querySelector(`[data-${sortKey}]`) ? a.querySelector(`[data-${sortKey}]`).textContent.trim().toLowerCase() : '';
                let bValue = b.querySelector(`[data-${sortKey}]`) ? b.querySelector(`[data-${sortKey}]`).textContent.trim().toLowerCase() : '';

                if (sortOrder === 'asc') {
                    return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
                } else {
                    return aValue < bValue ? 1 : (aValue > bValue ? -1 : 0);
                }
            });

            // Reorder rows in the table
            const table = document.querySelector('table');
            rows.forEach(row => {
                table.appendChild(row); // Move each row to the new position
            });

            // Toggle the sort order for price
            if (sortKey === 'book_price') {
                if (sortOrder === 'asc') {
                    // Change to descending (High to Low)
                    this.dataset.order = 'desc';
                    this.textContent = 'Price (Low to High)';
                } else {
                    // Change to ascending (Low to High)
                    this.dataset.order = 'asc';
                    this.textContent = 'Price (High to Low)';
                }
            }

            // Toggle the sort order for price
            if (sortKey === 'book_name') {
                if (sortOrder === 'asc') {
                    // Change to descending (High to Low)
                    this.dataset.order = 'desc';
                    this.textContent = 'Name (A to Z)';
                } else {
                    // Change to ascending (Low to High)
                    this.dataset.order = 'asc';
                    this.textContent = 'Name (Z to A)';
                }
            }
        });
    });

    //category selection, and sort it
    const categorySort = document.getElementById('category-sort');
        categorySort.addEventListener('change', function () {
            const selectedCategory = categorySort.value;
            const rows = Array.from(document.querySelectorAll('table tr:nth-child(n+2)')); 

            if (selectedCategory) {
                
                rows.sort((a, b) => {
                    let aCategory = a.querySelector('[data-book_category]') ? a.querySelector('[data-book_category]').textContent.trim().toLowerCase() : '';
                    let bCategory = b.querySelector('[data-book_category]') ? b.querySelector('[data-book_category]').textContent.trim().toLowerCase() : '';
                    
                    if (aCategory === selectedCategory && bCategory !== selectedCategory) {
                        return -1;  // a comes first
                    } else if (bCategory === selectedCategory && aCategory !== selectedCategory) {
                        return 1;   // b comes first
                    }
                    return 0;
                });

                // Reorder rows in the table
                const table = document.querySelector('table');
                rows.forEach(row => {
                    table.appendChild(row); // Move each row to the new position
                });
            }
        });
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

</style>