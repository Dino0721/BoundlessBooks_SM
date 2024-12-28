<?php
include '../pageFormat/base.php';
global $_db;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discount_code = trim($_POST['discount_code']);

    try {
        // Prepare the SQL query to fetch discount percentage
        $stmt = $_db->prepare("SELECT discount_percentage FROM discount_code WHERE code = :code AND status='active'");
        $stmt->bindValue(':code', $discount_code, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['discount_code_used'] = $discount_code;
            $_SESSION['discount_percentage'] = $result['discount_percentage'];
            echo json_encode([
                'valid' => true,
                'discount_percentage' => $result['discount_percentage']
            ]);
        } else {
            echo json_encode(['valid' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
