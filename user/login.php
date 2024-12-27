<?php

require '../pageFormat/base.php';
require '../pageFormat/head.php';

if (is_post()) {
    $form_type = req('form_type'); // Get the form type to differentiate actions
    $_err = [];

    if ($form_type == 'login') {
        // Handle Login Form Submission
        $email = req('email');
        $password = req('password');

        // Email Validation
        if ($email == '') {
            $_err['email'] = 'Email is required.';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid email format.';
        }

        // Password Validation
        if ($password == '') {
            $_err['password'] = 'Password is required.';
        }

        if (empty($_err)) {
            $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
            $stm->execute([$email]);

            $user = $stm->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['admin'] = $user['admin'];
                $_SESSION['user'] = $user;

                temp('info', 'Login successful.');
                login($user, '../productCatalog/productCatalog.php');
                exit();
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

    <p><a href="forgotPassword.php">Forgot Password?</a></p>
</form>