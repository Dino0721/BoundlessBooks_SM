<?php
require '../pageFormat/base.php';
require '../pageFormat/head.php';

if (is_post()) {
    $enteredOtp = req('otp');
    $email = $_SESSION['signup_email'] ?? null;
    $password = $_SESSION['signup_password'] ?? null;

    if (!$email || !$password) {
        echo 'Session expired. Please start the signup process again.';
        redirect('signup.php');
        exit();
    }

    if (!$enteredOtp || $enteredOtp != ($_SESSION['otp'] ?? '')) {
        echo 'Invalid OTP.';
    } elseif (time() > ($_SESSION['otp_expiry'] ?? 0)) {
        echo 'OTP expired. Please try signing up again.';
        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['signup_email'], $_SESSION['signup_password']);
        redirect('signup.php');
        exit();
    } else {
        // OTP is valid, insert user into database
        $stm = $_db->prepare('INSERT INTO user (email, password, phone_number) VALUES (?, ?, ?)');
        if ($stm->execute([$email, $password, ''])) {
            unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['signup_email'], $_SESSION['signup_password']);
            temp('info', 'Sign-up successful.');
            redirect('login.php');
            exit();
        } else {
            echo 'Failed to complete sign-up. Please try again.';
        }
    }
}
?>

<form action="verifyOtp.php" method="post">
    <h1>Verify OTP</h1>
    <label for="otp">Enter OTP:</label>
    <input type="text" id="otp" name="otp" maxlength="6" required>
    <button type="submit">Verify</button>
</form>
