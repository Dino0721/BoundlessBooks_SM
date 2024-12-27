<?php
require_once __DIR__ . '/../vendor/autoload.php';

require '../pageFormat/base.php';
require '../pageFormat/head.php';

// require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (is_post()) {
    $form_type = req('form_type'); // Get the form type to differentiate actions
    $_err = [];

    if ($form_type == 'signup') {
        params:
        // Handle Sign-Up Form Submission
        $email = req('email');
        $password = req('password');
        $confirm_password = req('confirm_password') ?? '';

        // Email Validation
        if ($email == '') {
            $_err['email'] = 'Email is required.';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid email format.';
        } else {
            // Check for duplicate email
            $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE email = ?');
            $stm->execute([$email]);
            if ($stm->fetchColumn() > 0) {
                $_err['email'] = 'Email already exists.';
            }
        }

        // Password Validation
        if ($password == '') {
            $_err['password'] = 'Password is required.';
        } else if (strlen($password) < 6) {
            $_err['password'] = 'Password must be at least 6 characters.';
        }

        // Confirm Password Validation
        if ($confirm_password == '') {
            $_err['confirm_password'] = 'Please confirm your password.';
        } else if ($password !== $confirm_password) {
            $_err['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($_err)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database
            $stm = $_db->prepare('INSERT INTO user (email, password, phone_number) VALUES (?, ?, ?)');
            if ($stm->execute([$email, $hashed_password, ''])) {
                temp('info', 'Sign-up successful.');

                // OTP generation
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_expiry'] = time() + 600;

                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'tequilaguey777@gmail.com'; // Use your email
                    $mail->Password = 'dbkhijmymjdaohkj'; // Use your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('tequilaguey777@gmail.com', 'BoundlessBooks');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Sign Up';
                    $mail->Body = "Your OTP code is: $otp. It will expire in 3 minutes.";

                    $mail->send();
                    echo 'OTP has been sent to your email.';
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

                $_SESSION['email'] = $email;

                redirect(url: 'verifyOtp.php');
                exit();
            } else {
                $_err['general'] = 'Failed to sign up. Please try again.';
            }
        }
    }
}
?>

<!-- style="display: none;" -->
<!-- Sign-Up Form -->
<form action="signup.php" method="post" id="sign-up-form">
    <h1>Sign Up</h1>
    <input type="hidden" name="form_type" value="signup">
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>

    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <label for="confirm_password">Confirm Password</label><br>
    <?= html_password('confirm_password', 'maxlength="100"') ?>
    <?= err('confirm_password') ?><br>

    <button type="submit">Sign Up</button>
    <?= isset($_err['general']) ? '<p>' . $_err['general'] . '</p>' : '' ?>
</form>