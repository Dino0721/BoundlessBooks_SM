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
            $stm = $_db->prepare('SELECT * FROM user WHERE email = ? AND password = SHA1(?)');
            $stm->execute([$email, $password]);

            $user = $stm->fetch();
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    temp('info', 'Login successful.');
                    login($user, '../productCatalog/productCatalog.php');
                    exit();
                }
            } else {
                $_err['password'] = 'Email or password is incorrect.';
            }
        }
    } elseif ($form_type == 'signup') {
        // Handle Sign-Up Form Submission
        $email = req('email');
        $password = req('password');

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

        if (empty($_err)) {
            $hashed_password = sha1($password);
            $role = 'Member'; // Default role

            $stm = $_db->prepare('INSERT INTO user (username, email, password, phone_number, role) VALUES (?, ?, ?, ?, ?)');
            if ($stm->execute(['New User', $email, $hashed_password, '', $role])) {
                temp('info', 'Sign-up successful.');
                login(['email' => $email, 'role' => $role], '../productCatalog/productCatalog.php');
                exit();
            } else {
                $_err['general'] = 'Failed to sign up. Please try again.';
            }
        }
    }
}
?>

<!-- Login and Sign-Up Form -->
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
        <button type="button" id="sign-up-btn">Sign Up</button>
    </div>

    <p><a href="forgotPassword.php">Forgot Password?</a></p>
</form>

<!-- Sign-Up Form -->
<form action="login.php" method="post" id="sign-up-form" style="display: none;">
    <h1>Sign Up</h1>
    <input type="hidden" name="form_type" value="signup">
    <label for="email">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>

    <label for="password">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <button type="submit">Sign Up</button>
    <?= isset($_err['general']) ? '<p>' . $_err['general'] . '</p>' : '' ?>
</form>