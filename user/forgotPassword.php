<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

require '../pageFormat/base.php';
include '../pageFormat/head.php';

if (is_post()) {
    $email = req('email');

    // email validation
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
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stm = $_db->prepare('UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?');
        $stm->execute([$token, $expiry, $email]);

        $resetLink = "localhost:8000/user/changePassword.php?reset_token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tequilaguey777@gmail.com'; // Your Gmail address
            $mail->Password = 'dbkhijmymjdaohkj '; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email details
            $mail->setFrom($mail->Username, 'BoundlessBooks');
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