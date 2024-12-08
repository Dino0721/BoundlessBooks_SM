<?php

require '../pageFormat/base.php';

// authenticating users
// auth('Admin');
// if (is_post()) {
//     echo "Form submitted via POST"; // Check if this message appears
// }

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
        // if (!$stm) {
        //     echo "Query preparation failed: " . implode(' ', $_db->errorInfo());
        //     exit();
        // } else {
        //     echo "Query preparation succeeded.<br>";
        // }
        $stm->execute([$email, $password]);
        // $result = 
        // if (!$result) {
        //     echo "Query execution failed: " . implode(' ', $_db->errorInfo());
        //     exit();
        // } else {
        //     echo "Query executed successfully.<br>";
        // }

        $u = $stm->fetch();
        // if ($u) {
        //     echo "User found: ";
        //     print_r($u);
        //     exit(); // Remove this after confirming results
        // } else {
        //     echo "No user matched the provided credentials.<br>";
        //     $_err['password'] = 'Not matched';
        // }
        
        if ($u) {
            temp('info', 'Login successfully');
            print_r($u);
            login($u, '../productCatalog/productCatalog.php');
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

<form action="login.php" method="post">
    <h1>Login</h1>
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>
    <br>
    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <button type="submit">Login</button>
    <button type="button" id="sign-up-btn">Sign Up</button>
</form>

<?php
include '../pageFormat/footer.php';