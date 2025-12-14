<?php
require '../pageFormat/base.php';
require '../pageFormat/head.php';
require_once __DIR__ . '/../app/bootstrap.php';

$userModel = new User();

if (is_post()) {
    // CSRF Check
    if (!SessionManager::verifyCsrfToken(post('csrf_token'))) {
        die('Invalid CSRF Token');
    }

    $enteredOtp = req('otp');
    $email = SessionManager::get('signup_email');
    $password = SessionManager::get('signup_password');
    $phone = SessionManager::get('signup_phone');

    if (!$email || !$password) {
        echo 'Session expired. Please start the signup process again.';
        redirect('signup.php');
        exit();
    }

    if (!$enteredOtp || $enteredOtp != SessionManager::get('otp')) {
        echo 'Invalid OTP.';
    } elseif (time() > SessionManager::get('otp_expiry', 0)) {
        echo 'OTP expired. Please try signing up again.';
        SessionManager::remove('otp');
        SessionManager::remove('otp_expiry');
        SessionManager::remove('signup_email');
        SessionManager::remove('signup_password');
        SessionManager::remove('signup_phone');
        redirect('signup.php');
        exit();
    } else {
        // OTP is valid, insert user into database
        if ($userModel->create($email, $password, $phone)) {
            SessionManager::remove('otp');
            SessionManager::remove('otp_expiry');
            SessionManager::remove('signup_email');
            SessionManager::remove('signup_password');
            SessionManager::remove('signup_phone');
            
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
    <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCsrfToken() ?>">
    <label for="otp">Enter OTP:</label>
    <input type="text" id="otp" name="otp" maxlength="6" required>
    <button type="submit">Verify</button>
</form>
