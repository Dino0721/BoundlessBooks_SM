<?php
require '../pageFormat/base.php';
?>

<form action="signup.php" id="sign-up-form" style="display: none;">
    <h1>Sign Up</h1>
    <label for="">Email</label><br>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?><br>

    <label for="">Password</label><br>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?><br>

    <button>Sign Up</button>

</form>