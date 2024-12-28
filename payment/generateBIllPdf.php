<?php
require_once '../fpdf/fpdf.php';

function generateBillingPDF($db, $purchaseTime, $purchaseDate)
{
    global $_SESSION;
    global $_db;
    $user_id = $_SESSION['user_id'];

    // Fetch user email
    $emailQuery = "SELECT email FROM user WHERE user_id = :user_id";
    $emailStmt = $db->prepare($emailQuery);
    $emailStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $emailStmt->execute();
    $userEmail = $emailStmt->fetchColumn();

    // Get the list of books the user has purchased (book_id, name, price)
    $selectBooksSql = "SELECT b.book_name, b.book_price, bo.purchase_date, bo.purchase_time
FROM book_ownership bo
JOIN book_item b ON bo.book_id = b.book_id
WHERE bo.user_id = :user_id AND bo.purchase_date = :purchaseDate AND bo.purchase_time = :purchaseTime";
    $selectBooksStmt = $_db->prepare($selectBooksSql);
    $selectBooksStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $selectBooksStmt->bindParam(':purchaseDate', $purchaseDate, PDO::PARAM_STR);
    $selectBooksStmt->bindParam(':purchaseTime', $purchaseTime, PDO::PARAM_STR);
    $selectBooksStmt->execute();
    $books = $selectBooksStmt->fetchAll(PDO::FETCH_ASSOC);


    // Calculate total price without discount
    $totalPrice = 0;
    foreach ($books as $book) {
        $totalPrice += $book['book_price'];
    }

    // Get the discount rate (default to 1.0 if no discount code)
    $discountRate = isset($_SESSION['discount_percentage']) ? ($_SESSION['discount_percentage'] / 100) : 1.0;
    $discountAmount = ($totalPrice * (1 - $discountRate)); // Apply discount to the total price

    // Create the PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Billing Invoice', 0, 1, 'C');
    $pdf->Ln(10);

    // Display discount code (if available)
    if (isset($_SESSION['discount_code_used'])) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(100, 10, 'Discount Code: ' . $_SESSION['discount_code_used'], 0, 1);
        $pdf->Cell(100, 10, 'Discount Percentage: ' . $_SESSION['discount_percentage'] . '%', 0, 1);
        $pdf->Ln(10);
    }

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, 'User Email: ' . $userEmail, 0, 1);
    $pdf->Cell(100, 10, 'Invoice Date: ' . date('Y-m-d'), 0, 1);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Book Title', 1);
    $pdf->Cell(40, 10, 'Purchase Date', 1);
    $pdf->Cell(40, 10, 'Purchase Time', 1);
    $pdf->Cell(30, 10, 'Price', 1);
    $pdf->Ln();

    // Table Rows
    $pdf->SetFont('Arial', '', 12);
    foreach ($books as $book) {
        $pdf->Cell(60, 10, $book['book_name'], 1);
        $pdf->Cell(40, 10, $book['purchase_date'], 1);
        $pdf->Cell(40, 10, $book['purchase_time'], 1);
        $pdf->Cell(30, 10, '$' . number_format($book['book_price'], 2), 1, 0, 'C');
        $pdf->Ln();
    }

    // Total Price and Discounted Price
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, 'Total Price', 0, 0, 'R');
    $pdf->Cell(30, 10, '$' . number_format($totalPrice, 2), 1, 1, 'C');

    if ($discountRate < 1.0) {
        // Show discounted total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, 'Discounted Total', 0, 0, 'R');
        $pdf->Cell(30, 10, '$' . number_format($discountAmount, 2), 1, 1, 'C');
    }

    $pdf->Output('D', 'BillingInvoice_' . $user_id . '.pdf'); // 'D' forces the download, 'I' would show inline

}
