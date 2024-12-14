<?php
include '../pageFormat/base.php';

global $_db;

// Get the user ID from the session
session_start();
$user_id = $_SESSION['user_id'];

try {
    // Begin transaction
    $_db->beginTransaction();

    // Update the current active cart's paid column to 1
    $updateCartSql = "UPDATE cart SET paid = 1 WHERE user_id = :user_id AND paid = 0";
    $updateCartStmt = $_db->prepare($updateCartSql);
    $updateCartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $updateCartStmt->execute();

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
    </style>
</head>
<body>
    <img src="paymentAssets/greenTick.png" alt="Green Tick" class="green-tick">
    <div class="message">Payment Done</div>

    <script>
        // Redirect the user after 3 seconds
        setTimeout(() => {
            window.location.href = '../productCatalog/productCatalog.php';
        }, 3000);
    </script>
</body>
</html>
