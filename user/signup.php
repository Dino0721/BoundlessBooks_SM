<?php
require '../pageFormat/base.php';
require '../pageFormat/head.php';
require_once __DIR__ . '/../app/bootstrap.php';

$userModel = new User();

if (is_post()) {
    $form_type = req('form_type');
    $_err = [];

    // CSRF Protection
    $token = post('csrf_token');
    if (!SessionManager::verifyCsrfToken($token)) {
        $_err['general'] = 'Invalid CSRF token. Please refresh and try again.';
        // Debugging
        $_err['debug'] = 'Session Token: ' . ($_SESSION['csrf_token'] ?? 'None') . ' | Posted Token: ' . $token;
    }

    if ($form_type == 'signup' && empty($_err)) {
        // Handle Sign-Up Form Submission
        $email = req('email');
        $password = req('password');
        $confirm_password = req('confirm_password') ?? '';
        $phone_number = req('phone_number') ?? '';

        // Email Validation
        if ($email == '') {
            $_err['email'] = 'Email is required.';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid email format.';
        } else {
            // Check for duplicate email using User model
            if ($userModel->findByEmail($email)) {
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

        // Phone Number Validation (Optional Field)
        if ($phone_number !== '' && !preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
            $_err['phone_number'] = 'Invalid phone number format. Use digits only, optionally prefixed by "+".';
        }

        if (empty($_err)) {
            // OTP generation
            $otp = rand(100000, 999999);
            SessionManager::set('signup_email', $email);
            SessionManager::set('signup_password', password_hash($password, PASSWORD_DEFAULT));
            SessionManager::set('signup_phone', $phone_number);
            SessionManager::set('otp', $otp);
            SessionManager::set('otp_expiry', time() + 180);

            // Use get_mail() for email setup
            $mail = get_mail();
            try {
                // Email setup
                $mail->addAddress($email);
                $mail->Subject = 'Your OTP for Sign Up';
                $mail->Body = "Your OTP code is: $otp. It will expire in 3 minutes.";

                // Send the email
                $mail->send();
                temp('info', 'OTP has been sent to your email.');
            } catch (Exception $e) {
                $_err['general'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

            // Redirect to OTP verification page if no errors occurred during email sending
            if (empty($_err['general'])) {
                redirect('verifyOtp.php');
                exit();
            }
        }
    }
}
?>

<?php
if (isset($_POST['back_to_login'])) {
    redirect('login.php');
    exit();
}
?>

<!-- Sign-Up Form -->
<form action="signup.php" method="post" id="sign-up-form">
    <h1>Sign Up</h1>
    <input type="hidden" name="form_type" value="signup">
    <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCsrfToken() ?>">
    
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>

    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <label for="confirm_password">Confirm Password</label><br>
    <?= html_password('confirm_password', 'maxlength="100"') ?>
    <?= err('confirm_password') ?><br>

    <label for="phone_number">Phone Number (Optional)</label><br>
    <?= html_text('phone_number', 'maxlength="15"') ?>
    <?= err('phone_number') ?><br>

    <button type="submit" name="back_to_login">Back to Login</button>
    <button type="reset">Reset</button>
    <button type="submit">Sign Up</button>
    <?= isset($_err['general']) ? '<p class="err">' . $_err['general'] . '</p>' : '' ?>
    <?= isset($_err['debug']) ? '<p class="err" style="font-size: 0.8em; color: gray;">' . $_err['debug'] . '</p>' : '' ?>
</form>
