<?php
$_title = 'Top Book Rankings';
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';

try {
    global $_db;

    // SQL query to calculate rankings
    $sql = "
        SELECT 
            b.book_id, 
            b.book_name, 
            b.book_price, 
            b.book_desc, 
            b.book_photo, 
            b.book_category,
            COUNT(bo.ownership_id) AS rank_count
        FROM book_ownership bo
        JOIN book_item b ON bo.book_id = b.book_id
        GROUP BY b.book_id
        ORDER BY rank_count DESC, b.book_name ASC
    ";

    // Execute the query
    $stmt = $_db->query($sql);
    $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    $rankings = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Book Rankings | BOUNDLESSBOOKS</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .ranking-table th, .ranking-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .ranking-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .ranking-table td img {
            width: 80px;
            height: auto;
            border-radius: 5px;
            margin-right: 10px;
        }

        .ranking-table td .book-details {
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <main>
        <h1>Top Book Rankings</h1>
        <?php if ($rankings): ?>
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Book</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Number of Purchases</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($rankings as $book): 
                        $defaultImage = "../images/default.jpg"; // Path to default image
                        $imageSrc = $book['book_photo'] ? "../images/" . htmlspecialchars(trim($book['book_photo'])) : $defaultImage;
                    ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td>
                                <div class="book-details">
                                    <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($book['book_name']) ?>">
                                    <div>
                                        <strong><?= htmlspecialchars($book['book_name']) ?></strong>
                                        <p style="color: grey;"><?= htmlspecialchars($book['book_category'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($book['book_desc']) ?></td>
                            <td>$<?= number_format($book['book_price'], 2) ?></td>
                            <td><?= $book['rank_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No data available for book rankings!</p>
        <?php endif; ?>
    </main>

    <script src="../js/main.js"></script>
</body>

</html>
