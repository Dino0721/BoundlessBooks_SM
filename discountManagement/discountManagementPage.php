<?php

include '../pageFormat/base.php';
include '../pageFormat/head.php';

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

// Update discount code status
if (isset($_POST['update_status'])) {
    $code_id = $_POST['code_id'];
    $code = $_POST['code'];
    $current_status = $_POST['current_status'];

    if($current_status === 'active'){
        $status = 'inactive';
    }else{
        $status = 'active';
    }

    $stmt = $_db->prepare("UPDATE discount_code SET status = :status WHERE code_id = :code_id");
    $stmt->execute([
        ':status' => $status,
        ':code_id' => $code_id,
    ]);

    echo "<script>alert('" . $code ." has been updated to ". $status ."');</script>";
}

// Delete discount code
if (isset($_POST['delete'])) {
    $code_id = $_POST['code_id'];

    $stmt = $_db->prepare("DELETE FROM discount_code WHERE code_id = :code_id");
    $stmt->execute([
        ':code_id' => $code_id,
    ]);
}

// Retrieve list of discount codes
$stmt = $_db->query("SELECT * FROM discount_code");
$discount_codes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discount Management</title>
    <link rel="stylesheet" href="discountManagementStyles.css">
</head>

<body class="discount-management">

    <h1 class="discount-management__title">Discount Management</h1>

    <div class="form-container">
        <form method="POST" class="discount-management__form">
            <input type="hidden" name="code_id" class="discount-management__input" placeholder="Code ID (for update/disable/delete)">
            <input type="text" name="code" class="discount-management__input" placeholder="Discount Code" required>
            <input type="number" step="0.01" name="discount_percentage" class="discount-management__input" placeholder="Discount Percentage" required>
            <select name="status" class="discount-management__select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit" name="insert" class="discount-management__button">Insert</button>
        </form>

        <!-- Search form -->
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search for a discount code..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php

    $searchQuery = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

    $sql = "SELECT * 
            FROM  discount_code
            WHERE code LIKE :search_query";
    $stm = $_db->prepare($sql);
    $stm->bindParam(':search_query', $searchQuery, PDO::PARAM_STR);
    $stm->execute();
    ?>

    <h2 class="discount-management__subtitle">List of Discount Codes</h2>
    <table border="1" class="discount-management__table">
        <tr class="discount-management__table-row">
            <th class="discount-management__table-header">ID</th>
            <th class="discount-management__table-header">Code</th>
            <th class="discount-management__table-header">Discount Percentage</th>
            <th class="discount-management__table-header">Status</th>
            <th class="discount-management__table-header">Actions</th>
        </tr>
        <?php foreach ($discount_codes as $row): ?>
            <tr class="discount-management__table-row">
                <td class="discount-management__table-cell"><?= htmlspecialchars($row->code_id) ?></td>
                <td class="discount-management__table-cell"><?= htmlspecialchars($row->code) ?></td>
                <td class="discount-management__table-cell"><?= htmlspecialchars($row->discount_percentage) . '%' ?></td>
                <td class="discount-management__table-cell"><?= htmlspecialchars($row->status) ?></td>
                <td class="discount-management__table-cell">
                    <!-- Form to update the status -->
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="code_id" value="<?= $row->code_id ?>">
                        <input type="hidden" name="current_status" value="<?= $row->status ?>">
                        <input type="hidden" name="code" value="<?= $row->code ?>">
                        <button type="submit" name="update_status">Update to <?php
                        
                        if($row->status == "inactive"){
                            echo "Active";
                        }else{
                            echo "Inactive";
                        }
                                                                                ?></button>
                    </form>

                    <!-- Form to delete the discount code -->
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="code_id" value="<?= $row->code_id ?>">
                        <button type="submit" name="delete" style="width:80px" onclick="return confirm('Are you sure you want to delete this discount code?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>

</html>