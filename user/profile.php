<?php

require '../pageFormat/base.php';

if (!isset($_SESSION['user_id'])) {
    temp('info', 'You need to log in to access the profile page.');
    header('Location: ../productCatalog/productCatalog.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stm = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
$stm->execute([$user_id]);
$user = $stm->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    temp('info', 'User not found. Please log in again.');
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <header>
        <h1>User Profile</h1>
    </header>

    <main>
        <section>
            <h2>Welcome, <?= htmlspecialchars($user['email']) ?>!</h2>
            <p><b>Email: </b> <?= htmlspecialchars($user['email']) ?></p>
            <!-- TODO -->
            <p><b>: </b> <?= htmlspecialchars($user['']) ?></p>
            <p><b>Role: </b> <?= htmlspecialchars($user['role'] ?? 'User') ?></p>
        </section>

        <section>
            <h3>Actions</h3>
            <ul>
                <li><a href="editProfile.php">Edit Profile</a></li>
                <li><a href="changePassword.php">Change Password</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </section>
    </main>
</body>
</html>