<?php

require '../pageFormat/base.php';
require '../pageFormat/head.php';
require_once __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();

if (is_post()) {
    $form_type = req('form_type');
    $_err = [];

    // CSRF Protection
    if (!SessionManager::verifyCsrfToken(post('csrf_token'))) {
        $_err['general'] = 'Invalid CSRF token. Please refresh and try again.';
    }

    if ($form_type == 'login' && empty($_err)) {
        $email = req('email');
        $password = req('password');

        // Validation
        if ($email == '') {
            $_err['email'] = 'Email is required.';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid email format.';
        }

        if ($password == '') {
            $_err['password'] = 'Password is required.';
        }

        if (empty($_err)) {
            if ($auth->login($email, $password)) {
                temp('info', 'Login successful.');
                redirect('../productCatalog/productCatalog.php');
            } else {
                $_err['password'] = 'Email or password is incorrect.';
            }
        }
    }
}
?>

<!-- Login Form -->
<form action="login.php" method="post" id="login-form">
    <h1>Login</h1>
    <input type="hidden" name="form_type" value="login">
    <input type="hidden" name="csrf_token" value="<?= SessionManager::generateCsrfToken() ?>">
    
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>

    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <div class="button-container">
        <button type="submit">Login</button>
        <button type="reset">Reset</button>
        <button type="button" id="sign-up-btn"><a href="signup.php">Sign Up</a></button>
    </div>

    <?= isset($_err['general']) ? '<p class="err">' . $_err['general'] . '</p>' : '' ?>

    <p><a href="forgotPassword.php">Forgot Password?</a></p>
</form>