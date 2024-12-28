<?php

require_once 'base.php';

?>

<?php
// Assuming user_id is stored in session when logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    try {
        // Fetch the active cart ID for the user
        $cartQuery = "SELECT cart_id FROM cart WHERE user_id = :user_id AND paid = 0";
        $cartStmt = $_db->prepare($cartQuery);
        $cartStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $cartStmt->execute();
        $cartId = $cartStmt->fetchColumn();

        if ($cartId) {
            // Fetch the number of items in the cart
            $itemCountQuery = "SELECT COUNT(*) FROM cart_item WHERE cart_id = :cart_id";
            $itemCountStmt = $_db->prepare($itemCountQuery);
            $itemCountStmt->bindParam(':cart_id', $cartId, PDO::PARAM_INT);
            $itemCountStmt->execute();
            $itemCount = $itemCountStmt->fetchColumn();
        } else {
            // If no active cart, set item count to 0
            $itemCount = 0;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        $itemCount = 0; // Set item count to 0 in case of error
    }
} else {
    $itemCount = 0; // No user logged in
}
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

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../user/profile.php" class="profile">
                        <?= htmlspecialchars($_SESSION['email'] ?? 'User'); ?>
                    </a>
                    <?= createNavItem("../user/logout.php", "Logout"); ?>
                <?php else: ?>
                    <?= createNavItem("../user/login.php", "Login"); ?>
                <?php endif; ?>

                <?= createNavItem("../cartSide/cartMain.php", "Shopping Cart" . ($itemCount ? " ($itemCount)" : "")); ?>
                <?= createNavItem("../wishList/wishList.php", "Wish List"); ?>
                <?= createNavItem("../orderManagement/orderHistory.php", "Order History"); ?>
                <?= createNavItem("../productCatalog/topBookRanking.php", "Top Book Rankings"); ?>
            </ul>
        </nav>

        <!-- Admin Navbar -->
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
            <nav class="admin-nav">
                <ul class="admin-nav__ul">
                    <?= createNavItem("../productCatalog/manageBooks.php", "Manage Books"); ?>
                    <?= createNavItem("../orderManagement/orderListing.php", "Order Listing"); ?>
                    <?= createNavItem("../discountManagement/discountManagementPage.php", "Discount Management"); ?>
                </ul>
            </nav>
        <?php endif; ?>
    </header>

    <main>
        <!-- <h1><?= $_title ?? 'Untitled' ?></h1> -->

</body>

</html>