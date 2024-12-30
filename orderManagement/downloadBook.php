<?php

include '../pageFormat/base.php';
global $_db;

if (isset($_GET['book_id'])) {
    $bookId = intval($_GET['book_id']); // Sanitize input
    $sql = "SELECT pdf_path FROM book_item WHERE book_id = :bookId";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':bookId', $bookId, PDO::PARAM_INT); // Bind bookId as an integer
    $stm->execute();
    $row = $stm->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $filePath = realpath(__DIR__ . '/../' . $row["pdf_path"]);

        // Validate file path and check its existence
        if ($filePath && file_exists($filePath) && is_readable($filePath)) {
            // Set headers to initiate the download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));

            ob_clean(); // Clear output buffer
            flush(); // Flush system output
            readfile($filePath);
            exit;
        } else {
            echo "The requested file does not exist or cannot be accessed.";
        }
    } else {
        echo "No file found for the given book ID.";
    }
} else {
    echo "Invalid request. No book ID provided.";
}

// Redirect if download fails
header("Location: orderHistory.php");
exit;
