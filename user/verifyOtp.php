<?php

require '../pageFormat/base.php';
require '../pageFormat/head.php';

if (is_post()) {
    $enteredOtp = req('otp');

    if ($_SESSION['otp'] == $enteredOtp && time() <= $_SESSION['otp_expiry']) {
        $password = req('password');
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = $_SESSION['email'];

        $stm = $_db->prepare('INSERT INTO user (email, password, phone_number) VALUES (?, ?, ?)');

        if ($stm->execute([$email, $hashed_password, ''])) {
            $new_user_id = $_db->lastInsertId();

            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiry']);

            temp('info', 'Sign-up successful.');

            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['email'] = $email;
            $_SESSION['user'] = [
                'user_id' => $new_user_id,
                'email' => $email,
            ];

            login(['email' => $email, 'role' => $role], '../productCatalog/productCatalog.php');
            exit();
        } else {
            $_err['general'] = 'Failed to sign up. Please try again.';
        }
    } else {
        $_err['otp'] = 'Invalid or expired OTP. Please try again.';
    }
}
?>

<!-- OTP Verification Form -->
<form action="verifyOtp.php" method="post">
    <h1>Verify OTP</h1>
    <label for="otp">OTP</label><br>
    <input type="text" name="otp" id="otp" maxlength="6" required><br>
    <?= err('otp') ?><br>

    <button type="submit">Verify OTP</button>
    <?= isset($_err['general']) ? '<p>' . $_err['general'] . '</p>' : '' ?>
</form>
