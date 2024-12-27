<!DOCTYPE html>
<html lang="en">
<html>
    
<?
require_once '../pageFormat/base.php';
include '../pageFormat/head.php';
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

// Update discount code
if (isset($_POST['update'])) {
    $code_id = $_POST['code_id'];
    $code = $_POST['code'];
    $discount = $_POST['discount_percentage'];
    $status = $_POST['status'];

    $stmt = $_db->prepare("UPDATE discount_code SET code = :code, discount_percentage = :discount, status = :status WHERE code_id = :code_id");
    $stmt->execute([
        ':code_id' => $code_id,
        ':code' => $code,
        ':discount' => $discount,
        ':status' => $status,
    ]);
}

// Disable discount code
if (isset($_POST['disable'])) {
    $code_id = $_POST['code_id'];

    $stmt = $_db->prepare("UPDATE discount_code SET status = 'inactive' WHERE code_id = :code_id");
    $stmt->execute([':code_id' => $code_id]);
}

// Delete discount code
if (isset($_POST['delete'])) {
    $code_id = $_POST['code_id'];

    $stmt = $_db->prepare("DELETE FROM discount_code WHERE code_id = :code_id");
    $stmt->execute([':code_id' => $code_id]);
}

// Retrieve list of discount codes
$stmt = $_db->query("SELECT * FROM discount_code");
$discount_codes = $stmt->fetchAll();
?>

<body>
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
        <button type="submit" name="update">Update</button>
        <button type="submit" name="disable">Disable</button>
        <button type="submit" name="delete">Delete</button>
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