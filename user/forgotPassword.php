<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require '../vendor/autoload.php';
require '../pageFormat/base.php';
include '../pageFormat/head.php';

if (is_post()) {
    $email = req('email');

    // Email validation
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else {
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $user = $stm->fetch();
    }

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stm = $_db->prepare('UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?');
        $stm->execute([$token, $expiry, $email]);

        $resetLink = "http://localhost:8000/user/changePassword.php?reset_token=$token";

        // Use the get_mail() function
        $mail = get_mail();
        try {
            // Email details
            $mail->addAddress($email); // Recipient's email
            $mail->Subject = 'Password Reset';
            $mail->Body = "Click this link to reset your password: $resetLink";

            // Send the email
            $mail->send();
            temp('info', 'Check your email for the password reset link');
        } catch (Exception $e) {
            $_err['email'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_err['email'] = 'Email not found';
    }
}
?>

<form action="forgotPassword.php" method="post">
    <h1>Forgot Password</h1>
    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>
    <button>Submit</button>
</form>
