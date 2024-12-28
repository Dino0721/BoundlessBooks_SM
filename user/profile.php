<?php

require '../pageFormat/base.php';
require '../pageFormat/head.php';

if (!isset($_SESSION['user_id'])) {
    temp('info', 'You need to log in to access the profile page.');
    header('Location: login.php');
    exit();
}

// Retrieve user information from the database
$user_id = $_SESSION['user_id'];
try {
    $stmt = $_db->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        temp('info', 'User not found. Please log in again.');
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    temp('info', 'An error occurred while fetching user data.');
    header('Location: login.php');
    exit();
}

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
            <p><b>Phone Number: </b> <?= htmlspecialchars($user['phone_number'] ?? 'Not provided') ?></p>
            <p><b>Role: </b> <?= htmlspecialchars($user['admin'] ? 'Admin' : 'User') ?></p>
            <?php if (!empty($user['profile_photo'])): ?>
                <p><b>Profile Photo:</b><br>
                    <img src="/uploads/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" style="max-width: 150px;">
                </p>
            <?php endif; ?>
        </section>

        <section>
            <h3>Actions</h3>
            <ul>
                <li><a href="editProfile.php">Edit Profile</a></li>
                <li><a href="forgotPassword.php">Change Password</a></li>
                <li><a href="uploadProfilePhoto.php">Upload Profile Photo</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </section>
    </main>
</body>
</html>