<?php
include '../pageFormat/base.php';
include '../pageFormat/head.php';
require_once __DIR__ . '/../app/bootstrap.php';

$userModel = new User();

if (is_post()) {
    // CSRF Check
    if (!SessionManager::verifyCsrfToken(post('csrf_token'))) {
        die('Invalid CSRF Token');
    }

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
                
                if ($userModel->updatePasswordByToken($token, $hashedPassword)) {
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
        <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCsrfToken() ?>">
        <label for="password">New Password</label><br>
        <input type="password" name="password" id="password" placeholder="Enter new password" required><br><br>
        <label for="confirm_password">Confirm Password</label><br>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required><br><br>
        <button type="submit">Update Password</button>
    </form>
</body>

</html>