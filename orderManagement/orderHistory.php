<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

global $_db;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="orderHistoryStyles.css">
</head>

<body>

<div class="table-container">
    <h1>Order History</h1>
    <?php

    $user = $_SESSION['user_id'];

    // Query to join book_ownership with book_item and fetch necessary details
    $sql = "SELECT b.book_name, b.book_price, bo.purchase_date, bo.purchase_time 
            FROM book_ownership bo 
            JOIN book_item b ON b.book_id = bo.book_id 
            WHERE bo.user_id = :user_id";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':user_id', $user, PDO::PARAM_INT);
    $stm->execute();

    // Check if there are results
    if ($stm->rowCount() > 0) {
        echo '<table class="order-history-table">';
        echo '<thead>
                <tr>
                    <th>Book Name</th>
                    <th>Book Price</th>
                    <th>Purchase Date</th>
                    <th>Purchase Time</th>
                </tr>
              </thead>';
        echo '<tbody>';
        // Loop through and display each book item
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>
                    <td>' . htmlspecialchars($row["book_name"]) . '</td>
                    <td>RM' . number_format($row["book_price"], 2) . '</td>
                    <td>' . htmlspecialchars($row["purchase_date"]) . '</td>
                    <td>' . htmlspecialchars($row["purchase_time"]) . '</td>
                  </tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>No purchase history found.</p>";
    }

    ?>
</div>

</body>
</html>
