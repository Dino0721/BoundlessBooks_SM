<?php
require '../pageFormat/base.php';
require '../pageFormat/head.php';
require_once __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$user = $auth->getCurrentUser();

if (!$user) {
    temp('info', 'You need to log in to access the profile page.');
    redirect('login.php');
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
            <h2>Welcome, <?= htmlspecialchars($user->email) ?>!</h2>
            <p><b>Email: </b> <?= htmlspecialchars($user->email) ?></p>
            <p><b>Phone Number: </b> <?= htmlspecialchars($user->phone_number ?? 'Not provided') ?></p>
            <p><b>Role: </b> <?= htmlspecialchars($user->isAdmin() ? 'Admin' : 'User') ?></p>
            <?php if (!empty($user->profile_photo)): ?>
                <p><b>Profile Photo:</b><br>
                    <img src="/uploads/<?= htmlspecialchars($user->profile_photo) ?>" alt="Profile Photo" style="max-width: 150px;">
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