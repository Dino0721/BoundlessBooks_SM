<?php

require_once 'base.php';

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
    <script>
        // Highlight the active menu item
        $(document).ready(function() {
            $('.header__a').each(function() {
                if (this.href === window.location.href) {
                    $(this).addClass('active'); // Add a class for styling
                }
            });
        });
    </script>
</head>

<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>


    <header class="header">
        <h1 class="header__logo">
            <a href="index.php">BoundlessBooks
                <!-- <img src="" alt="Logo"> -->
            </a>
        </h1>
        <nav class="header__nav">
            <ul class="header__ul">
                <?= createNavItem("../productCatalog/productCatalog.php", "Product Catalog"); ?>
                <?= createNavItem("../productCatalog/manageBooks.php", "Manage Books"); ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../user/profile.php" class="profile">
                        <?= htmlspecialchars($_SESSION['email'] ?? 'User'); ?>
                    </a>
                    <?= createNavItem("../user/logout.php", "Logout"); ?>
                <?php else: ?>
                    <?= createNavItem("../user/login.php", "Login"); ?>
                <?php endif; ?>

                <?= createNavItem("../cartSide/CartPage.php", "Shopping Cart"); ?>
                
                <?php
                if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
                    echo createNavItem("../orderManagement/orderHistory.php", "Order History");
                    echo createNavItem("../orderManagement/orderListing.php", "Order Listing");
                }
                ?>

            </ul>
        </nav>
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