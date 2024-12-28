<?php

include '../pageFormat/base.php';
global $_db;


// Get the book_id from the query parameter
if (isset($_GET['book_id'])) {
    $bookId = intval($_GET['book_id']); // Sanitize input
    $sql = "SELECT pdf_path FROM book_item WHERE book_id = :bookId";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':bookId', $bookId, PDO::PARAM_INT); // Bind bookId as an integer
    $stm->execute();
    $row = $stm->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $filePath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . $row["pdf_path"];
        echo $filePath;
        // Validate file path and check its existence
        if (file_exists($filePath) && is_readable($filePath)) {
            // Set headers to initiate the download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));

            echo $filepath;

            // Output the file contents
            readfile($filePath);
            exit;
        } else {
            // File does not exist or is not readable
            echo "The requested file does not exist or cannot be accessed.";
        }
    } else {
        // No matching book_id found in the database
        echo "No file found for the given book ID.";
    }
} else {
    // Invalid request
    echo "Invalid request. No book ID provided.";
}

Header("Location: orderHistory.php");
exit;
