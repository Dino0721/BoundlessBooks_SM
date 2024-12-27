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
    <!-- <pre>
<?php print_r($_SESSION); ?>
</pre> -->

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

                <?= createNavItem("../orderManagement/orderHistory.php", "Order History"); ?>
            </ul>
        </nav>

        <!-- Admin Navbar -->
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
            <nav class="admin-nav">
                <ul class="admin-nav__ul">
                    <?= createNavItem("../orderManagement/orderListing.php", "Order Listing"); ?>
                    <?= createNavItem("../rewardManagement/rewardManagementPage.php", "Reward Management"); ?>
                </ul>
            </nav>
        <?php endif; ?>
    </header>

    <main>
        <!-- <h1><?= $_title ?? 'Untitled' ?></h1> -->

</body>

</html>
