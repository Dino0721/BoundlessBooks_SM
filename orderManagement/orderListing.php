<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

// Ensure admin privileges
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 0) {
    die("Access denied. Admins only.");
}

global $_db;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Listings</title>
    <link rel="stylesheet" href="orderListingStyles.css">
</head>

<body>

<div class="table-container">
    <h1>Order Listings</h1>
    <?php

    // Query to fetch book ownership details
    $sql = "SELECT bo.ownership_id, u.email, b.book_name, b.book_price, bo.purchase_date, bo.purchase_time 
            FROM book_ownership bo 
            JOIN book_item b ON b.book_id = bo.book_id 
            JOIN user u ON u.user_id = bo.user_id";
    $stm = $_db->prepare($sql);
    $stm->execute();

    // Check if there are results
    if ($stm->rowCount() > 0) {
        echo '<table class="ownership-listing-table">';
        echo '<thead>
                <tr>
                    <th>Order Id</th>
                    <th>User Email</th>
                    <th>Book Name</th>
                    <th>Book Price</th>
                    <th>Purchase Date</th>
                    <th>Purchase Time</th>
                </tr>
              </thead>';
        echo '<tbody>';
        // Loop through and display each ownership record
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>
                    <td>' . htmlspecialchars($row["ownership_id"]) . '</td>
                    <td>' . htmlspecialchars($row["email"]) . '</td>
                    <td>' . htmlspecialchars($row["book_name"]) . '</td>
                    <td>RM' . number_format($row["book_price"], 2) . '</td>
                    <td>' . htmlspecialchars($row["purchase_date"]) . '</td>
                    <td>' . htmlspecialchars($row["purchase_time"]) . '</td>
                  </tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No book ownership records found.</p>";
    }

    ?>
</div>

</body>
</html>
