<?php
include '../pageFormat/base.php';
global $_db;

include_once 'generateBillPdf.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST['generate_bill'])) {
    // Call the generateBillingPdf function to generate the PDF
    if (isset($_SESSION['purchase_time']) && isset($_SESSION['purchase_date'])) {
        generateBillingPdf($_db, $_SESSION['purchase_time'], $_SESSION['purchase_date']);
        exit();
    }
}

try {
    // Begin transaction
    $_db->beginTransaction();

    $selectBooksSql = "SELECT book_id FROM cart_item WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0)";
    $selectBooksStmt = $_db->prepare($selectBooksSql);
    $selectBooksStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $selectBooksStmt->execute();
    $unpaidBookRows = $selectBooksStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($unpaidBookRows > 0) {
        $_SESSION['purchase_time'] = date('H:i:s');
        $_SESSION['purchase_date'] = date('Y-m-d');
    }

    $purchaseTime = $_SESSION['purchase_time'];
    $purchaseDate = $_SESSION['purchase_date'];

    // Update the current active cart's paid column to 1
    $updateCartSql = "UPDATE cart SET paid = 1 WHERE user_id = :user_id AND paid = 0";
    $updateCartStmt = $_db->prepare($updateCartSql);
    $updateCartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $updateCartStmt->execute();

    // Insert the book IDs into the book_ownership table
    $insertOwnershipSql = "INSERT INTO book_ownership (user_id, book_id, purchase_date, purchase_time) VALUES (:user_id, :book_id, :purchase_date, :purchase_time)";
    $insertOwnershipStmt = $_db->prepare($insertOwnershipSql);

    // Loop through the results and insert into book_ownership table
    foreach ($unpaidBookRows as $row) {
        $insertOwnershipStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertOwnershipStmt->bindParam(':book_id', $row['book_id'], PDO::PARAM_INT);
        $insertOwnershipStmt->bindParam(':purchase_date', $purchaseDate, PDO::PARAM_STR);
        $insertOwnershipStmt->bindParam(':purchase_time', $purchaseTime, PDO::PARAM_STR);
        $insertOwnershipStmt->execute();
    }

    // Insert a new cart for the user with paid = 0
    $insertCartSql = "INSERT INTO cart (user_id, paid) VALUES (:user_id, 0)";
    $insertCartStmt = $_db->prepare($insertCartSql);
    $insertCartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $insertCartStmt->execute();

    // Commit transaction
    $_db->commit();
} catch (Exception $e) {
    // Rollback transaction in case of error
    $_db->rollBack();
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .green-tick {
            width: 100px;
            height: 100px;
            animation: rotateTick 0.5s ease-in-out;
        }

        @keyframes rotateTick {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .message {
            margin-top: 20px;
            font-size: 1.5rem;
            color: #28a745;
            text-align: center;
        }

        body a {
            padding: 8px;
            background-color: gray;
            margin: 5px;
            text-decoration: none;
            color: white;
        }
    </style>
</head>

<body>
    <img src="paymentAssets/greenTick.png" alt="Green Tick" class="green-tick">
    <div class="message">Payment Done</div>

    <!-- Form to trigger PDF generation -->
    <form method="POST">
        <button type="submit" name="generate_bill">Download Bill as PDF</button>
    </form>

    <br>
    <a href="../orderManagement/orderHistory.php">Proceed to download book pdf</a>
</body>

</html>