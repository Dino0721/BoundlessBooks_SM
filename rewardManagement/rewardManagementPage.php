<!DOCTYPE html>
<html lang="en">
<html>

<?
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';

session_start();

?>

<?php
// Database connection (Global PDO object)
$_db = new PDO('mysql:dbname=ebookdb', 'root', '', [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]);

// Insert discount code
if (isset($_POST['insert'])) {
    $code = $_POST['code'];
    $discount = $_POST['discount_percentage'];
    $status = $_POST['status'];

    $stmt = $_db->prepare("INSERT INTO discount_code (code, discount_percentage, status) VALUES (:code, :discount, :status)");
    $stmt->execute([
        ':code' => $code,
        ':discount' => $discount,
        ':status' => $status,
    ]);
}


// Retrieve list of discount codes
$stmt = $_db->query("SELECT * FROM discount_code");
$discount_codes = $stmt->fetchAll();
?>

<body>
    <link rel="stylesheet" href="../rewardManagement/rewardManagementStyles.css">

    <h1>Reward Management</h1>
    <form method="POST">
        <input type="hidden" name="code_id" placeholder="Code ID (for update/disable/delete)">
        <input type="text" name="code" placeholder="Discount Code" required>
        <input type="number" step="0.01" name="discount_percentage" placeholder="Discount Percentage" required>
        <select name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <button type="submit" name="insert">Insert</button>
    </form>

    <h2>List of Discount Codes</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Discount Percentage</th>
            <th>Status</th>
        </tr>
        <?php foreach ($discount_codes as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row->code_id) ?></td>
                <td><?= htmlspecialchars($row->code) ?></td>
                <td><?= htmlspecialchars($row->discount_percentage) ?></td>
                <td><?= htmlspecialchars($row->status) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <body>

    </body>

</html>