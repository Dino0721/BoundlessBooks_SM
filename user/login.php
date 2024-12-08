<?php

require '../pageFormat/base.php';
include '../pageFormat/head.php';

// authenticating users
// auth('Admin');

if (is_post()) {
    // $username = req('username');
    $email = req('email');
    $password = req('password');

    // Username Validation
    // if ($username == '') {
    //     $_err['username'] = 'Required';
    // }

    // Email Validation
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Password Validation
    if ($password == '') {
        $_err['password'] = 'Required';
    }

    // Login the user
    if (!$_err) {
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ? AND password = SHA1(?)');
        
        $stm->execute([$email, $password]);

        $user = $stm->fetch();
        
        if ($user) {
            temp('info', 'Login successfully');
            print_r($user);
            login($user, '../productCatalog/productCatalog.php');
            // redirect('../productCatalog/productCatalog.php');
            // header("Location: ../productCatalog/product.php");
            // exit();
        } else {
            $_err['password'] = 'Not matched';
        }
    }
}
?>

<?php
// include '../pageFormat/head.php'
?>

<form action="login.php" method="post" id="login-form">
    <h1>Login</h1>
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>
    <br>
    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <button type="submit">Login</button>
    <button type="button" id="sign-up-btn">Sign Up</button><br>
    <button type="button" id="reset-btn">Reset</button>
    <p><a href="forgotPassword.php">Forgot Password</a></p>
</form>

<?php
include '../pageFormat/footer.php';