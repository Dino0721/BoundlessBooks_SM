<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = req('reset_token');
    $newPassword = req('password');
    $confirmPassword = req('confirm_password');

    if ($newPassword !== $confirmPassword) {
        temp('error', 'Passwords do not match.');
    } else {
        if (strlen($newPassword) < 6) {
            temp('error', 'Password must be at least 6 characters long.');
        } else {
            if ($token && $newPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stm = $_db->prepare('UPDATE user SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ? AND token_expiry > NOW()');
                $stm->execute([$hashedPassword, $token]);
    
                if ($stm->rowCount() > 0) {
                    temp('info', 'Password updated successfully. You can now log in.');
                    header('Location: login.php');
                    exit;
                } else {
                    temp('error', 'Invalid or expired token.');
                }
            } else {
                temp('error', 'Please provide a new password.');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>

<body>
    <h1>Change Password</h1>
    <form action="changePassword.php" method="post">
        <input type="hidden" name="reset_token" value="<?= htmlspecialchars($_GET['reset_token'] ?? '') ?>">
        <label for="password">New Password</label><br>
        <input type="password" name="password" id="password" placeholder="Enter new password" required><br><br>
        <label for="confirm_password">Confirm Password</label><br>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required><br><br>
        <button type="submit">Update Password</button>
    </form>
</body>

</html>