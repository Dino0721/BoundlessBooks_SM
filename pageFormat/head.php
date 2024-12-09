<?php

require_once 'base.php';
// session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/main.js"></script>
</head>

<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>

    <header>
        <h1><a href="/">BoundlessBooks</a></h1>
        <!-- redirect('../loginSide/login.php') -->
        <a href="../user/login.php">Login</a>
        <a href="../productCatalog/productCatalog.php">Product Catalog</a>
        <a href="../productCatalog/manageBooks.php" class="btn">
            Manage Books
        </a>
        <!-- <?php if ($isAdmin): ?> -->
        <!-- <a href="manageBooks.php" class="btn" style="display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
            Manage Books
        </a> -->

        <!-- <?php endif; ?> -->

        <!-- TODO
        <?php if (1): ?>
            <div>
                <?= 0 ?><br>
                <?= 0 ?>
            </div>
            <img src="/photos/<?= '0.jpg' ?>">
        <?php endif ?> -->
    </header>

    <nav>
        <!-- <a href="/">Index</a>

            TODO
        <?php if (1): ?>
            <a href="/demo1.php">Demo 1</a>
        <?php endif ?>

        <?php if (1): ?>
            <a href="/demo2.php">Demo 2</a>
        <?php endif ?>

        <?php if (1): ?>
            <a href="/demo3.php">Demo 3</a>
        <?php endif ?>

        <div></div>

        TODO 
        <?php if (1): ?>
            <a href="/user/profile.php">Profile</a>
            <a href="/user/password.php">Password</a>
            <a href="/logout.php">Logout</a>
        <?php  ?>
            <a href="/user/register.php">Register</a>
            <a href="/login.php">Login</a>
        <?php endif ?> -->
    </nav>

    <main>
        <!-- <h1><?= $_title ?? 'Untitled' ?></h1> -->